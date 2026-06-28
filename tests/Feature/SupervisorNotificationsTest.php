<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class SupervisorNotificationsTest extends TestCase
{
    public function test_supervisor_can_open_notifications_workspace(): void
    {
        $user = User::factory()->create([
            'role' => 'supervisor',
            'name' => 'Jane Supervisor',
            'email' => 'jane@example.com',
        ]);

        $response = $this->actingAs($user)->get('/supervisor/notifications');

        $response->assertStatus(200);
        $response->assertSee('Notifications');
    }
}
