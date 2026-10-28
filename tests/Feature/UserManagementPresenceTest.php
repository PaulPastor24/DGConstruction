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
        Schema::create('system_logs', function ($table) {
            $table->increments('log_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 200);
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
        Schema::create('projects', function ($table) {
            $table->increments('project_id');
            $table->unsignedBigInteger('engineer_id')->nullable();
        });
        Schema::create('project_supervisors', function ($table) {
            $table->unsignedBigInteger('supervisor_id');
            $table->unsignedBigInteger('project_id');
            $table->date('assigned_date')->nullable();
            $table->boolean('is_active')->default(true);
        });
        Schema::create('attendance_logs', function ($table) {
            $table->increments('log_id');
            $table->unsignedBigInteger('recorded_by')->nullable();
        });
        Schema::create('accomplishment_reports', function ($table) {
            $table->increments('report_id');
            $table->unsignedBigInteger('submitted_by')->nullable();
        });
        Schema::create('clients', function ($table) {
            $table->increments('client_id');
            $table->unsignedBigInteger('user_id');
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

    public function test_user_status_changes_are_logged_and_visible_in_activity_logs(): void
    {
        $admin = $this->user('admin@example.com');
        $target = $this->user('target@example.com', 'supervisor');

        $this->actingAs($admin)
            ->patch(route('admin.users.toggleStatus', $target))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('system_logs', [
            'user_id' => $admin->user_id,
            'action' => 'User Status Changed',
        ]);
        $this->get(route('admin.users.auditLog'))
            ->assertOk()
            ->assertSee('User Activity Logs')
            ->assertSee('disabled');
    }

    public function test_user_creation_update_and_deletion_are_logged_and_searchable(): void
    {
        $admin = $this->user('admin@example.com');
        $this->actingAs($admin);

        $this->post(route('admin.users.store'), [
            'first_name' => 'New',
            'last_name' => 'Supervisor',
            'email' => 'new-supervisor@gmail.com',
            'password' => 'TestPass123!',
            'password_confirmation' => 'TestPass123!',
            'role' => 'supervisor',
        ])->assertRedirect(route('admin.users.index'));

        $createdUser = User::where('email', 'new-supervisor@gmail.com')->firstOrFail();
        $this->assertDatabaseHas('system_logs', [
            'user_id' => $admin->user_id,
            'action' => 'User Created',
        ]);

        $this->put(route('admin.users.update', $createdUser), [
            'first_name' => 'New',
            'last_name' => 'Lead',
            'email' => 'new-supervisor@gmail.com',
            'role' => 'supervisor',
            'is_active' => 1,
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('system_logs', [
            'user_id' => $admin->user_id,
            'action' => 'User Updated',
            'description' => "Updated account #{$createdUser->user_id} for New Lead. Changed fields: last_name.",
        ]);

        $this->delete(route('admin.users.destroy', $createdUser))
            ->assertRedirect()
            ->assertSessionHas('success', 'User deleted successfully!');

        $this->assertDatabaseHas('system_logs', [
            'user_id' => $admin->user_id,
            'action' => 'User Deleted',
            'description' => "Deleted account #{$createdUser->user_id} for New Lead (new-supervisor@gmail.com), role supervisor.",
        ]);

        $this->get(route('admin.users.auditLog'))
            ->assertOk()
            ->assertSee('User Created')
            ->assertSee('User Updated')
            ->assertSee('User Deleted')
            ->assertSee('new-supervisor@gmail.com');

        $this->get(route('admin.users.auditLog', ['search' => 'New Lead']))
            ->assertOk()
            ->assertSee('User Updated')
            ->assertSee('User Deleted');

        $this->get(route('admin.users.auditLog', ['search' => 'admin@example.com']))
            ->assertOk()
            ->assertSee('User Created')
            ->assertSee('User Updated')
            ->assertSee('User Deleted');
    }

    public function test_audit_search_treats_sql_syntax_characters_as_plain_text(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $this->actingAs($admin);

        \App\Models\SystemLog::create([
            'user_id' => $admin->user_id,
            'action' => 'User Updated',
            'description' => "Updated account O'Brien' --",
            'ip_address' => '127.0.0.1',
        ]);
        \App\Models\SystemLog::create([
            'user_id' => $admin->user_id,
            'action' => 'User Deleted',
            'description' => 'Unrelated account update',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->get(route('admin.users.auditLog', ['search' => "O'Brien' --"]));

        $response->assertOk()
            ->assertSee('O&#039;Brien', false)
            ->assertDontSee('Unrelated account update');
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
