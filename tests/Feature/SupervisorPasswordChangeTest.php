<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupervisorPasswordChangeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function ($table) {
            $table->id('user_id');
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('supervisor');
            $table->string('contact_number')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function test_supervisor_cannot_set_the_same_password_as_the_current_one(): void
    {
        $user = User::create([
            'name' => 'Supervisor Four',
            'email' => 'supervisor4@example.com',
            'password' => Hash::make('old-password-123'),
            'role' => 'supervisor',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->put(route('supervisor.profile.password'), [
            'current_password' => 'old-password-123',
            'password' => 'old-password-123',
            'password_confirmation' => 'old-password-123',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('old-password-123', $user->fresh()->password));
    }
}
