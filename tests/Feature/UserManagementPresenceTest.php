<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\UserPresence;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserManagementPresenceTest extends TestCase
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
            $table->string('role')->default('engineer');
            $table->string('profile_photo')->nullable();
            $table->string('contact_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
        Cache::flush();
    }

    private function user(string $email, string $role = 'engineer', bool $active = true): User
    {
        return User::create([
            'first_name' => 'Test', 'last_name' => 'User', 'email' => $email,
            'password' => bcrypt('password'), 'role' => $role, 'is_active' => $active,
        ]);
    }

    public function test_management_renders_saved_photo_and_refreshes_photo_and_presence_from_database(): void
    {
        $admin = $this->user('admin@example.com');
        $supervisor = $this->user('supervisor@example.com', 'supervisor');
        $supervisor->update(['profile_photo' => 'profile-photos/new-photo.jpg']);
        $disabled = $this->user('disabled@example.com', 'client', false);
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk()
            ->assertSee('/storage/profile-photos/new-photo.jpg', false)
            ->assertSee('Offline')->assertSee('Online')->assertSee('Disabled');
        $supervisor->update(['profile_photo' => 'profile-photos/updated-photo.jpg']);
        $this->getJson(route('admin.users.activity', ['ids' => [$supervisor->user_id, $disabled->user_id]]))
            ->assertOk()->assertJsonFragment([
                'id' => $supervisor->user_id, 'status' => 'Offline',
                'photo_url' => asset('storage/profile-photos/updated-photo.jpg'),
            ])->assertJsonFragment(['id' => $disabled->user_id, 'status' => 'Disabled']);
    }

    public function test_heartbeat_marks_login_online_and_logout_marks_it_offline(): void
    {
        $user = $this->user('supervisor@example.com', 'supervisor');
        $this->actingAs($user)->postJson(route('presence.heartbeat'))->assertOk();
        $this->assertContains($user->user_id, app(UserPresence::class)->onlineIds([$user->user_id]));
        $this->withCookie(config('session.cookie'), session()->getId());
        $this->post(route('logout'))->assertRedirect('/');
        $this->assertSame([], app(UserPresence::class)->onlineIds([$user->user_id]));
    }

    public function test_inactive_sessions_expire_and_other_device_remains_online_after_one_device_logs_out(): void
    {
        $user = $this->user('supervisor@example.com', 'supervisor');
        $presence = app(UserPresence::class);
        $presence->touch($user->user_id, 'another-phone');
        $this->actingAs($user)->postJson(route('presence.heartbeat'))->assertOk();
        $this->withCookie(config('session.cookie'), session()->getId());
        $this->post(route('logout'))->assertRedirect('/');
        $this->assertContains($user->user_id, $presence->onlineIds([$user->user_id]));
        $this->travel(6)->minutes();
        $this->assertSame([], $presence->onlineIds([$user->user_id]));
    }

    public function test_guests_cannot_send_heartbeats_and_supervisors_cannot_read_admin_activity(): void
    {
        $this->postJson(route('presence.heartbeat'))->assertUnauthorized();
        $supervisor = $this->user('supervisor@example.com', 'supervisor');
        $this->actingAs($supervisor)->getJson(route('admin.users.activity', ['ids' => [$supervisor->user_id]]))
            ->assertRedirect('/login');
    }

    public function test_photo_url_supports_existing_storage_paths_and_missing_photos(): void
    {
        $user = $this->user('avatar@example.com');
        $this->assertNull($user->profile_photo_url);
        $user->profile_photo = '/storage/profile-photos/photo.jpg';
        $this->assertSame(asset('storage/profile-photos/photo.jpg'), $user->profile_photo_url);
    }

    public function test_admin_role_aliases_can_access_user_management(): void
    {
        foreach (['admin', 'administrator', 'engineer'] as $role) {
            $user = $this->user($role.'@example.com', $role);
            $this->actingAs($user)->get(route('admin.users.index'))->assertOk();
        }
    }

    public function test_disabled_accounts_cannot_access_management_with_an_existing_session(): void
    {
        $user = $this->user('disabled-admin@example.com', 'admin', false);
        $this->actingAs($user)->get(route('admin.users.index'))->assertRedirect('/login');
    }

    public function test_disabled_accounts_cannot_log_in_with_a_valid_password(): void
    {
        $user = $this->user('disabled-login@example.com', 'supervisor', false);
        $this->post('/login', [
            'email' => $user->email, 'password' => 'password', 'portal' => 'staff',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
