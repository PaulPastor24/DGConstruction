<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create(['role' => 'engineer']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'portal' => 'staff',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create(['role' => 'engineer']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'portal' => 'staff',
        ]);

        $this->assertGuest();
    }

    public function test_client_portal_authenticates_client_accounts(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $response = $this->post('/login', [
            'email' => $client->email,
            'password' => 'password',
            'portal' => 'client',
        ]);

        $this->assertAuthenticatedAs($client);
        $response->assertRedirect(route('client.dashboard'));
    }

    public function test_client_portal_rejects_staff_accounts(): void
    {
        $staff = User::factory()->create(['role' => 'supervisor']);

        $this->post('/login', [
            'email' => $staff->email,
            'password' => 'password',
            'portal' => 'client',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
