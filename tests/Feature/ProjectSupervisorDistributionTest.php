<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectSupervisorAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProjectSupervisorDistributionTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $admin = User::factory()->create(['role' => 'engineer']);
        $client = Client::create(['user_id' => User::factory()->create(['role' => 'client'])->user_id]);
        $supervisors = collect(range(1, 4))->map(fn ($i) => User::factory()->create(['role' => 'supervisor']));
        $projects = collect(range(1, 10))->map(function ($i) use ($admin, $client, $supervisors) {
            $project = Project::create([
                'project_name' => 'Distribution project '.$i, 'project_location' => 'Test site',
                'engineer_id' => $admin->user_id, 'client_id' => $client->client_id,
                'start_date' => now()->toDateString(), 'target_end_date' => now()->addMonth()->toDateString(),
                'status' => $i === 10 ? 'completed' : 'ongoing',
            ]);
            $project->supervisors()->attach($supervisors[0]->user_id, ['assigned_date' => '2026-10-01', 'is_active' => true]);
            return $project;
        });

        return [$admin, $client, $supervisors, $projects];
    }

    public function test_preview_leaves_assignments_unchanged_and_apply_balances_all_non_archived_projects(): void
    {
        [, , $supervisors, $projects] = $this->fixture();
        $archived = $projects[0]->replicate();
        $archived->project_name = 'Archived assignment'; $archived->status = 'archived'; $archived->save();
        $archived->supervisors()->attach($supervisors[0]->user_id, ['assigned_date' => '2026-10-01', 'is_active' => true]);
        $before = DB::table('project_supervisors')->orderBy('assignment_id')->get()->toJson();
        $this->artisan('projects:distribute-supervisors')->assertSuccessful();
        $this->assertSame($before, DB::table('project_supervisors')->orderBy('assignment_id')->get()->toJson());
        $this->artisan('projects:distribute-supervisors', ['--apply' => true])->assertSuccessful();
        $rows = app(ProjectSupervisorAssignment::class)->distribute();
        $this->assertSame([3, 3, 2, 2], array_column($rows, 'projects'));
        foreach ($projects as $project) {
            $this->assertSame(1, $project->activeSupervisors()->count());
        }
        // Former assignments remain present, but no longer grant access.
        $this->assertDatabaseHas('project_supervisors', ['project_id' => $projects[1]->project_id,
            'supervisor_id' => $supervisors[0]->user_id, 'is_active' => false]);
        $this->assertDatabaseHas('project_supervisors', ['project_id' => $archived->project_id,
            'supervisor_id' => $supervisors[0]->user_id, 'is_active' => true]);
        $this->assertDatabaseHas('projects', ['project_id' => $projects[9]->project_id, 'status' => 'completed']);
        $count = DB::table('project_supervisors')->count();
        app(ProjectSupervisorAssignment::class)->distribute(true);
        $this->assertSame($count, DB::table('project_supervisors')->count());
    }

    public function test_distribution_refuses_to_change_data_when_four_active_supervisors_are_not_present(): void
    {
        [, , $supervisors] = $this->fixture();
        $supervisors[3]->update(['is_active' => false]);
        $before = DB::table('project_supervisors')->get()->toJson();
        $this->artisan('projects:distribute-supervisors', ['--apply' => true])->assertFailed();
        $this->assertSame($before, DB::table('project_supervisors')->get()->toJson());
    }

    public function test_new_projects_are_automatically_assigned_to_the_least_loaded_supervisor(): void
    {
        [$admin, $client] = $this->fixture();
        app(ProjectSupervisorAssignment::class)->distribute(true);
        $this->actingAs($admin);
        for ($i = 1; $i <= 6; $i++) {
            $this->postJson(route('admin.projects.store'), [
                'project_name' => 'New balanced project '.$i, 'project_location' => 'New site',
                'client_id' => $client->client_id, 'start_date' => now()->toDateString(),
                'target_end_date' => now()->addMonth()->toDateString(),
            ])->assertRedirect(route('admin.projects.index'))->assertSessionHas('success');
            $project = Project::where('project_name', 'New balanced project '.$i)->firstOrFail();
            $this->assertSame(1, $project->activeSupervisors()->count());
        }
        $loads = DB::table('project_supervisors')->where('is_active', true)
            ->selectRaw('supervisor_id, COUNT(*) as total')->groupBy('supervisor_id')->pluck('total')->all();
        $this->assertSame([4, 4, 4, 4], $loads);
    }

    public function test_manual_supervisor_selection_is_preserved_and_non_supervisors_are_rejected(): void
    {
        [$admin, $client, $supervisors] = $this->fixture();
        $payload = ['project_name' => 'Explicit assignment', 'project_location' => 'Test site',
            'client_id' => $client->client_id, 'start_date' => now()->toDateString(),
            'target_end_date' => now()->addMonth()->toDateString(), 'supervisor_id' => $supervisors[3]->user_id];
        $this->actingAs($admin)->postJson(route('admin.projects.store'), $payload)->assertRedirect(route('admin.projects.index'))->assertSessionHas('success');
        $project = Project::where('project_name', 'Explicit assignment')->firstOrFail();
        $this->assertSame([$supervisors[3]->user_id], $project->activeSupervisors()->pluck('users.user_id')->all());
        $payload['project_name'] = 'Invalid supervisor'; $payload['supervisor_id'] = $admin->user_id;
        $this->postJson(route('admin.projects.store'), $payload)->assertUnprocessable();
    }

    public function test_previous_supervisor_no_longer_sees_reassigned_projects_in_monitoring_screens(): void
    {
        [, , $supervisors, $projects] = $this->fixture();
        app(ProjectSupervisorAssignment::class)->distribute(true);
        $this->actingAs($supervisors[0]);
        $this->get(route('supervisor.timeline'))->assertOk()
            ->assertSee($projects[0]->project_name)->assertDontSee($projects[1]->project_name);
        $this->get(route('supervisor.phases'))->assertOk()
            ->assertViewHas('assignedProjects', fn ($assigned) => $assigned->pluck('project_id')->sort()->values()->all()
                === [$projects[0]->project_id, $projects[4]->project_id, $projects[8]->project_id]);
    }
}
