<?php

namespace Tests\Feature;

use App\Models\ConstructionPhase;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminPhaseManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('engineer');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id('project_id');
            $table->string('project_name');
            $table->string('project_location');
            $table->bigInteger('client_id');
            $table->bigInteger('engineer_id');
            $table->date('start_date');
            $table->date('target_end_date');
            $table->date('actual_end_date')->nullable();
            $table->string('status')->default('ongoing');
            $table->timestamps();
        });

        Schema::create('construction_phases', function (Blueprint $table) {
            $table->id('phase_id');
            $table->bigInteger('project_id');
            $table->string('phase_name');
            $table->integer('phase_order');
            $table->date('planned_start_date');
            $table->date('planned_end_date');
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->decimal('completion_percentage', 5, 2)->default(0.00);
            $table->string('status')->default('not_started');
            $table->timestamps();
        });

        Schema::create('system_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->bigInteger('user_id')->nullable();
            $table->string('action');
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('construction_phases');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_engineer_can_create_phase_with_json_response(): void
    {
        $engineer = User::create([
            'name' => 'Engineer User',
            'email' => 'engineer-phase@example.com',
            'password' => bcrypt('password'),
            'role' => 'engineer',
            'is_active' => true,
        ]);

        $project = Project::create([
            'project_name' => 'Test Project',
            'project_location' => 'Test Location',
            'client_id' => 1,
            'engineer_id' => $engineer->user_id,
            'start_date' => now()->toDateString(),
            'target_end_date' => now()->addMonth()->toDateString(),
            'status' => 'ongoing',
        ]);

        $this->actingAs($engineer)
            ->postJson(route('admin.phases.store'), [
                'project_id' => $project->project_id,
                'phase_name' => 'Site Preparation',
                'phase_order' => 1,
                'planned_start_date' => now()->toDateString(),
                'planned_end_date' => now()->addWeek()->toDateString(),
                'status' => 'not_started',
            ])
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_engineer_receives_validation_errors_for_invalid_phase_update(): void
    {
        $engineer = User::create([
            'name' => 'Engineer User Two',
            'email' => 'engineer-phase-update@example.com',
            'password' => bcrypt('password'),
            'role' => 'engineer',
            'is_active' => true,
        ]);

        $project = Project::create([
            'project_name' => 'Test Project Two',
            'project_location' => 'Test Location',
            'client_id' => 2,
            'engineer_id' => $engineer->user_id,
            'start_date' => now()->toDateString(),
            'target_end_date' => now()->addMonth()->toDateString(),
            'status' => 'ongoing',
        ]);

        $phase = ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Foundation',
            'phase_order' => 1,
            'planned_start_date' => now()->toDateString(),
            'planned_end_date' => now()->addWeek()->toDateString(),
            'completion_percentage' => 0,
            'status' => 'not_started',
        ]);

        $this->actingAs($engineer)
            ->putJson(route('admin.phases.update', [$project->project_id, $phase->phase_id]), [
                'phase_name' => '',
                'phase_order' => 0,
                'planned_start_date' => now()->addDay()->toDateString(),
                'planned_end_date' => now()->toDateString(),
                'completion_percentage' => 101,
                'status' => 'invalid_status',
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_engineer_cannot_update_phase_without_making_changes(): void
    {
        $engineer = User::create([
            'name' => 'Engineer User Three',
            'email' => 'engineer-phase-unchanged@example.com',
            'password' => bcrypt('password'),
            'role' => 'engineer',
            'is_active' => true,
        ]);

        $project = Project::create([
            'project_name' => 'Test Project Three',
            'project_location' => 'Test Location',
            'client_id' => 3,
            'engineer_id' => $engineer->user_id,
            'start_date' => now()->toDateString(),
            'target_end_date' => now()->addMonth()->toDateString(),
            'status' => 'ongoing',
        ]);

        $phase = ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Foundation',
            'phase_order' => 1,
            'planned_start_date' => now()->toDateString(),
            'planned_end_date' => now()->addWeek()->toDateString(),
            'completion_percentage' => 0,
            'status' => 'not_started',
        ]);

        $this->actingAs($engineer)
            ->putJson(route('admin.phases.update', [$project->project_id, $phase->phase_id]), [
                'phase_name' => 'Foundation',
                'phase_order' => 1,
                'planned_start_date' => $phase->planned_start_date->toDateString(),
                'planned_end_date' => $phase->planned_end_date->toDateString(),
                'actual_start_date' => $phase->actual_start_date?->toDateString(),
                'actual_end_date' => $phase->actual_end_date?->toDateString(),
                'completion_percentage' => (string) $phase->completion_percentage,
                'status' => $phase->status,
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_cannot_mark_completed_if_progress_less_than_100(): void
    {
        $engineer = User::create([
            'name' => 'Engineer User Four',
            'email' => 'engineer-phase-complete@example.com',
            'password' => bcrypt('password'),
            'role' => 'engineer',
            'is_active' => true,
        ]);

        $project = Project::create([
            'project_name' => 'Test Project Four',
            'project_location' => 'Test Location',
            'client_id' => 4,
            'engineer_id' => $engineer->user_id,
            'start_date' => now()->toDateString(),
            'target_end_date' => now()->addMonth()->toDateString(),
            'status' => 'ongoing',
        ]);

        $phase = ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Finishing',
            'phase_order' => 1,
            'planned_start_date' => now()->toDateString(),
            'planned_end_date' => now()->addWeek()->toDateString(),
            'completion_percentage' => 50,
            'status' => 'in_progress',
        ]);

        $this->actingAs($engineer)
            ->putJson(route('admin.phases.update', [$project->project_id, $phase->phase_id]), [
                'phase_name' => 'Finishing',
                'phase_order' => 1,
                'planned_start_date' => $phase->planned_start_date->toDateString(),
                'planned_end_date' => $phase->planned_end_date->toDateString(),
                'completion_percentage' => 50,
                'status' => 'completed',
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_completion_100_auto_sets_status_completed(): void
    {
        $engineer = User::create([
            'name' => 'Engineer User Five',
            'email' => 'engineer-phase-auto@example.com',
            'password' => bcrypt('password'),
            'role' => 'engineer',
            'is_active' => true,
        ]);

        $project = Project::create([
            'project_name' => 'Test Project Five',
            'project_location' => 'Test Location',
            'client_id' => 5,
            'engineer_id' => $engineer->user_id,
            'start_date' => now()->toDateString(),
            'target_end_date' => now()->addMonth()->toDateString(),
            'status' => 'ongoing',
        ]);

        $phase = ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Finishing',
            'phase_order' => 1,
            'planned_start_date' => now()->toDateString(),
            'planned_end_date' => now()->addWeek()->toDateString(),
            'completion_percentage' => 90,
            'status' => 'in_progress',
        ]);

        $this->actingAs($engineer)
            ->putJson(route('admin.phases.update', [$project->project_id, $phase->phase_id]), [
                'phase_name' => 'Finishing',
                'phase_order' => 1,
                'planned_start_date' => $phase->planned_start_date->toDateString(),
                'planned_end_date' => $phase->planned_end_date->toDateString(),
                'completion_percentage' => 100,
                'status' => 'in_progress',
            ])
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('construction_phases', [
            'phase_id' => $phase->phase_id,
            'completion_percentage' => 100,
            'status' => 'completed',
        ]);
    }

    public function test_cannot_change_completed_to_other_status(): void
    {
        $engineer = User::create([
            'name' => 'Engineer User Six',
            'email' => 'engineer-phase-locked@example.com',
            'password' => bcrypt('password'),
            'role' => 'engineer',
            'is_active' => true,
        ]);

        $project = Project::create([
            'project_name' => 'Test Project Six',
            'project_location' => 'Test Location',
            'client_id' => 6,
            'engineer_id' => $engineer->user_id,
            'start_date' => now()->toDateString(),
            'target_end_date' => now()->addMonth()->toDateString(),
            'status' => 'ongoing',
        ]);

        $phase = ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Closure',
            'phase_order' => 1,
            'planned_start_date' => now()->toDateString(),
            'planned_end_date' => now()->addWeek()->toDateString(),
            'completion_percentage' => 100,
            'status' => 'completed',
        ]);

        $this->actingAs($engineer)
            ->putJson(route('admin.phases.update', [$project->project_id, $phase->phase_id]), [
                'phase_name' => 'Closure',
                'phase_order' => 1,
                'planned_start_date' => $phase->planned_start_date->toDateString(),
                'planned_end_date' => $phase->planned_end_date->toDateString(),
                'completion_percentage' => 50,
                'status' => 'in_progress',
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}
