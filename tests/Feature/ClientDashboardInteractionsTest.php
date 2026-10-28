<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Client;
use App\Models\ConstructionPhase;
use App\Models\Project;
use App\Models\Report;
use App\Models\User;

class ClientDashboardInteractionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_dashboard_renders_project_detail_controls(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);

        $client = Client::factory()->create([
            'user_id' => $user->user_id,
            'company_name' => 'Lovelace Builders',
        ]);

        $project = Project::create([
            'project_name' => 'Riverfront Villa',
            'project_location' => 'Lagos',
            'client_id' => $client->client_id,
            'engineer_id' => $user->user_id,
            'start_date' => now()->subDays(10)->toDateString(),
            'target_end_date' => now()->addDays(30)->toDateString(),
            'status' => 'ongoing',
            'description' => 'A modern villa project',
        ]);

        ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Foundation Works',
            'phase_order' => 1,
            'planned_start_date' => now()->subDays(5)->toDateString(),
            'planned_end_date' => now()->addDays(20)->toDateString(),
            'completion_percentage' => 45,
            'status' => 'in_progress',
        ]);

        $this->actingAs($user);

        $response = $this->get('/client/dashboard');

        $response->assertStatus(200);
        $response->assertSee('heroStatusPill', false);
        $response->assertSee('Riverfront Villa');

        $snapshot = $this->get(route('client.dashboard.project.snapshot', $project->project_id));
        $snapshot->assertOk()
            ->assertJsonPath('hero.status_label', 'In Progress')
            ->assertJsonPath('stats.schedule_health_label', 'On Schedule');
    }

    public function test_client_my_projects_renders_project_detail_controls(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
        ]);

        $client = Client::factory()->create([
            'user_id' => $user->user_id,
            'company_name' => 'Hopper Construction',
        ]);

        $project = Project::create([
            'project_name' => 'Skyline Apartments',
            'project_location' => 'Abuja',
            'client_id' => $client->client_id,
            'engineer_id' => $user->user_id,
            'start_date' => now()->subDays(5)->toDateString(),
            'target_end_date' => now()->addDays(20)->toDateString(),
            'status' => 'ongoing',
            'description' => 'Luxury apartments',
        ]);

        ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Roofing',
            'phase_order' => 1,
            'planned_start_date' => now()->subDays(3)->toDateString(),
            'planned_end_date' => now()->addDays(15)->toDateString(),
            'completion_percentage' => 70,
            'status' => 'in_progress',
        ]);

        $this->actingAs($user);

        $response = $this->get('/client/myprojects');

        $response->assertStatus(200);
        $response->assertSee('My Projects');
        $response->assertSee('data-project-card', false);
        $response->assertSee('In Progress');
        $response->assertSee('On Schedule');
        $response->assertSee('70%');
        $response->assertDontSee('At Risk');
    }

    public function test_client_project_details_show_database_status_and_schedule_updates(): void
    {
        $user = User::factory()->create(['role' => 'client']);
        $client = Client::factory()->create(['user_id' => $user->user_id]);
        $project = Project::create([
            'project_name' => 'Harbor Renovation',
            'project_location' => 'Cebu',
            'client_id' => $client->client_id,
            'engineer_id' => $user->user_id,
            'start_date' => now()->subDays(20)->toDateString(),
            'target_end_date' => now()->subDays(2)->toDateString(),
            'status' => 'ongoing',
            'description' => 'Renovation works for the harbor office.',
        ]);

        ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Structural Repairs',
            'phase_order' => 1,
            'planned_start_date' => now()->subDays(15)->toDateString(),
            'planned_end_date' => now()->subDays(1)->toDateString(),
            'completion_percentage' => 35,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->get(route('client.myprojects'));

        $response->assertOk()
            ->assertSee('In Progress')
            ->assertSee('Might Be Delayed')
            ->assertSee('status-warning', false)
            ->assertSee('Past target date')
            ->assertSee('Harbor Renovation')
            ->assertDontSee('At Risk')
            ->assertDontSee('On Track');
    }

    public function test_admin_project_details_modal_renders_live_project_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $clientUser = User::factory()->create(['role' => 'client']);
        $client = Client::factory()->create(['user_id' => $clientUser->user_id]);
        $project = Project::create([
            'project_name' => 'Cedar Heights',
            'project_location' => 'Davao',
            'client_id' => $client->client_id,
            'engineer_id' => $admin->user_id,
            'start_date' => now()->subDays(12)->toDateString(),
            'target_end_date' => now()->addDays(18)->toDateString(),
            'status' => 'ongoing',
            'description' => 'Live modal summary from the project record.',
        ]);
        $phase = ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Foundation Works',
            'phase_order' => 1,
            'planned_start_date' => now()->subDays(8)->toDateString(),
            'planned_end_date' => now()->addDays(10)->toDateString(),
            'completion_percentage' => 45,
            'status' => 'in_progress',
        ]);
        Report::create([
            'project_id' => $project->project_id,
            'phase_id' => $phase->phase_id,
            'submitted_by' => $admin->user_id,
            'report_date' => now()->toDateString(),
            'report_text' => 'Foundation inspection completed.',
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('admin.projects.show', $project->project_id));

        $response->assertOk()
            ->assertSee('Cedar Heights')
            ->assertSee('Live modal summary from the project record.')
            ->assertSee('45%')
            ->assertSee('Approved')
            ->assertSee('In Progress')
            ->assertSee('Project Readiness')
            ->assertDontSee('On Track')
            ->assertSee('ps-status-pill.danger', false);
    }

    public function test_client_projects_are_paginated_ten_per_page(): void
    {
        $user = User::factory()->create(['role' => 'client']);
        $client = Client::factory()->create(['user_id' => $user->user_id]);

        for ($index = 1; $index <= 11; $index++) {
            Project::create([
                'project_name' => 'Client Project ' . $index,
                'project_location' => 'Lagos',
                'client_id' => $client->client_id,
                'engineer_id' => $user->user_id,
                'start_date' => now()->subDays(5)->toDateString(),
                'target_end_date' => now()->addDays(20)->toDateString(),
                'status' => 'ongoing',
                'description' => 'Client project ' . $index,
            ]);
        }

        $this->actingAs($user);

        $firstPage = $this->get('/client/myprojects');
        $firstPage->assertStatus(200);
        $firstPageProjects = $firstPage->viewData('projects');

        $this->assertSame(10, $firstPageProjects->perPage());
        $this->assertSame(2, $firstPageProjects->lastPage());
        $this->assertCount(10, $firstPageProjects);
        $firstPage->assertSee('page=2', false);

        $secondPage = $this->get('/client/myprojects?page=2');
        $secondPage->assertStatus(200);
        $secondPageProjects = $secondPage->viewData('projects');

        $this->assertSame(2, $secondPageProjects->currentPage());
        $this->assertSame(11, $secondPageProjects->firstItem());
        $this->assertCount(1, $secondPageProjects);
    }

    public function test_client_report_page_filters_to_selected_project(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
            'first_name' => 'Alan',
            'last_name' => 'Turing',
        ]);

        $client = Client::factory()->create([
            'user_id' => $user->user_id,
            'company_name' => 'Turing Build Group',
        ]);

        $projectA = Project::create([
            'project_name' => 'Alpha Tower',
            'project_location' => 'Lagos',
            'client_id' => $client->client_id,
            'engineer_id' => $user->user_id,
            'start_date' => now()->subDays(20)->toDateString(),
            'target_end_date' => now()->addDays(40)->toDateString(),
            'status' => 'ongoing',
            'description' => 'Alpha project',
        ]);

        $projectB = Project::create([
            'project_name' => 'Beta Villas',
            'project_location' => 'Abuja',
            'client_id' => $client->client_id,
            'engineer_id' => $user->user_id,
            'start_date' => now()->subDays(30)->toDateString(),
            'target_end_date' => now()->addDays(30)->toDateString(),
            'status' => 'ongoing',
            'description' => 'Beta project',
        ]);

        $phaseA = ConstructionPhase::create([
            'project_id' => $projectA->project_id,
            'phase_name' => 'Foundation',
            'phase_order' => 1,
            'planned_start_date' => now()->subDays(10)->toDateString(),
            'planned_end_date' => now()->addDays(20)->toDateString(),
            'status' => 'in_progress',
            'completion_percentage' => 40,
        ]);

        $phaseB = ConstructionPhase::create([
            'project_id' => $projectB->project_id,
            'phase_name' => 'Structural',
            'phase_order' => 1,
            'planned_start_date' => now()->subDays(15)->toDateString(),
            'planned_end_date' => now()->addDays(25)->toDateString(),
            'status' => 'in_progress',
            'completion_percentage' => 55,
        ]);

        Report::create([
            'project_id' => $projectA->project_id,
            'phase_id' => $phaseA->phase_id,
            'submitted_by' => $user->user_id,
            'report_date' => now()->toDateString(),
            'report_text' => 'Alpha project update',
            'approval_status' => 'approved',
            'is_published_to_client' => true,
        ]);

        Report::create([
            'project_id' => $projectB->project_id,
            'phase_id' => $phaseB->phase_id,
            'submitted_by' => $user->user_id,
            'report_date' => now()->toDateString(),
            'report_text' => 'Beta project update',
            'approval_status' => 'approved',
            'is_published_to_client' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get('/client/reports?project_id=' . $projectA->project_id);

        $response->assertStatus(200);
        $response->assertViewHas('selectedProject', fn ($selectedProject) => $selectedProject && $selectedProject->project_id === $projectA->project_id);
        $response->assertViewHas('reports', function ($reports) use ($projectA, $projectB) {
            $projectIds = $reports->pluck('project_id')->unique()->values()->all();

            return $projectIds === [$projectA->project_id] && ! in_array($projectB->project_id, $projectIds, true);
        });
    }
}
