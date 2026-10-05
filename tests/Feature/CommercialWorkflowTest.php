<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Client;
use App\Models\CompanyProfile;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommercialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $administrator;

    private Client $client;

    private Article $article;

    protected function setUp(): void
    {
        parent::setUp();

        $this->administrator = User::create([
            'name' => 'Camille Martin',
            'email' => 'camille@example.test',
            'password' => 'a secure test password',
            'role' => User::ROLE_ADMIN,
        ]);
        $this->actingAs($this->administrator);
        $profile = CompanyProfile::current();
        $profile->update([
            'name' => 'Atelier Exemple',
            'email' => 'contact@atelier.example',
            'address' => '12 rue des Lilas',
            'postal_code' => '69001',
            'city' => 'Lyon',
        ]);
        $this->client = Client::create([
            'name' => 'Alex Martin',
            'company' => 'Société Martin',
            'email' => 'alex@martin.example',
            'address' => '8 avenue Victor-Hugo',
            'postal_code' => '69002',
            'city' => 'Lyon',
        ]);
        $this->article = Article::create([
            'sku' => 'AUDIT-001',
            'name' => 'Accompagnement professionnel',
            'description' => 'Prestation de conseil',
            'unit' => 'heure',
            'unit_price' => '125.00',
            'tax_rate' => '20.00',
        ]);
    }

    public function test_quote_uses_catalogue_prices_calculates_tax_and_assigns_a_yearly_number(): void
    {
        $response = $this->post(route('quotes.store'), $this->quotePayload([
            'unit_price' => '0.01',
            'tax_rate' => '0',
        ]));

        $quote = Document::query()->sole();
        $this->assertSame('DEV-'.now()->format('Y').'-0001', $quote->number);
        $this->assertSame('draft', $quote->status);
        $this->assertSame('312.50', $quote->subtotal);
        $this->assertSame('62.50', $quote->tax_total);
        $this->assertSame('375.00', $quote->total);
        $this->assertSame('125.00', $quote->lines()->sole()->unit_price);
        $this->assertSame('Alex Martin', $quote->client_snapshot['name']);
        $this->assertSame('Atelier Exemple', $quote->company_snapshot['name']);
        $response->assertRedirect(route('quotes.show', $quote));
    }

    public function test_xof_quotes_use_whole_cfa_amounts_and_preserve_currency(): void
    {
        CompanyProfile::current()->update(['currency' => 'XOF']);
        $this->article->update(['unit_price' => '10000', 'tax_rate' => '18']);

        $this->post(route('quotes.store'), $this->quotePayload(['quantity' => 1.25]))
            ->assertSessionHasNoErrors();

        $quote = Document::query()->sole();
        $line = $quote->lines()->sole();
        $this->assertSame('XOF', $quote->currency);
        $this->assertSame('12500.00', $line->subtotal);
        $this->assertSame('2250.00', $line->tax_amount);
        $this->assertSame('14750.00', $quote->total);
        $this->assertSame('14 750 FCFA', $quote->formatAmount($quote->total));
        $this->get('/')->assertOk()->assertSee('12 500 FCFA HT');
    }

    public function test_xof_catalogue_prices_are_saved_as_whole_cfa_amounts(): void
    {
        CompanyProfile::current()->update(['currency' => 'XOF']);

        $this->post(route('articles.store'), [
            'sku' => 'XOF-001',
            'name' => 'Prestation XOF',
            'description' => 'Tarif de test',
            'unit' => 'forfait',
            'unit_price' => '123.50',
            'tax_rate' => '18',
        ])->assertRedirect(route('articles.index'));

        $this->assertSame('124.00', Article::query()->where('sku', 'XOF-001')->sole()->unit_price);
    }

    public function test_document_list_pages_render_the_status_filters(): void
    {
        $this->get(route('quotes.index'))
            ->assertOk()
            ->assertSee('Brouillon')
            ->assertSee('Envoyé')
            ->assertSee('Accepté');

        $this->get(route('invoices.index'))
            ->assertOk()
            ->assertSee('En retard')
            ->assertSee('Payé');

        $this->get(route('staff.index'))
            ->assertOk()
            ->assertSee('Administrateur');
    }

    public function test_quote_lifecycle_converts_once_to_a_snapshot_invoice_and_records_payment(): void
    {
        $this->post(route('quotes.store'), $this->quotePayload())->assertSessionHasNoErrors();
        $quote = Document::query()->sole();
        $originalLinePrice = $quote->lines()->sole()->unit_price;

        $this->post(route('quotes.send', $quote))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('documents', ['id' => $quote->id, 'status' => 'sent']);
        $this->post(route('quotes.accept', $quote))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('documents', ['id' => $quote->id, 'status' => 'accepted']);

        $this->post(route('quotes.convert', $quote))->assertSessionHasNoErrors();
        $invoice = Document::query()->where('type', Document::TYPE_INVOICE)->sole();

        $this->assertSame('FAC-'.now()->format('Y').'-0001', $invoice->number);
        $this->assertSame($quote->id, $invoice->quote_id);
        $this->assertSame($originalLinePrice, $invoice->lines()->sole()->unit_price);
        $this->assertSame($quote->total, $invoice->total);

        $this->from(route('quotes.show', $quote))
            ->post(route('quotes.convert', $quote))
            ->assertSessionHasErrors('document');
        $this->assertSame(1, Document::query()->where('type', Document::TYPE_INVOICE)->count());

        $this->post(route('invoices.send', $invoice))->assertSessionHasNoErrors();
        $this->post(route('invoices.pay', $invoice))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('documents', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);
        $this->assertNotNull($invoice->fresh()->paid_at);
    }

    public function test_quote_validation_rejects_unknown_or_inactive_articles_and_empty_lines(): void
    {
        $this->from(route('quotes.create'))
            ->post(route('quotes.store'), [
                'client_id' => $this->client->id,
                'issue_date' => today()->toDateString(),
                'lines' => [['article_id' => 9999, 'quantity' => 1]],
            ])
            ->assertSessionHasErrors(['lines.0.article_id']);

        $this->article->update(['is_active' => false]);
        $this->post(route('quotes.store'), $this->quotePayload())
            ->assertSessionHasErrors(['lines.0.article_id']);

        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('document_sequences', 0);
    }

    public function test_only_accountants_and_administrators_can_record_invoice_payments(): void
    {
        $this->post(route('quotes.store'), $this->quotePayload());
        $quote = Document::query()->sole();
        $this->post(route('quotes.send', $quote));
        $this->post(route('quotes.accept', $quote));
        $this->post(route('quotes.convert', $quote));
        $invoice = Document::query()->where('type', Document::TYPE_INVOICE)->sole();
        $this->post(route('invoices.send', $invoice));

        $salesUser = User::create([
            'name' => 'Commercial',
            'email' => 'commercial@example.test',
            'password' => 'a secure test password',
            'role' => User::ROLE_SALES,
        ]);
        $this->actingAs($salesUser)
            ->post(route('invoices.pay', $invoice))
            ->assertForbidden();
        $this->assertDatabaseHas('documents', ['id' => $invoice->id, 'status' => 'sent']);
    }

    public function test_api_access_requires_a_token_and_honours_token_abilities(): void
    {
        $this->app['auth']->guard('web')->logout();
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/v1/articles')->assertUnauthorized();

        CompanyProfile::current()->update(['currency' => 'XOF']);
        $readToken = $this->administrator->createToken('read-only', ['documents:read'])->plainTextToken;
        $this->withToken($readToken)
            ->getJson('/api/v1/articles')
            ->assertOk()
            ->assertJsonPath('data.0.currency', 'XOF');
        $this->withToken($readToken)->postJson('/api/v1/quotes', $this->quotePayload())
            ->assertForbidden();

        $writeToken = $this->administrator->createToken('sales-integration', [
            'documents:read',
            'documents:write',
        ])->plainTextToken;
        $this->assertContains(
            'documents:write',
            $this->administrator->tokens()->where('name', 'sales-integration')->sole()->abilities
        );
        $this->app['auth']->forgetGuards();
        $this->withoutToken();
        $response = $this->withToken($writeToken)
            ->postJson('/api/v1/quotes', $this->quotePayload());

        $this->assertSame(201, $response->status(), $response->getContent());
        $response->assertJsonPath('data.type', 'quote')
            ->assertJsonPath('data.status', 'draft');
    }

    public function test_pdf_and_filtered_csv_exports_are_generated(): void
    {
        Storage::fake('local');
        $this->post(route('quotes.store'), $this->quotePayload());
        $quote = Document::query()->sole();

        $this->get(route('documents.pdf', $quote))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertTrue(Storage::disk('local')->exists($quote->pdfPath()));

        $csv = $this->get(route('documents.export', ['type' => 'quote']))->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString($quote->number, $csv);
        $this->assertStringContainsString('Société Martin', $csv);
    }

    /**
     * @return array<string, mixed>
     */
    private function quotePayload(array $extraLineValues = []): array
    {
        return [
            'client_id' => $this->client->id,
            'issue_date' => today()->toDateString(),
            'lines' => [[
                'article_id' => $this->article->id,
                'quantity' => '2.50',
                ...$extraLineValues,
            ]],
        ];
    }
}
