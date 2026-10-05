<?php

namespace App\Services;

use App\Jobs\GenerateDocumentPdf;
use App\Models\Article;
use App\Models\Client;
use App\Models\CompanyProfile;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DocumentService
{
    public function send(Document $document): Document
    {
        return DB::transaction(function () use ($document): Document {
            $document = Document::query()->lockForUpdate()->findOrFail($document->id);
            if (! in_array($document->type, [Document::TYPE_QUOTE, Document::TYPE_INVOICE], true)
                || $document->status !== 'draft') {
                throw ValidationException::withMessages([
                    'document' => 'Seul un document brouillon peut être marqué comme envoyé.',
                ]);
            }

            $document->update(['status' => 'sent', 'sent_at' => now()]);
            $this->refreshPdfAfterCommit($document);

            return $document;
        }, 3);
    }

    public function acceptQuote(Document $document): Document
    {
        return $this->transitionQuote($document, 'accepted');
    }

    public function rejectQuote(Document $document): Document
    {
        return $this->transitionQuote($document, 'rejected');
    }

    public function markInvoicePaid(Document $document): Document
    {
        return DB::transaction(function () use ($document): Document {
            $document = Document::query()->lockForUpdate()->findOrFail($document->id);

            if ($document->type !== Document::TYPE_INVOICE || $document->status !== 'sent') {
                throw ValidationException::withMessages([
                    'document' => 'Seule une facture envoyée peut être marquée comme payée.',
                ]);
            }

            $document->update(['status' => 'paid', 'paid_at' => now()]);
            $this->refreshPdfAfterCommit($document);

            return $document;
        }, 3);
    }

    public function createQuote(User $user, array $data): Document
    {
        return DB::transaction(function () use ($user, $data): Document {
            $client = Client::query()->findOrFail($data['client_id']);
            $profile = CompanyProfile::current();
            $issueDate = $data['issue_date'];
            $validUntil = $data['valid_until']
                ?? Carbon::parse($issueDate)->addDays($profile->quote_validity_days)->toDateString();
            $lines = $this->priceLines($data['lines'], $profile->currency);
            $totals = $this->totals($lines);

            $document = Document::create([
                'type' => Document::TYPE_QUOTE,
                'number' => app(DocumentNumberGenerator::class)->next(
                    Document::TYPE_QUOTE,
                    (int) substr($issueDate, 0, 4)
                ),
                'status' => 'draft',
                'client_id' => $client->id,
                'created_by' => $user->id,
                'issue_date' => $issueDate,
                'valid_until' => $validUntil,
                'currency' => $profile->currency,
                'client_snapshot' => $this->clientSnapshot($client),
                'company_snapshot' => $this->companySnapshot($profile),
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
                ...$totals,
            ]);

            $document->lines()->createMany($lines);

            return $document;
        }, 3);
    }

    public function updateQuote(Document $document, array $data): Document
    {
        return DB::transaction(function () use ($document, $data): Document {
            $document = Document::query()->lockForUpdate()->findOrFail($document->id);
            $this->ensureDraft($document, Document::TYPE_QUOTE);

            $client = Client::query()->findOrFail($data['client_id']);
            $profile = CompanyProfile::current();
            $lines = $this->priceLines($data['lines'], $document->currency);

            $document->update([
                'client_id' => $client->id,
                'issue_date' => $data['issue_date'],
                'valid_until' => $data['valid_until']
                    ?? Carbon::parse($data['issue_date'])->addDays($profile->quote_validity_days)->toDateString(),
                'client_snapshot' => $this->clientSnapshot($client),
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
                ...$this->totals($lines),
            ]);
            $document->lines()->delete();
            $document->lines()->createMany($lines);
            $this->refreshPdfAfterCommit($document);

            return $document->load('lines', 'client');
        }, 3);
    }

    public function convertAcceptedQuoteToInvoice(Document $quote, User $user): Document
    {
        return DB::transaction(function () use ($quote, $user): Document {
            $quote = Document::query()->lockForUpdate()->with('lines')->findOrFail($quote->id);

            if ($quote->type !== Document::TYPE_QUOTE || $quote->status !== 'accepted') {
                throw ValidationException::withMessages([
                    'document' => 'Seul un devis accepté peut être converti en facture.',
                ]);
            }

            if ($quote->invoice()->exists()) {
                throw ValidationException::withMessages([
                    'document' => 'Ce devis a déjà été converti en facture.',
                ]);
            }

            $profile = CompanyProfile::current();
            $issueDate = now()->toDateString();
            $invoice = Document::create([
                'type' => Document::TYPE_INVOICE,
                'number' => app(DocumentNumberGenerator::class)->next(
                    Document::TYPE_INVOICE,
                    (int) now()->format('Y')
                ),
                'status' => 'draft',
                'client_id' => $quote->client_id,
                'created_by' => $user->id,
                'quote_id' => $quote->id,
                'issue_date' => $issueDate,
                'due_date' => now()->addDays($profile->invoice_due_days)->toDateString(),
                'currency' => $quote->currency,
                'client_snapshot' => $quote->client_snapshot,
                'company_snapshot' => $this->companySnapshot($profile),
                'notes' => $quote->notes,
                'terms' => $quote->terms,
                'subtotal' => $quote->subtotal,
                'tax_total' => $quote->tax_total,
                'total' => $quote->total,
            ]);

            foreach ($quote->lines as $line) {
                $invoice->lines()->create($line->only([
                    'article_id',
                    'description',
                    'unit',
                    'quantity',
                    'unit_price',
                    'tax_rate',
                    'subtotal',
                    'tax_amount',
                    'total',
                ]));
            }

            return $invoice->load('lines', 'client', 'sourceQuote');
        }, 3);
    }

    /**
     * @param  array<int, array{article_id: int, quantity: int|float|string}>  $input
     * @return array<int, array<string, int|float|string|null>>
     */
    private function priceLines(array $input, string $currency): array
    {
        $articles = Article::query()
            ->where('is_active', true)
            ->whereIn('id', collect($input)->pluck('article_id'))
            ->get()
            ->keyBy('id');

        if ($articles->count() !== collect($input)->pluck('article_id')->unique()->count()) {
            throw ValidationException::withMessages([
                'lines' => 'Un article sélectionné n’est plus disponible dans le catalogue.',
            ]);
        }

        return collect($input)->map(function (array $inputLine) use ($articles, $currency): array {
            $article = $articles->get((int) $inputLine['article_id']);
            $quantity = (float) $inputLine['quantity'];
            $minorUnitsPerUnit = $currency === 'XOF' ? 1 : 100;
            $precision = $currency === 'XOF' ? 0 : 2;
            $unitPriceMinor = (int) round(
                (float) $article->unit_price * $minorUnitsPerUnit,
                0,
                PHP_ROUND_HALF_UP
            );
            $subtotalMinor = (int) round($quantity * $unitPriceMinor, 0, PHP_ROUND_HALF_UP);
            $taxMinor = (int) round(
                $subtotalMinor * (float) $article->tax_rate / 100,
                0,
                PHP_ROUND_HALF_UP
            );
            $formatAmount = static fn (int $amount): string => number_format(
                $amount / $minorUnitsPerUnit,
                $precision,
                '.',
                ''
            );

            return [
                'article_id' => $article->id,
                'description' => $article->description
                    ? "{$article->name} — {$article->description}"
                    : $article->name,
                'unit' => $article->unit,
                'quantity' => number_format($quantity, 2, '.', ''),
                'unit_price' => $formatAmount($unitPriceMinor),
                'tax_rate' => $article->tax_rate,
                'subtotal' => $formatAmount($subtotalMinor),
                'tax_amount' => $formatAmount($taxMinor),
                'total' => $formatAmount($subtotalMinor + $taxMinor),
            ];
        })->all();
    }

    /**
     * @param  array<int, array<string, int|float|string|null>>  $lines
     * @return array{subtotal: string, tax_total: string, total: string}
     */
    private function totals(array $lines): array
    {
        $subtotal = array_sum(array_map(
            static fn (array $line): int => (int) round((float) $line['subtotal'] * 100),
            $lines
        ));
        $tax = array_sum(array_map(
            static fn (array $line): int => (int) round((float) $line['tax_amount'] * 100),
            $lines
        ));

        return [
            'subtotal' => number_format($subtotal / 100, 2, '.', ''),
            'tax_total' => number_format($tax / 100, 2, '.', ''),
            'total' => number_format(($subtotal + $tax) / 100, 2, '.', ''),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function clientSnapshot(Client $client): array
    {
        return $client->only([
            'name',
            'company',
            'email',
            'phone',
            'address',
            'postal_code',
            'city',
        ]);
    }

    /**
     * @return array<string, string|null>
     */
    private function companySnapshot(CompanyProfile $profile): array
    {
        return $profile->only([
            'name',
            'email',
            'phone',
            'address',
            'postal_code',
            'city',
            'country',
            'registration_number',
            'vat_number',
            'iban',
        ]);
    }

    private function ensureDraft(Document $document, string $type): void
    {
        if ($document->type !== $type || ! $document->isEditable()) {
            throw ValidationException::withMessages([
                'document' => 'Seuls les documents brouillon de ce type peuvent être modifiés.',
            ]);
        }
    }

    private function transitionQuote(Document $document, string $status): Document
    {
        return DB::transaction(function () use ($document, $status): Document {
            $document = Document::query()->lockForUpdate()->findOrFail($document->id);

            if ($document->type !== Document::TYPE_QUOTE || $document->status !== 'sent') {
                throw ValidationException::withMessages([
                    'document' => 'Seul un devis envoyé peut être accepté ou refusé.',
                ]);
            }

            $document->update(['status' => $status]);
            $this->refreshPdfAfterCommit($document);

            return $document;
        }, 3);
    }

    private function refreshPdfAfterCommit(Document $document): void
    {
        DB::afterCommit(function () use ($document): void {
            Storage::disk('local')->delete($document->pdfPath());
            GenerateDocumentPdf::dispatch($document->id);
        });
    }
}
