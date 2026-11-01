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
            $table->boolean('can_delete_users')->default(false);
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

    public function test_designated_admin_can_delete_other_management_roles_when_an_active_admin_remains(): void
    {
        $actor = $this->user('remaining-admin@example.com', 'admin');
        $actor->forceFill(['can_delete_users' => true])->save();
        $this->actingAs($actor);
        foreach (['engineer', 'admin', 'administrator'] as $role) {
            $target = $this->user($role.'-delete@example.com', $role);
            $this->deleteJson(route('admin.users.destroy', $target))->assertOk()
                ->assertJsonPath('success', true);
            $this->assertDatabaseMissing('users', ['user_id' => $target->user_id]);
        }
        $this->assertDatabaseHas('users', ['user_id' => $actor->user_id, 'is_active' => true]);
    }

    public function test_other_admins_can_create_users_but_cannot_delete_or_grant_deletion_permission(): void
    {
        $target = $this->user('protected-target@example.com', 'client');
        foreach (['engineer', 'admin', 'administrator'] as $role) {
            $actor = $this->user($role.'-ordinary@gmail.com', $role);
            $this->actingAs($actor)->get(route('admin.users.index'))->assertOk()
                ->assertDontSee('title="Delete User Account"', false);
            $this->deleteJson(route('admin.users.destroy', $target))->assertForbidden()
                ->assertJsonPath('message', 'Only the designated administrator can delete user accounts.');
            $this->deleteJson(route('profile.destroy'), ['password' => 'password'])->assertForbidden();
            $this->postJson(route('admin.users.store'), [
                'first_name' => 'New', 'last_name' => 'Supervisor', 'email' => $role.'-created@gmail.com',
                'password' => 'TestPass123!', 'password_confirmation' => 'TestPass123!',
                'role' => 'supervisor', 'can_delete_users' => true,
            ])->assertOk();
            $created = User::where('email', $role.'-created@gmail.com')->firstOrFail();
            $this->assertFalse($created->canDeleteUsers());
            $this->putJson(route('admin.users.update', $actor), [
                'first_name' => 'Test', 'last_name' => 'User', 'email' => $actor->email,
                'role' => $role, 'is_active' => 1, 'can_delete_users' => true,
            ])->assertOk();
            $this->assertFalse($actor->fresh()->canDeleteUsers());
        }
        $this->assertDatabaseHas('users', ['user_id' => $target->user_id]);
    }

    public function test_other_admin_cannot_take_over_the_designated_account(): void
    {
        $owner = $this->user('dgcon.ad@gmail.com', 'engineer');
        $owner->forceFill(['can_delete_users' => true])->save();
        $other = $this->user('other-admin@gmail.com', 'admin');
        $this->actingAs($other)->putJson(route('admin.users.update', $owner), [
            'first_name' => 'Changed', 'last_name' => 'Owner', 'email' => 'changed-owner@gmail.com',
            'password' => 'TestPass123!', 'password_confirmation' => 'TestPass123!', 'role' => 'engineer',
        ])->assertForbidden();
        $this->patchJson(route('admin.users.toggleStatus', $owner))->assertForbidden();
        $this->assertDatabaseHas('users', ['user_id' => $owner->user_id, 'email' => 'dgcon.ad@gmail.com', 'is_active' => true]);
    }

    public function test_migration_grants_permission_only_to_the_existing_designated_account(): void
    {
        Schema::table('users', fn ($table) => $table->dropColumn('can_delete_users'));
        $owner = $this->user('dgcon.ad@gmail.com', 'engineer');
        $other = $this->user('another-admin@gmail.com', 'admin');
        (require database_path('migrations/2026_10_31_000001_add_user_deletion_permission.php'))->up();
        $this->assertTrue($owner->fresh()->canDeleteUsers());
        $this->assertFalse($other->fresh()->canDeleteUsers());
        $this->actingAs($owner->fresh())->get(route('admin.users.index'))->assertOk()
            ->assertSee('title="Delete User Account"', false);
        $owner->update(['email' => 'renamed-owner@gmail.com']);
        $replacement = $this->user('dgcon.ad@gmail.com', 'admin');
        $this->assertTrue($owner->fresh()->canDeleteUsers());
        $this->assertFalse($replacement->fresh()->canDeleteUsers());
    }

    public function test_management_self_deletion_is_blocked_through_both_routes(): void
    {
        foreach (['engineer', 'admin', 'administrator'] as $role) {
            $actor = $this->user($role.'-self@example.com', $role);
            $actor->forceFill(['can_delete_users' => true])->save();
            $this->actingAs($actor);
            $this->deleteJson(route('admin.users.destroy', $actor))->assertUnprocessable()
                ->assertJsonPath('message', 'You cannot delete your own account while logged in.');
            $this->deleteJson(route('profile.destroy'), ['password' => 'password'])
                ->assertUnprocessable()->assertJsonValidationErrors('account');
            $this->assertAuthenticatedAs($actor);
            $this->assertDatabaseHas('users', ['user_id' => $actor->user_id]);
        }
    }

    public function test_last_active_admin_cannot_be_disabled_or_demoted_even_with_inactive_admins(): void
    {
        $actor = $this->user('last-active@gmail.com', 'admin');
        $this->user('inactive-peer@example.com', 'engineer', false);
        $this->actingAs($actor);
        foreach (['engineer', 'admin', 'administrator'] as $role) {
            $actor->update(['role' => $role]);
            $this->patch(route('admin.users.toggleStatus', $actor))->assertRedirect()->assertSessionHas('error');
            $this->assertTrue($actor->fresh()->is_active);
            $payload = ['first_name' => 'Test', 'last_name' => 'User', 'email' => $actor->email,
                'role' => 'supervisor', 'is_active' => 1];
            $this->putJson(route('admin.users.update', $actor), $payload)->assertUnprocessable()
                ->assertJsonFragment(['message' => 'At least one active Engineer/Administrator account must remain. Create or activate another administrator first.']);
            $payload['role'] = $role; $payload['is_active'] = 0;
            $this->putJson(route('admin.users.update', $actor), $payload)->assertUnprocessable();
            $this->assertDatabaseHas('users', ['user_id' => $actor->user_id, 'role' => $role, 'is_active' => true]);
        }
    }

    public function test_another_active_admin_allows_disabling_or_demoting_a_management_account(): void
    {
        $actor = $this->user('remaining@example.com', 'administrator');
        $target = $this->user('changeable@gmail.com', 'engineer');
        $this->actingAs($actor)->patch(route('admin.users.toggleStatus', $target))
            ->assertRedirect()->assertSessionHas('success');
        $this->assertFalse($target->fresh()->is_active);
        $this->putJson(route('admin.users.update', $target), [
            'first_name' => 'Test', 'last_name' => 'User', 'email' => $target->email,
            'role' => 'supervisor', 'is_active' => 1,
        ])->assertOk()->assertJsonPath('success', true);
    }

    public function test_guard_rejects_deleting_the_last_active_management_account(): void
    {
        $actor = $this->user('sole-admin@example.com', 'administrator');
        $this->user('disabled-admin@example.com', 'admin', false);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        \Illuminate\Support\Facades\DB::transaction(function () use ($actor) {
            app(\App\Services\AdminAccountGuard::class)->ensureAdminRemains($actor);
        });
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
        $admin->forceFill(['can_delete_users' => true])->save();
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
