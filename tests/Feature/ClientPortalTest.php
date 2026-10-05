<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Client;
use App\Models\CompanyProfile;
use App\Models\Document;
use App\Models\User;
use App\Services\DocumentService;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ClientPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $administrator;

    private Client $firstClient;

    private Client $secondClient;

    private User $firstClientAccount;

    private Document $firstQuote;

    private Document $secondQuote;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Storage::fake('local');

        $this->administrator = User::create([
            'name' => 'Compte entreprise',
            'email' => 'studio@example.test',
            'password' => 'secure-company-password',
            'role' => User::ROLE_ADMIN,
        ]);

        CompanyProfile::current()->update([
            'name' => 'Studio de test',
            'email' => 'bonjour@studio.example.test',
        ]);

        $this->firstClient = Client::create([
            'name' => 'Camille Dubois',
            'company' => 'Maison des Fleurs',
            'email' => 'camille@fleurs.example.test',
        ]);
        $this->secondClient = Client::create([
            'name' => 'Alex Martin',
            'company' => 'Atelier du Vélo',
            'email' => 'alex@velo.example.test',
        ]);

        $this->firstClientAccount = User::create([
            'name' => $this->firstClient->name,
            'email' => $this->firstClient->email,
            'password' => 'secure-customer-password',
            'role' => User::ROLE_CLIENT,
            'client_id' => $this->firstClient->id,
        ]);
        User::create([
            'name' => $this->secondClient->name,
            'email' => $this->secondClient->email,
            'password' => 'another-customer-password',
            'role' => User::ROLE_CLIENT,
            'client_id' => $this->secondClient->id,
        ]);

        $article = Article::create([
            'sku' => 'PORTAL-001',
            'name' => 'Création de vitrine',
            'description' => 'Conception d’une identité visuelle',
            'unit' => 'forfait',
            'unit_price' => '500.00',
            'tax_rate' => '20.00',
        ]);

        $documents = app(DocumentService::class);
        $this->firstQuote = $this->createSentQuote($documents, $article, $this->firstClient);
        $this->secondQuote = $this->createSentQuote($documents, $article, $this->secondClient);
    }

    public function test_client_login_opens_a_private_portal_with_only_their_documents(): void
    {
        $this->post(route('login.store'), [
            'email' => $this->firstClient->email,
            'password' => 'secure-customer-password',
        ])->assertRedirect(route('portal.index'));

        $this->get(route('portal.index'))
            ->assertOk()
            ->assertSee($this->firstClient->name)
            ->assertSee($this->firstQuote->number)
            ->assertDontSee($this->secondQuote->number);

        $this->get(route('portal.documents.show', $this->firstQuote))->assertOk();
        $this->get(route('portal.documents.show', $this->secondQuote))->assertNotFound();
        $this->get(route('portal.documents.pdf', $this->secondQuote))->assertNotFound();
    }

    public function test_client_can_answer_only_their_own_sent_quote_and_cannot_open_staff_pages(): void
    {
        $this->actingAs($this->firstClientAccount);

        $this->post(route('portal.quotes.accept', $this->firstQuote))
            ->assertRedirect(route('portal.documents.show', $this->firstQuote));

        $this->assertDatabaseHas('documents', [
            'id' => $this->firstQuote->id,
            'status' => 'accepted',
        ]);

        $this->post(route('portal.quotes.accept', $this->secondQuote))->assertNotFound();
        $this->get(route('clients.index'))->assertForbidden();
        $this->get(route('articles.index'))->assertForbidden();
    }

    public function test_demo_seeder_refuses_to_run_against_the_primary_or_test_database(): void
    {
        $this->expectException(RuntimeException::class);

        (new DemoDataSeeder)->run();
    }

    public function test_customer_api_tokens_cannot_access_the_company_api(): void
    {
        $token = $this->firstClientAccount->createToken('client-portal')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/clients')
            ->assertForbidden();
    }

    public function test_client_accounts_are_not_managed_as_company_staff(): void
    {
        $this->actingAs($this->administrator)
            ->get(route('staff.index'))
            ->assertOk()
            ->assertDontSee($this->firstClientAccount->email);

        $this->actingAs($this->administrator)
            ->delete(route('staff.destroy', $this->firstClientAccount))
            ->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $this->firstClientAccount->id]);
    }

    private function createSentQuote(
        DocumentService $documents,
        Article $article,
        Client $client
    ): Document {
        $quote = $documents->createQuote($this->administrator, [
            'client_id' => $client->id,
            'issue_date' => today()->toDateString(),
            'valid_until' => today()->addDays(30)->toDateString(),
            'lines' => [['article_id' => $article->id, 'quantity' => 1]],
        ]);

        return $documents->send($quote);
    }
}
