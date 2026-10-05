<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Client;
use App\Models\CompanyProfile;
use App\Models\Document;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureDemoDatabase();

        if (User::query()->where('email', 'gestion@studio-noroit.example.test')->exists()) {
            $this->upgradeExistingDemo();

            return;
        }

        if (User::query()->exists() || CompanyProfile::query()->exists()) {
            throw new RuntimeException('La base de démonstration contient déjà des données inconnues. Aucune donnée n’a été modifiée.');
        }

        DB::transaction(function (): void {
            $company = CompanyProfile::current();
            $company->update([
                'name' => 'Studio Noroît — Démonstration',
                'email' => 'bonjour@studio-noroit.example.test',
                'phone' => '+221 77 000 00 01',
                'address' => '12 avenue du Port (adresse fictive)',
                'postal_code' => '12000',
                'city' => 'Dakar',
                'country' => 'SN',
                'currency' => 'XOF',
                'registration_number' => null,
                'default_tax_rate' => 18,
                'quote_validity_days' => 30,
                'invoice_due_days' => 30,
            ]);

            $administrator = User::create([
                'name' => 'Équipe Studio Noroît',
                'email' => 'gestion@studio-noroit.example.test',
                'password' => 'AtelierDemo-2026!',
                'role' => User::ROLE_ADMIN,
            ]);

            $articles = collect([
                [
                    'sku' => 'DEMO-IDENTITE',
                    'name' => 'Identité visuelle',
                    'description' => 'Conception du logo, palette de couleurs et mini-guide de marque.',
                    'unit' => 'forfait',
                    'unit_price' => '180000.00',
                    'tax_rate' => '18.00',
                ],
                [
                    'sku' => 'DEMO-SITE',
                    'name' => 'Site vitrine',
                    'description' => 'Création d’un site vitrine responsive de cinq pages.',
                    'unit' => 'forfait',
                    'unit_price' => '450000.00',
                    'tax_rate' => '18.00',
                ],
                [
                    'sku' => 'DEMO-LOCAL',
                    'name' => 'Visibilité locale',
                    'description' => 'Optimisation de la présence locale et configuration de la fiche établissement.',
                    'unit' => 'forfait',
                    'unit_price' => '120000.00',
                    'tax_rate' => '18.00',
                ],
                [
                    'sku' => 'DEMO-MAINTENANCE',
                    'name' => 'Maintenance mensuelle',
                    'description' => 'Mises à jour, sauvegarde et contrôle mensuel du site.',
                    'unit' => 'mois',
                    'unit_price' => '35000.00',
                    'tax_rate' => '18.00',
                ],
            ])->mapWithKeys(fn (array $attributes) => [
                $attributes['sku'] => Article::create($attributes),
            ]);

            $clients = [
                [
                    'name' => 'Claire Dubois',
                    'company' => 'Café du Canal',
                    'email' => 'claire.dubois@cafe-du-canal.example.test',
                    'phone' => '+221 77 000 00 02',
                    'address' => '8 rue du Marché (adresse fictive)',
                    'postal_code' => '12000',
                    'city' => 'Dakar',
                    'password' => 'ClaireDemo-2026!',
                    'article' => 'DEMO-IDENTITE',
                    'scenario' => 'quote',
                ],
                [
                    'name' => 'Thomas Lefèvre',
                    'company' => 'Brûlerie des Chartrons',
                    'email' => 'thomas.lefevre@brulerie-chartrons.example.test',
                    'phone' => '+221 77 000 00 03',
                    'address' => '5 rue des Artisans (adresse fictive)',
                    'postal_code' => '12000',
                    'city' => 'Dakar',
                    'password' => 'ThomasDemo-2026!',
                    'article' => 'DEMO-SITE',
                    'scenario' => 'invoice',
                ],
                [
                    'name' => 'Sarah Benali',
                    'company' => 'Yoga des Quais',
                    'email' => 'sarah.benali@yoga-des-quais.example.test',
                    'phone' => '+221 77 000 00 04',
                    'address' => '3 rue des Jardins (adresse fictive)',
                    'postal_code' => '12000',
                    'city' => 'Dakar',
                    'password' => 'SarahDemo-2026!',
                    'article' => 'DEMO-LOCAL',
                    'scenario' => 'paid',
                ],
            ];

            $documents = app(DocumentService::class);

            foreach ($clients as $data) {
                $client = Client::create(collect($data)->except([
                    'password',
                    'article',
                    'scenario',
                ])->all());

                User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'role' => User::ROLE_CLIENT,
                    'client_id' => $client->id,
                ]);

                $quote = $documents->createQuote($administrator, [
                    'client_id' => $client->id,
                    'issue_date' => today()->toDateString(),
                    'valid_until' => today()->addDays(30)->toDateString(),
                    'notes' => 'Exemple de suivi commercial : prestation adaptée à votre activité.',
                    'terms' => 'Projet fictif préparé pour la démonstration.',
                    'lines' => [[
                        'article_id' => $articles->get($data['article'])->id,
                        'quantity' => 1,
                    ]],
                ]);

                $documents->send($quote);

                if ($data['scenario'] === 'quote') {
                    continue;
                }

                $documents->acceptQuote($quote);
                $invoice = $documents->convertAcceptedQuoteToInvoice($quote, $administrator);
                $documents->send($invoice);

                if ($data['scenario'] === 'paid') {
                    $documents->markInvoicePaid($invoice);
                }
            }
        });
    }

    private function ensureDemoDatabase(): void
    {
        $connection = DB::connection();
        $expected = realpath(database_path()).DIRECTORY_SEPARATOR.'demo.sqlite';
        $actual = realpath($connection->getDatabaseName()) ?: $connection->getDatabaseName();

        if ($connection->getDriverName() !== 'sqlite' || strcasecmp($actual, $expected) !== 0) {
            throw new RuntimeException('Les données de démonstration ne peuvent être créées que dans database/demo.sqlite. La base configurée n’a pas été modifiée.');
        }
    }

    private function upgradeExistingDemo(): void
    {
        $profile = CompanyProfile::current();

        DB::transaction(function () use ($profile): void {
            $profile->update([
                'name' => 'Studio Noroît — Démonstration',
                'email' => 'bonjour@studio-noroit.example.test',
                'phone' => '+221 77 000 00 01',
                'address' => '12 avenue du Port (adresse fictive)',
                'postal_code' => '12000',
                'city' => 'Dakar',
                'country' => 'SN',
                'currency' => 'XOF',
                'registration_number' => null,
                'default_tax_rate' => 18,
            ]);
            Client::query()
                ->whereIn('email', [
                    'claire.dubois@cafe-du-canal.example.test',
                    'thomas.lefevre@brulerie-chartrons.example.test',
                    'sarah.benali@yoga-des-quais.example.test',
                ])
                ->get()
                ->each(function (Client $client): void {
                    $client->update([
                        'phone' => match ($client->email) {
                            'claire.dubois@cafe-du-canal.example.test' => '+221 77 000 00 02',
                            'thomas.lefevre@brulerie-chartrons.example.test' => '+221 77 000 00 03',
                            default => '+221 77 000 00 04',
                        },
                        'address' => match ($client->email) {
                            'claire.dubois@cafe-du-canal.example.test' => '8 rue du Marché (adresse fictive)',
                            'thomas.lefevre@brulerie-chartrons.example.test' => '5 rue des Artisans (adresse fictive)',
                            default => '3 rue des Jardins (adresse fictive)',
                        },
                        'postal_code' => '12000',
                        'city' => 'Dakar',
                    ]);
                });

            $prices = [
                'DEMO-IDENTITE' => 180000,
                'DEMO-SITE' => 450000,
                'DEMO-LOCAL' => 120000,
                'DEMO-MAINTENANCE' => 35000,
            ];
            $articles = collect($prices)->mapWithKeys(function (int $price, string $sku): array {
                $article = Article::query()->where('sku', $sku)->firstOrFail();
                $article->update(['unit_price' => $price, 'tax_rate' => 18]);

                return [$article->id => $article->fresh()];
            });
            $companySnapshot = $profile->fresh()->only([
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
            $clientIds = Client::query()
                ->whereIn('email', [
                    'claire.dubois@cafe-du-canal.example.test',
                    'thomas.lefevre@brulerie-chartrons.example.test',
                    'sarah.benali@yoga-des-quais.example.test',
                ])
                ->pluck('id');

            Document::query()
                ->whereIn('client_id', $clientIds)
                ->where('notes', 'like', 'Exemple de suivi commercial%')
                ->with(['lines', 'client'])
                ->get()
                ->each(function (Document $document) use ($articles, $companySnapshot): void {
                    Storage::disk('local')->delete($document->pdfPath());
                    $subtotal = 0;
                    $taxTotal = 0;

                    foreach ($document->lines as $line) {
                        $article = $articles->get($line->article_id);
                        if ($article === null) {
                            continue;
                        }

                        $quantity = (float) $line->quantity;
                        $lineSubtotal = (int) round((float) $article->unit_price * $quantity);
                        $lineTax = (int) round($lineSubtotal * (float) $article->tax_rate / 100);
                        $line->update([
                            'description' => "{$article->name} — {$article->description}",
                            'unit' => $article->unit,
                            'unit_price' => $article->unit_price,
                            'tax_rate' => $article->tax_rate,
                            'subtotal' => $lineSubtotal,
                            'tax_amount' => $lineTax,
                            'total' => $lineSubtotal + $lineTax,
                        ]);
                        $subtotal += $lineSubtotal;
                        $taxTotal += $lineTax;
                    }

                    $clientSnapshot = $document->client->only([
                        'name',
                        'company',
                        'email',
                        'phone',
                        'address',
                        'postal_code',
                        'city',
                    ]);
                    $document->update([
                        'currency' => 'XOF',
                        'client_snapshot' => $clientSnapshot,
                        'company_snapshot' => $companySnapshot,
                        'subtotal' => $subtotal,
                        'tax_total' => $taxTotal,
                        'total' => $subtotal + $taxTotal,
                    ]);
                });
        });
    }
}
