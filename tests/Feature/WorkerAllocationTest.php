<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackUserPresence;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WorkerAllocationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(TrackUserPresence::class);
        $this->withoutVite();
        Schema::create('users', function ($table) {
            $table->id('user_id'); $table->string('name')->default('Test user');
            $table->string('role'); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('workers', function ($table) {
            $table->increments('worker_id'); $table->string('first_name'); $table->string('last_name');
            $table->string('trade')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('projects', function ($table) {
            $table->increments('project_id'); $table->string('project_name'); $table->string('status'); $table->timestamps();
        });
        (require database_path('migrations/2026_09_13_002248_create_project_workers_table_fix.php'))->up();
        Schema::create('attendance_logs', function ($table) {
            $table->id('log_id'); $table->integer('deployment_id'); $table->date('log_date');
        });
        Schema::create('system_logs', function ($table) {
            $table->id('log_id'); $table->integer('user_id'); $table->string('action');
            $table->text('description'); $table->string('ip_address')->nullable();
        });
        DB::table('users')->insert(['user_id' => 1, 'role' => 'supervisor']);
        DB::table('workers')->insert([
            ['worker_id' => 1, 'first_name' => 'Global', 'last_name' => 'Worker', 'is_active' => true],
            ['worker_id' => 2, 'first_name' => 'Inactive', 'last_name' => 'Worker', 'is_active' => false],
        ]);
        DB::table('projects')->insert([
            ['project_id' => 1, 'project_name' => 'Original project', 'status' => 'ongoing'],
            ['project_id' => 2, 'project_name' => 'Other supervisor project', 'status' => 'ongoing'],
            ['project_id' => 3, 'project_name' => 'Archived project', 'status' => 'archived'],
        ]);
        DB::table('project_workers')->insert(['project_id' => 1, 'worker_id' => 1, 'deployed_date' => '2026-10-01']);
        DB::table('attendance_logs')->insert(['deployment_id' => 1, 'log_date' => '2026-10-01']);
        $this->actingAs(User::findOrFail(1));
    }

    public function test_both_roles_can_see_all_workers_and_projects_without_supervisor_assignments(): void
    {
        foreach (['admin', 'administrator', 'engineer', 'supervisor'] as $role) {
            $user = User::findOrFail(1); $user->update(['role' => $role]); $this->actingAs($user);
            $prefix = $role === 'supervisor' ? 'supervisor' : 'admin';
            $this->get(route($prefix.'.workers.index'))->assertOk()->assertSee('Global Worker')
                ->assertSee('Inactive Worker')->assertSee('Other supervisor project')->assertDontSee('Archived project');
        }
    }

    public function test_all_roles_can_assign_without_ending_other_assignments_and_repeat_safely(): void
    {
        foreach (['admin', 'administrator', 'engineer', 'supervisor'] as $role) {
            $user = User::findOrFail(1); $user->update(['role' => $role]); $this->actingAs($user);
            $prefix = $role === 'supervisor' ? 'supervisor' : 'admin';
            $this->postJson(route($prefix.'.workers.allocate', 1), ['project_id' => 2, 'mode' => 'assign'])
                ->assertOk()->assertJson(['success' => true]);
        }
        $this->assertDatabaseCount('project_workers', 2);
        $this->assertSame(2, DB::table('project_workers')->where('is_active', true)->count());
        $this->assertDatabaseCount('system_logs', 4);
    }

    public function test_transfer_preserves_historical_attendance_and_can_transfer_back(): void
    {
        $route = route('supervisor.workers.allocate', 1);
        $this->postJson($route, ['project_id' => 2, 'mode' => 'transfer'])->assertOk();
        $this->assertDatabaseHas('project_workers', ['deployment_id' => 1, 'project_id' => 1, 'is_active' => false]);
        $this->assertDatabaseHas('attendance_logs', ['deployment_id' => 1, 'log_date' => '2026-10-01']);
        $this->assertDatabaseHas('project_workers', ['project_id' => 2, 'worker_id' => 1, 'is_active' => true]);
        $this->postJson($route, ['project_id' => 1, 'mode' => 'transfer'])->assertOk();
        $this->assertDatabaseCount('project_workers', 3);
        $this->assertSame(1, DB::table('project_workers')->where('is_active', true)->count());
        $this->assertDatabaseHas('project_workers', ['deployment_id' => 1, 'is_active' => false]);
    }

    public function test_invalid_allocations_do_not_change_current_deployments(): void
    {
        foreach ([['project_id' => 3, 'mode' => 'transfer'], ['project_id' => 99, 'mode' => 'assign'], ['project_id' => 2, 'mode' => 'invalid']] as $data) {
            $this->postJson(route('supervisor.workers.allocate', 1), $data)->assertUnprocessable();
        }
        $this->postJson(route('supervisor.workers.allocate', 2), ['project_id' => 2, 'mode' => 'assign'])->assertUnprocessable();
        $this->assertDatabaseCount('project_workers', 1);
        $this->assertDatabaseHas('project_workers', ['deployment_id' => 1, 'is_active' => true]);
    }

    public function test_clients_cannot_view_or_allocate_workers(): void
    {
        $user = User::findOrFail(1); $user->update(['role' => 'client']); $this->actingAs($user);
        foreach (['admin', 'supervisor'] as $prefix) {
            $this->get(route($prefix.'.workers.index'))->assertRedirect('/login');
            $this->postJson(route($prefix.'.workers.allocate', 1), ['project_id' => 2, 'mode' => 'transfer'])->assertRedirect('/login');
        }
        $this->assertDatabaseCount('project_workers', 1);
    }
}
