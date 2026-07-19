<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Client;
use App\Models\ConstructionPhase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimelineViewsTest extends TestCase
{
    use RefreshDatabase;

    protected function seedDatabase(): void
    {
        $engineerId = User::factory()->create(['role' => 'engineer'])->user_id;
        $supervisorId = User::factory()->create(['role' => 'supervisor', 'name' => 'Jane Supervisor', 'email' => 'jane@example.com'])->user_id;
        $clientUserId = User::factory()->create(['role' => 'client'])->user_id;
        $clientId = \App\Models\Client::create(['user_id' => $clientUserId])->client_id;

        $project = Project::create([
            'project_name' => 'Test Project',
            'project_location' => 'Test Location',
            'client_id' => $clientId,
            'engineer_id' => $engineerId,
            'start_date' => now()->subMonths(2),
            'target_end_date' => now()->addMonths(6),
            'status' => 'ongoing',
        ]);

        \Illuminate\Support\Facades\DB::table('project_supervisors')->insert([
            'project_id' => $project->project_id,
            'supervisor_id' => $supervisorId,
            'assigned_date' => now()->toDateString(),
            'is_active' => true,
            'created_at' => now(),
        ]);

        ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Phase 1',
            'phase_order' => 1,
            'planned_start_date' => now()->subMonths(1),
            'planned_end_date' => now()->addMonths(1),
            'completion_percentage' => 50,
            'status' => 'in_progress',
        ]);
    }

    public function test_supervisor_timeline_view_loads()
    {
        $this->seedDatabase();
        $supervisor = User::query()->where('role', 'supervisor')->first();
        
        if (!$supervisor) {
            $this->markTestSkipped('No supervisor user found in database');
        }

        $project = Project::with(['supervisors'])
            ->whereHas('supervisors', function ($q) use ($supervisor) {
                $q->where('project_supervisors.supervisor_id', $supervisor->user_id)
                    ->where('project_supervisors.is_active', true);
            })
            ->first();

        $this->actingAs($supervisor);
        
        $response = $this->get('/supervisor/timeline');

        $response->assertStatus(200);
        $response->assertViewIs('supervisor.timeline');
        
        $projectsData = $response->viewData('projectsWithStats');
        $this->assertNotNull($projectsData);
        
        if ($project) {
            $found = false;
            foreach ($projectsData as $item) {
                if ($item['id'] == $project->project_id) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Project should be in projects data');
        }
    }

    public function test_client_timeline_view_loads()
    {
        $this->seedDatabase();
        $clientUser = User::query()->where('role', 'client')->first();
        
        if (!$clientUser) {
            $this->markTestSkipped('No client user found in database');
        }

        $this->actingAs($clientUser);
        
        $response = $this->get('/client/timeline');

        $response->assertStatus(200);
        $response->assertViewIs('client.timeline');
        
        $projectsData = $response->viewData('projectsWithStats');
        $this->assertNotNull($projectsData);
    }

    public function test_supervisor_cannot_access_admin_timeline()
    {
        $this->seedDatabase();
        $supervisor = User::query()->where('role', 'supervisor')->first();
        
        if (!$supervisor) {
            $this->markTestSkipped('No supervisor user found');
        }

        $this->actingAs($supervisor);
        $response = $this->get('/admin/timeline');
        
        $this->assertTrue(
            $response->status() === 403 || $response->status() === 302,
            "Expected 403 or 302, got {$response->status()}"
        );
    }

    public function test_client_cannot_access_admin_timeline()
    {
        $this->seedDatabase();
        $client = User::query()->where('role', 'client')->first();
        
        if (!$client) {
            $this->markTestSkipped('No client user found');
        }

        $this->actingAs($client);
        $response = $this->get('/admin/timeline');
        
        $this->assertTrue(
            $response->status() === 403 || $response->status() === 302,
            "Expected 403 or 302, got {$response->status()}"
        );
    }

    public function test_timeline_data_structure_is_correct()
    {
        $this->seedDatabase();
        $supervisor = User::query()->where('role', 'supervisor')->first();
        
        if (!$supervisor) {
            $this->markTestSkipped('No supervisor user found');
        }

        $this->actingAs($supervisor);
        $response = $this->get('/supervisor/timeline');

        $projectsData = $response->viewData('projectsWithStats');
        
        if ($projectsData && is_array($projectsData) && count($projectsData) > 0) {
            $project = $projectsData[0];
            
            $this->assertArrayHasKey('id', $project);
            $this->assertArrayHasKey('name', $project);
            $this->assertArrayHasKey('phases', $project);
            $this->assertArrayHasKey('progress', $project);
            $this->assertArrayHasKey('completedPhases', $project);
            $this->assertArrayHasKey('inProgressPhases', $project);
            $this->assertArrayHasKey('upcomingPhases', $project);
            
            $phases = $project['phases'];
            if ($phases && is_array($phases) && count($phases) > 0) {
                foreach ($phases as $phase) {
                    $this->assertArrayHasKey('name', $phase);
                    $this->assertArrayHasKey('start', $phase);
                    $this->assertArrayHasKey('end', $phase);
                    $this->assertArrayHasKey('progress', $phase);
                    $this->assertArrayHasKey('milestones', $phase);
                    $this->assertTrue(
                        in_array($phase['display_status'], ['completed', 'in-progress', 'planning']),
                        "Phase {$phase['name']} has invalid display_status: {$phase['display_status']}"
                    );
                }
            }
        }
    }
}
