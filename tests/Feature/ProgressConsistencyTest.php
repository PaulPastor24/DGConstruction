<?php

namespace Tests\Feature;

use App\Models\ConstructionPhase;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProgressConsistencyTest extends TestCase
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

        Schema::create('timeline_milestones', function (Blueprint $table) {
            $table->increments('milestone_id');
            $table->unsignedInteger('phase_id');
            $table->string('milestone_name', 200);
            $table->date('planned_date');
            $table->date('actual_date')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_delayed')->default(false);
            $table->timestamps();
            $table->foreign('phase_id')->references('phase_id')->on('construction_phases')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('timeline_milestones');
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('construction_phases');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_phase_progress_uses_accessor_not_raw_database_value(): void
    {
        $engineer = User::create([
            'name' => 'Engineer User',
            'email' => 'engineer-progress@example.com',
            'password' => bcrypt('password'),
            'role' => 'engineer',
            'is_active' => true,
        ]);

        $project = Project::create([
            'project_name' => 'Test Progress Project',
            'project_location' => 'Test Location',
            'client_id' => 1,
            'engineer_id' => $engineer->user_id,
            'start_date' => now()->toDateString(),
            'target_end_date' => now()->addMonth()->toDateString(),
            'status' => 'ongoing',
        ]);

        $phase = ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Testing Phase',
            'phase_order' => 1,
            'planned_start_date' => now()->subWeek()->toDateString(),
            'planned_end_date' => now()->addWeek()->toDateString(),
            'completion_percentage' => 45.00,
            'status' => 'not_started',
        ]);

        $this->actingAs($engineer)
            ->get(route('admin.phases', ['project_id' => $project->project_id]))
            ->assertStatus(200)
            ->assertSee('0%');
    }
}
