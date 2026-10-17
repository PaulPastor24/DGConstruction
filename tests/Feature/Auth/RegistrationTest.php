<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_redirects_to_login(): void
    {
        $response = $this->get('/register');

        $response->assertRedirect('/login');
    }

    public function test_guests_cannot_create_accounts(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'client',
        ]);

        $this->assertGuest();
        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }
}
