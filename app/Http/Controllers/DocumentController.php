<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Models\Article;
use App\Models\Client;
use App\Models\CompanyProfile;
use App\Models\Document;
use App\Services\DocumentPdfService;
use App\Services\DocumentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function quotes(Request $request): View
    {
        return $this->listing($request, Document::TYPE_QUOTE);
    }

    public function invoices(Request $request): View
    {
        return $this->listing($request, Document::TYPE_INVOICE);
    }

    public function createQuote(Request $request): View|RedirectResponse
    {
        if (! CompanyProfile::current()->isReadyForDocuments()) {
            return to_route('company.edit')
                ->with('warning', 'Renseignez le nom, l’adresse et l’e-mail de l’entreprise avant d’émettre un devis.');
        }

        return view('documents.create', [
            'clients' => Client::query()->orderBy('name')->get(),
            'articles' => Article::query()->where('is_active', true)->orderBy('name')->get(),
            'document' => new Document([
                'issue_date' => today(),
                'valid_until' => today()->addDays(CompanyProfile::current()->quote_validity_days),
                'client_id' => $request->integer('client_id') ?: null,
            ]),
        ]);
    }

    public function storeQuote(StoreDocumentRequest $request, DocumentService $documents): RedirectResponse
    {
        $document = $documents->createQuote($request->user(), $request->validated());

        return to_route('quotes.show', $document)->with('success', 'Le devis brouillon a été créé.');
    }

    public function showQuote(Document $document): View
    {
        $this->ensureType($document, Document::TYPE_QUOTE);

        return $this->showDocument($document, 'quote');
    }

    public function editQuote(Document $document): View
    {
        $this->ensureEditableType($document, Document::TYPE_QUOTE);

        return view('documents.edit', [
            'clients' => Client::query()->orderBy('name')->get(),
            'articles' => Article::query()->where('is_active', true)->orderBy('name')->get(),
            'document' => $document->load('lines'),
        ]);
    }

    public function updateQuote(
        StoreDocumentRequest $request,
        Document $document,
        DocumentService $documents
    ): RedirectResponse {
        $this->ensureType($document, Document::TYPE_QUOTE);
        $documents->updateQuote($document, $request->validated());

        return to_route('quotes.show', $document)->with('success', 'Le devis brouillon a été mis à jour.');
    }

    public function showInvoice(Document $document): View
    {
        $this->ensureType($document, Document::TYPE_INVOICE);

        return $this->showDocument($document, 'invoice');
    }

    public function send(Document $document, DocumentService $documents): RedirectResponse
    {
        $documents->send($document);

        return $this->documentRoute($document)
            ->with('success', 'Le document est marqué comme envoyé. Le PDF est prêt à télécharger.');
    }

    public function acceptQuote(Document $document, DocumentService $documents): RedirectResponse
    {
        $documents->acceptQuote($document);

        return to_route('quotes.show', $document)->with('success', 'Le devis a été accepté par le client.');
    }

    public function rejectQuote(Document $document, DocumentService $documents): RedirectResponse
    {
        $documents->rejectQuote($document);

        return to_route('quotes.show', $document)->with('success', 'Le devis a été refusé.');
    }

    public function convertQuote(Document $document, DocumentService $documents): RedirectResponse
    {
        $invoice = $documents->convertAcceptedQuoteToInvoice($document, auth()->user());

        return to_route('invoices.show', $invoice)
            ->with('success', "La facture {$invoice->number} a été créée à partir du devis.");
    }

    public function markPaid(Document $document, DocumentService $documents): RedirectResponse
    {
        $documents->markInvoicePaid($document);

        return to_route('invoices.show', $document)->with('success', 'Le règlement intégral a été enregistré.');
    }

    public function destroyQuote(Document $document): RedirectResponse
    {
        $this->ensureEditableType($document, Document::TYPE_QUOTE);
        $document->delete();

        return to_route('quotes.index')->with('success', 'Le devis brouillon a été supprimé.');
    }

    public function downloadPdf(Document $document, DocumentPdfService $pdfService): BinaryFileResponse
    {
        $path = $document->pdfPath();

        if (! Storage::disk('local')->exists($path)) {
            $pdfService->generate($document);
        }

        return response()->download(
            Storage::disk('local')->path($path),
            "{$document->number}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'type' => ['nullable', 'in:quote,invoice'],
            'status' => ['nullable', 'in:draft,sent,accepted,rejected,paid,overdue,cancelled'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $query = Document::query()
            ->with('client')
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($filters['status'] ?? null, function (Builder $query, string $status): void {
                if ($status === 'overdue') {
                    $query->where('type', Document::TYPE_INVOICE)
                        ->where('status', 'sent')
                        ->whereDate('due_date', '<', today());
                } else {
                    $query->where('status', $status);
                }
            })
            ->when($filters['from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('issue_date', '>=', $date))
            ->when($filters['to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('issue_date', '<=', $date))
            ->orderBy('issue_date')
            ->orderBy('number');

        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'wb');
            if ($output === false) {
                throw new \RuntimeException('Impossible d’ouvrir le flux CSV.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'Type',
                'Numéro',
                'Statut',
                'Client',
                'E-mail',
                'Date',
                'Échéance',
                'Sous-total HT',
                'TVA',
                'Total TTC',
                'Devise',
            ], ';', '"', '\\');

            $query->chunk(500, function ($documents) use ($output): void {
                foreach ($documents as $document) {
                    fputcsv($output, [
                        $document->type === Document::TYPE_QUOTE ? 'Devis' : 'Facture',
                        $document->number,
                        $document->displayStatus(),
                        $document->client_snapshot['company'] ?: $document->client_snapshot['name'],
                        $document->client_snapshot['email'],
                        $document->issue_date->format('Y-m-d'),
                        ($document->due_date ?? $document->valid_until)?->format('Y-m-d'),
                        $document->subtotal,
                        $document->tax_total,
                        $document->total,
                        $document->currency,
                    ], ';', '"', '\\');
                }
            });

            fclose($output);
        }, 'documents-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function listing(Request $request, string $type): View
    {
        $statuses = $type === Document::TYPE_QUOTE
            ? ['draft', 'sent', 'accepted', 'rejected']
            : ['draft', 'sent', 'paid', 'overdue', 'cancelled'];
        $filters = $request->validate([
            'status' => ['nullable', 'in:'.implode(',', $statuses)],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $documents = Document::query()
            ->with('client')
            ->ofType($type)
            ->when($filters['status'] ?? null, function (Builder $query, string $status): void {
                if ($status === 'overdue') {
                    $query->where('status', 'sent')->whereDate('due_date', '<', today());
                } else {
                    $query->where('status', $status);
                }
            })
            ->when(trim($filters['search'] ?? '') !== '', function (Builder $query) use ($filters): void {
                $search = trim($filters['search']);
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('number', 'like', "%{$search}%")
                        ->orWhereHas('client', fn (Builder $client) => $client->where('name', 'like', "%{$search}%")
                            ->orWhere('company', 'like', "%{$search}%"));
                });
            })
            ->latest('issue_date')
            ->paginate(15)
            ->withQueryString();

        return view('documents.index', compact('documents', 'filters', 'type'));
    }

    private function showDocument(Document $document, string $view): View
    {
        $document->load(['lines', 'client', 'sourceQuote']);

        return view('documents.show', compact('document', 'view'));
    }

    private function ensureEditableType(Document $document, string $type): void
    {
        $this->ensureType($document, $type);
        if (! $document->isEditable()) {
            throw ValidationException::withMessages([
                'document' => 'Un document envoyé ne peut plus être modifié.',
            ]);
        }
    }

    private function ensureType(Document $document, string $type): void
    {
        abort_unless($document->type === $type, 404);
    }

    private function documentRoute(Document $document): RedirectResponse
    {
        return $document->type === Document::TYPE_QUOTE
            ? to_route('quotes.show', $document)
            : to_route('invoices.show', $document);
    }
}
