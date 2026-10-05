<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_client_summary_and_recent_clients(): void
    {
        $client = Client::create([
            'name' => 'Camille Martin',
            'email' => 'camille@example.com',
            'city' => 'Lyon',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('1')
            ->assertSee('Camille Martin')
            ->assertSee('camille@example.com');
    }

    public function test_client_can_be_created_with_validated_contact_details(): void
    {
        $this->post(route('clients.store'), [
            'name' => 'Camille Martin',
            'company' => 'Atelier Martin',
            'email' => 'camille@example.com',
            'phone' => '0601020304',
            'address' => '12 rue des Lilas',
            'postal_code' => '69001',
            'city' => 'Lyon',
            'notes' => 'Contacter le matin',
        ])
            ->assertRedirect();

        $this->assertDatabaseHas('clients', [
            'name' => 'Camille Martin',
            'company' => 'Atelier Martin',
            'email' => 'camille@example.com',
            'city' => 'Lyon',
        ]);
    }

    public function test_client_name_is_required_and_email_must_be_valid(): void
    {
        $this->from(route('clients.create'))
            ->post(route('clients.store'), [
                'name' => '',
                'email' => 'adresse-invalide',
            ])
            ->assertRedirect(route('clients.create'))
            ->assertSessionHasErrors(['name', 'email']);

        $this->assertDatabaseCount('clients', 0);
    }

    public function test_clients_can_be_searched_by_company_or_city(): void
    {
        Client::create(['name' => 'Alice Durand', 'company' => 'Studio Étoile', 'city' => 'Paris']);
        Client::create(['name' => 'Bruno Petit', 'company' => 'Bureau Nord', 'city' => 'Lille']);

        $this->get(route('clients.index', ['search' => 'Étoile']))
            ->assertOk()
            ->assertSee('Alice Durand')
            ->assertDontSee('Bruno Petit');

        $this->get(route('clients.index', ['search' => 'Lille']))
            ->assertOk()
            ->assertSee('Bruno Petit')
            ->assertDontSee('Alice Durand');
    }

    public function test_client_can_be_updated_and_deleted(): void
    {
        $client = Client::create(['name' => 'Alice Durand', 'city' => 'Paris']);

        $this->put(route('clients.update', $client), [
            'name' => 'Alice Durand',
            'city' => 'Nantes',
        ])->assertRedirect(route('clients.show', $client));

        $this->assertDatabaseHas('clients', ['id' => $client->id, 'city' => 'Nantes']);

        $this->delete(route('clients.destroy', $client))
            ->assertRedirect(route('clients.index'));

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }
}
