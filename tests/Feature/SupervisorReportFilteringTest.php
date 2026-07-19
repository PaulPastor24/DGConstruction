<?php

namespace Tests\Feature;

use App\Models\ConstructionPhase;
use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupervisorReportFilteringTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function ($table) {
            $table->id('user_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('supervisor');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('projects', function ($table) {
            $table->id('project_id');
            $table->string('project_name');
            $table->string('status')->default('ongoing');
            $table->timestamps();
        });

        Schema::create('construction_phases', function ($table) {
            $table->id('phase_id');
            $table->bigInteger('project_id');
            $table->string('phase_name');
            $table->integer('phase_order')->default(1);
            $table->string('status')->default('in_progress');
            $table->timestamps();
        });

        Schema::create('project_supervisors', function ($table) {
            $table->id();
            $table->bigInteger('project_id');
            $table->bigInteger('supervisor_id');
            $table->timestamps();
        });

        Schema::create('accomplishment_reports', function ($table) {
            $table->id('report_id');
            $table->bigInteger('project_id');
            $table->bigInteger('phase_id');
            $table->bigInteger('submitted_by');
            $table->date('report_date');
            $table->text('report_text')->nullable();
            $table->text('site_images')->nullable();
            $table->string('approval_status')->default('pending');
            $table->timestamps();
        });
    }

    public function test_supervisor_reports_show_all_assigned_projects_when_no_project_filter_is_selected(): void
    {
        $user = User::create([
            'name' => 'Supervisor Three',
            'email' => 'supervisor3@example.com',
            'password' => bcrypt('password'),
            'role' => 'supervisor',
            'is_active' => true,
        ]);

        $firstProject = Project::create(['project_name' => 'Alpha Project', 'status' => 'ongoing']);
        $secondProject = Project::create(['project_name' => 'Beta Project', 'status' => 'ongoing']);

        DB::table('project_supervisors')->insert([
            ['project_id' => $firstProject->project_id, 'supervisor_id' => $user->user_id, 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $secondProject->project_id, 'supervisor_id' => $user->user_id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $firstPhase = ConstructionPhase::create([
            'project_id' => $firstProject->project_id,
            'phase_name' => 'Foundation',
            'phase_order' => 1,
            'status' => 'in_progress',
        ]);

        $secondPhase = ConstructionPhase::create([
            'project_id' => $secondProject->project_id,
            'phase_name' => 'Structure',
            'phase_order' => 1,
            'status' => 'in_progress',
        ]);

        Report::create([
            'project_id' => $firstProject->project_id,
            'phase_id' => $firstPhase->phase_id,
            'submitted_by' => $user->user_id,
            'report_date' => '2026-07-01',
            'report_text' => 'First report',
            'site_images' => null,
            'approval_status' => 'pending',
        ]);

        Report::create([
            'project_id' => $secondProject->project_id,
            'phase_id' => $secondPhase->phase_id,
            'submitted_by' => $user->user_id,
            'report_date' => '2026-07-02',
            'report_text' => 'Second report',
            'site_images' => null,
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('supervisor.reports'));

        $response->assertOk();
        $response->assertSee('First report');
        $response->assertSee('Second report');
    }
}
