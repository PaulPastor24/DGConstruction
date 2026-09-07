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
        $response->assertSee('Project Progress Overview');
        $response->assertSee('project-detail-trigger', false);
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
        $response->assertSee('project-detail-trigger', false);
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
