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
            'role' => $user->role,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_legacy_office_staff_accounts_can_authenticate_as_staff(): void
    {
        $user = User::factory()->create(['role' => 'office_staff']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'role' => 'staff',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_staff_can_open_their_own_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $this->actingAs($user)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSee('Management Dashboard');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create(['role' => 'engineer']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'role' => $user->role,
        ]);

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
