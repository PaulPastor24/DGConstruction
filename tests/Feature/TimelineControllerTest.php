<?php

namespace Tests\Feature;

use App\Http\Controllers\TimelineController;
use App\Models\ConstructionPhase;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class TimelineControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('timeline_milestones');
        Schema::dropIfExists('construction_phases');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('project_supervisors');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id('project_id');
            $table->string('project_name');
            $table->string('project_location')->nullable();
            $table->bigInteger('client_id')->nullable();
            $table->bigInteger('engineer_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('target_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->string('status')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('construction_phases', function (Blueprint $table) {
            $table->id('phase_id');
            $table->bigInteger('project_id');
            $table->string('phase_name');
            $table->integer('phase_order')->default(1);
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('timeline_milestones', function (Blueprint $table) {
            $table->id('milestone_id');
            $table->bigInteger('phase_id');
            $table->string('milestone_name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_delayed')->default(false);
            $table->timestamps();
        });

        Schema::create('project_supervisors', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('project_id');
            $table->bigInteger('supervisor_id');
            $table->date('assigned_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function test_enrich_project_data_includes_project_milestones_for_timeline_ui(): void
    {
        $project = Project::create([
            'project_name' => 'Alpha Tower',
            'project_location' => 'Nairobi',
            'status' => 'in_progress',
        ]);

        $phase = ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Foundation',
            'phase_order' => 1,
            'planned_start_date' => '2026-07-01',
            'planned_end_date' => '2026-07-15',
            'completion_percentage' => 25,
            'status' => 'in_progress',
        ]);

        Milestone::create([
            'phase_id' => $phase->phase_id,
            'milestone_name' => 'Excavation Complete',
            'start_date' => '2026-07-10',
            'end_date' => null,
            'is_completed' => false,
            'is_delayed' => false,
        ]);

        $controller = new TimelineController();
        $method = new \ReflectionMethod($controller, 'enrichProjectData');
        $method->setAccessible(true);

        $data = $method->invoke($controller, $project);

        $this->assertArrayHasKey('milestones', $data);
        $this->assertCount(1, $data['milestones']);
        $this->assertSame('Excavation Complete', $data['milestones'][0]['milestone_name']);
        $this->assertSame('upcoming', $data['milestones'][0]['status']);
    }
}
