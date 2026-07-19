<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ConstructionPhase;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupervisorPhaseUpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('supervisor');
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id('client_id');
            $table->bigInteger('user_id');
            $table->string('company_name')->nullable();
            $table->string('address')->nullable();
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
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('project_supervisors', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->bigInteger('project_id');
            $table->bigInteger('supervisor_id');
            $table->date('assigned_date');
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
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
        Schema::dropIfExists('project_supervisors');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_it_rejects_no_change_phase_progress_update(): void
    {
        /** @var User $user */
        $user = User::create([
            'name' => 'Supervisor User',
            'email' => 'supervisor-progress@example.com',
            'password' => bcrypt('password'),
            'role' => 'supervisor',
            'is_active' => true,
        ]);

        $client = Client::create([
            'user_id' => $user->user_id,
            'company_name' => 'Test Company',
            'address' => 'Test Address',
        ]);

        $project = Project::create([
            'project_name' => 'Test Project',
            'project_location' => 'Test Location',
            'client_id' => $client->client_id,
            'engineer_id' => $user->user_id,
            'start_date' => now()->toDateString(),
            'target_end_date' => now()->addMonth()->toDateString(),
            'status' => 'ongoing',
        ]);
        $project->supervisors()->attach($user->user_id, ['assigned_date' => now()->toDateString(), 'is_active' => true]);

        $phase = ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Foundation',
            'phase_order' => 1,
            'planned_start_date' => now()->toDateString(),
            'planned_end_date' => now()->addWeek()->toDateString(),
            'completion_percentage' => 25,
            'status' => 'in_progress',
        ]);

        $this->actingAs($user)
            ->postJson(route('supervisor.api.phases.updateProgress', ['id' => $phase->phase_id]), [
                'completion_percentage' => 25,
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('unchanged', true);
    }

    public function test_it_rejects_no_change_phase_status_update(): void
    {
        /** @var User $user */
        $user = User::create([
            'name' => 'Supervisor User Two',
            'email' => 'supervisor-status@example.com',
            'password' => bcrypt('password'),
            'role' => 'supervisor',
            'is_active' => true,
        ]);

        $client = Client::create([
            'user_id' => $user->user_id,
            'company_name' => 'Test Company',
            'address' => 'Test Address',
        ]);

        $project = Project::create([
            'project_name' => 'Test Project',
            'project_location' => 'Test Location',
            'client_id' => $client->client_id,
            'engineer_id' => $user->user_id,
            'start_date' => now()->toDateString(),
            'target_end_date' => now()->addMonth()->toDateString(),
            'status' => 'ongoing',
        ]);
        $project->supervisors()->attach($user->user_id, ['assigned_date' => now()->toDateString(), 'is_active' => true]);

        $phase = ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Foundation',
            'phase_order' => 1,
            'planned_start_date' => now()->toDateString(),
            'planned_end_date' => now()->addWeek()->toDateString(),
            'completion_percentage' => 25,
            'status' => 'in_progress',
        ]);

        $this->actingAs($user)
            ->postJson(route('supervisor.api.phases.updateStatus', ['id' => $phase->phase_id]), [
                'status' => 'in_progress',
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('unchanged', true);
    }
}
