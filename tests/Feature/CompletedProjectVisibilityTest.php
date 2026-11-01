<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ConstructionPhase;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CompletedProjectVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function project(User $engineer, User $supervisor, string $name): Project
    {
        $client = Client::create(['user_id' => User::factory()->create(['role' => 'client'])->user_id]);
        $project = Project::create([
            'project_name' => $name, 'project_location' => 'Test site',
            'client_id' => $client->client_id, 'engineer_id' => $engineer->user_id,
            'start_date' => now()->subMonth(), 'target_end_date' => now()->addMonth(), 'status' => 'ongoing',
        ]);
        $project->supervisors()->attach($supervisor->user_id, ['assigned_date' => now()->toDateString(), 'is_active' => true]);
        ConstructionPhase::create([
            'project_id' => $project->project_id, 'phase_name' => $name.' phase', 'phase_order' => 1,
            'planned_start_date' => now()->subWeek(), 'planned_end_date' => now()->addWeek(),
            'completion_percentage' => 50, 'status' => 'in_progress',
        ]);

        return $project;
    }

    public function test_completed_projects_are_hidden_from_management_screens_but_retained_in_project_management(): void
    {
        $engineer = User::factory()->create(['role' => 'engineer']);
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $closed = $this->project($engineer, $supervisor, 'Finished construction');
        $open = $this->project($engineer, $supervisor, 'Active construction');

        foreach (['completed', 'complete', 'finished'] as $status) {
            DB::table('projects')->where('project_id', $closed->project_id)->update(['status' => $status]);
            foreach (['engineer', 'admin', 'administrator'] as $role) {
                $engineer->update(['role' => $role]);
                $this->actingAs($engineer);
                $this->get(route('admin.timeline'))->assertOk()->assertSee($open->project_name)->assertDontSee($closed->project_name);
                $this->withSession(['admin_selected_project_id' => $closed->project_id])
                    ->get(route('admin.phases', ['project_id' => $closed->project_id]))->assertOk()
                    ->assertViewHas('selectedProject', fn ($project) => $project->project_id === $open->project_id)
                    ->assertDontSee($closed->project_name);
                $this->getJson(route('admin.timeline.data', $closed))->assertNotFound();
                $this->get(route('admin.projects.index', ['status' => 'completed']))->assertOk()->assertSee($closed->project_name);
            }
            $this->actingAs($supervisor);
            $this->withSession(['supervisor_selected_project_id' => $closed->project_id])
                ->get(route('supervisor.timeline', ['project_id' => $closed->project_id]))->assertOk()
                ->assertSee($open->project_name)->assertDontSee($closed->project_name);
            $this->get(route('supervisor.phases', ['project_id' => $closed->project_id]))->assertOk()
                ->assertViewHas('primaryProject', fn ($project) => $project->project_id === $open->project_id)
                ->assertDontSee($closed->project_name);
            $this->getJson(route('supervisor.api.phases.details', $closed->phases->first()->phase_id))->assertNotFound();
        }
    }

    public function test_completed_projects_cannot_be_reopened_through_phase_or_milestone_endpoints(): void
    {
        $engineer = User::factory()->create(['role' => 'engineer']);
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $project = $this->project($engineer, $supervisor, 'Closed construction');
        $phase = $project->phases->first();
        $project->update(['status' => 'completed']);
        $this->actingAs($engineer);
        $this->get(route('admin.milestones.index', ['project' => $project, 'phase' => $phase]))->assertNotFound();
        $this->putJson(route('admin.phases.update', ['project' => $project, 'phase' => $phase]), [])->assertNotFound();
        $this->deleteJson(route('admin.phases.destroy', ['project' => $project, 'phase' => $phase]))->assertNotFound();
        $this->postJson(route('admin.phases.store'), ['project_id' => $project->project_id])->assertNotFound();
        $this->getJson(route('admin.phases.export.csv', ['project_id' => $project->project_id]))->assertNotFound();
        $this->getJson(route('admin.phases.export.pdf', ['project_id' => $project->project_id]))->assertNotFound();
        $this->actingAs($supervisor)->postJson(route('supervisor.api.phases.exportPdf'), ['project_id' => $project->project_id])->assertForbidden();
        $this->assertDatabaseHas('construction_phases', ['phase_id' => $phase->phase_id]);
    }

    public function test_completing_the_final_milestone_removes_project_from_progress_monitoring(): void
    {
        // The existing date-renaming migration runs on MySQL but skips SQLite.
        Schema::table('timeline_milestones', function ($table) {
            $table->renameColumn('planned_date', 'start_date');
            $table->renameColumn('actual_date', 'end_date');
        });
        $engineer = User::factory()->create(['role' => 'engineer']);
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $project = $this->project($engineer, $supervisor, 'Final milestone construction');
        $phase = $project->phases->first();
        $milestone = Milestone::create([
            'phase_id' => $phase->phase_id, 'milestone_name' => 'Final inspection',
            'start_date' => now()->subDay(), 'end_date' => now()->addDay(),
            'is_completed' => false,
        ]);
        $this->actingAs($engineer)->postJson(route('admin.milestones.complete', [
            'project' => $project, 'phase' => $phase, 'milestone' => $milestone,
        ]))->assertOk();
        $this->assertSame('completed', $project->fresh()->status);
        $this->get(route('admin.timeline'))->assertOk()->assertDontSee($project->project_name);
        $this->get(route('admin.phases'))->assertOk()->assertViewHas('selectedProject', null);
        $this->actingAs($supervisor)->get(route('supervisor.timeline'))->assertOk()->assertDontSee($project->project_name);
        $this->get(route('supervisor.phases'))->assertOk()->assertViewHas('primaryProject', null);
        $this->assertDatabaseHas('projects', ['project_id' => $project->project_id, 'status' => 'completed']);
    }
}
