<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Client;
use App\Models\CompanyProfile;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureDemoDatabase();

        if (User::query()->where('email', 'gestion@studio-noroit.example.test')->exists()) {
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
                'phone' => '+33 5 00 00 00 00',
                'address' => '1 rue de Démonstration',
                'postal_code' => '33000',
                'city' => 'Bordeaux',
                'country' => 'FR',
                'registration_number' => '00000000000000',
                'default_tax_rate' => 20,
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
                    'unit_price' => '780.00',
                    'tax_rate' => '20.00',
                ],
                [
                    'sku' => 'DEMO-SITE',
                    'name' => 'Site vitrine',
                    'description' => 'Création d’un site vitrine responsive de cinq pages.',
                    'unit' => 'forfait',
                    'unit_price' => '1850.00',
                    'tax_rate' => '20.00',
                ],
                [
                    'sku' => 'DEMO-LOCAL',
                    'name' => 'Visibilité locale',
                    'description' => 'Optimisation de la présence locale et configuration de la fiche établissement.',
                    'unit' => 'forfait',
                    'unit_price' => '460.00',
                    'tax_rate' => '20.00',
                ],
                [
                    'sku' => 'DEMO-MAINTENANCE',
                    'name' => 'Maintenance mensuelle',
                    'description' => 'Mises à jour, sauvegarde et contrôle mensuel du site.',
                    'unit' => 'mois',
                    'unit_price' => '95.00',
                    'tax_rate' => '20.00',
                ],
            ])->mapWithKeys(fn (array $attributes) => [
                $attributes['sku'] => Article::create($attributes),
            ]);

            $clients = [
                [
                    'name' => 'Claire Dubois',
                    'company' => 'Café du Canal',
                    'email' => 'claire.dubois@cafe-du-canal.example.test',
                    'phone' => '+33 1 00 00 00 01',
                    'address' => '12 rue des Péniches',
                    'postal_code' => '33000',
                    'city' => 'Bordeaux',
                    'password' => 'ClaireDemo-2026!',
                    'article' => 'DEMO-IDENTITE',
                    'scenario' => 'quote',
                ],
                [
                    'name' => 'Thomas Lefèvre',
                    'company' => 'Brûlerie des Chartrons',
                    'email' => 'thomas.lefevre@brulerie-chartrons.example.test',
                    'phone' => '+33 1 00 00 00 02',
                    'address' => '24 rue des Ateliers',
                    'postal_code' => '33000',
                    'city' => 'Bordeaux',
                    'password' => 'ThomasDemo-2026!',
                    'article' => 'DEMO-SITE',
                    'scenario' => 'invoice',
                ],
                [
                    'name' => 'Sarah Benali',
                    'company' => 'Yoga des Quais',
                    'email' => 'sarah.benali@yoga-des-quais.example.test',
                    'phone' => '+33 1 00 00 00 03',
                    'address' => '7 place des Marées',
                    'postal_code' => '33000',
                    'city' => 'Bordeaux',
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
}
