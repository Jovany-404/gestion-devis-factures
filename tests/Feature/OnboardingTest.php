<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_registration_creates_the_administrator_and_closes_public_registration(): void
    {
        $this->get(route('register'))->assertOk();

        $this->post(route('register.store'), [
            'name' => 'Camille Martin',
            'email' => 'camille@example.test',
            'password' => 'a sufficiently secure password',
            'password_confirmation' => 'a sufficiently secure password',
        ])->assertRedirect(route('company.edit'));

        $this->assertDatabaseHas('users', [
            'email' => 'camille@example.test',
            'role' => User::ROLE_ADMIN,
        ]);
        $this->get(route('register'))->assertRedirect('/');
    }

    public function test_registration_requires_a_long_confirmed_password(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Camille Martin',
                'email' => 'camille@example.test',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['password']);
        $this->assertDatabaseCount('users', 0);
    }
}
