<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackUserPresence;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminAttendanceAndReportActionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(TrackUserPresence::class);
        Schema::create('users', function ($t) {
            $t->id('user_id'); $t->string('role'); $t->boolean('is_active')->default(true); $t->timestamps();
        });
        foreach (['admin' => 'admin_id', 'supervisor' => 'supervisor_id', 'client' => 'client_id'] as $type => $key) {
            Schema::create($type.'_notifications', function ($t) use ($key) {
                $t->id(); $t->unsignedBigInteger($key); $t->string('type'); $t->string('title');
                $t->text('message')->nullable(); $t->json('data')->nullable();
                $t->unsignedBigInteger('related_id')->nullable(); $t->string('related_type')->nullable();
                $t->boolean('is_read')->default(false); $t->timestamps();
            });
        }
        Schema::create('clients', function ($t) { $t->id('client_id'); $t->unsignedBigInteger('user_id'); });
        Schema::create('project_supervisors', function ($t) { $t->integer('project_id'); $t->integer('supervisor_id'); $t->boolean('is_active')->default(true); });
        Schema::create('project_workers', function ($t) { $t->integer('project_id'); $t->integer('worker_id'); $t->boolean('is_active')->default(true); });
        (require database_path('migrations/2026_08_10_000001_create_attendance_schedule_rules_table.php'))->up();
        Schema::create('accomplishment_reports', function ($t) { $t->id('report_id'); $t->string('approval_status'); $t->timestamps(); });
        Schema::create('system_logs', function ($t) {
            $t->id('log_id'); $t->integer('user_id'); $t->string('action'); $t->text('description'); $t->string('ip_address')->nullable();
        });
        DB::table('users')->insert([
            ['user_id' => 1, 'role' => 'admin', 'is_active' => true],
            ['user_id' => 2, 'role' => 'supervisor', 'is_active' => true],
            ['user_id' => 3, 'role' => 'client', 'is_active' => true],
            ['user_id' => 4, 'role' => 'supervisor', 'is_active' => false],
        ]);
        DB::table('clients')->insert(['client_id' => 9, 'user_id' => 3]);
        $this->actingAs(User::findOrFail(1));
    }

    public function test_schedule_notifications_target_the_selected_role_and_include_updated_times(): void
    {
        $data = ['role' => 'supervisor', 'start_time' => '08:00', 'end_time' => '17:00'];
        $this->post(route('admin.attendance.schedules.store'), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('supervisor_notifications', 1);
        $this->assertDatabaseHas('supervisor_notifications', ['supervisor_id' => 2, 'is_read' => false, 'title' => 'New attendance schedule rule set']);
        $this->assertDatabaseCount('client_notifications', 0);
        $this->assertDatabaseCount('admin_notifications', 0);
        $ruleId = DB::table('attendance_schedule_rules')->value('id');
        $data['start_time'] = '09:00';
        $this->put(route('admin.attendance.schedules.update', $ruleId), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('supervisor_notifications', 2);
        $this->assertStringContainsString('09:00', DB::table('supervisor_notifications')->orderByDesc('id')->value('message'));
        $data['role'] = 'client';
        $this->post(route('admin.attendance.schedules.store'), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('client_notifications', ['client_id' => 9, 'is_read' => false]);
    }

    public function test_worker_rule_notifies_only_supervisors_with_active_worker_deployments(): void
    {
        DB::table('project_supervisors')->insert(['project_id' => 10, 'supervisor_id' => 2]);
        DB::table('project_workers')->insert(['project_id' => 10, 'worker_id' => 7]);
        $this->post(route('admin.attendance.schedules.store'), ['role' => 'worker', 'start_time' => '07:00', 'end_time' => '17:00'])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('supervisor_notifications', 1);
        $this->assertDatabaseHas('supervisor_notifications', ['supervisor_id' => 2]);
    }

    public function test_all_admin_roles_can_delete_pending_and_reviewed_reports_with_an_audit_log(): void
    {
        foreach (['admin', 'administrator', 'engineer'] as $role) {
            $user = User::findOrFail(1); $user->role = $role; $user->save(); $this->actingAs($user);
            foreach (['pending', 'approved', 'rejected'] as $status) {
                $id = DB::table('accomplishment_reports')->insertGetId(['approval_status' => $status], 'report_id');
                $this->from('/admin/reports')->delete(route('admin.reports.destroy', $id))->assertRedirect('/admin/reports')->assertSessionHas('success');
                $this->assertDatabaseMissing('accomplishment_reports', ['report_id' => $id]);
            }
        }
        $this->assertDatabaseCount('system_logs', 9);
    }

    public function test_non_admin_cannot_delete_reports(): void
    {
        $id = DB::table('accomplishment_reports')->insertGetId(['approval_status' => 'pending'], 'report_id');
        $this->actingAs(User::findOrFail(2))->delete(route('admin.reports.destroy', $id))->assertRedirect('/login');
        $this->assertDatabaseHas('accomplishment_reports', ['report_id' => $id]);
    }
}
