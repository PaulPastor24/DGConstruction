<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Client;

class TimelineViewsTest extends TestCase
{
    /**
     * Test supervisor timeline view loads correctly
     */
    public function test_supervisor_timeline_view_loads()
    {
        // Get or create supervisor user
        $supervisor = User::query()->where('role', 'site_supervisor')->first();
        
        if (!$supervisor) {
            $this->markTestSkipped('No supervisor user found in database');
        }

        // Get a project that has this supervisor assigned
        $project = Project::with(['supervisors'])
            ->whereHas('supervisors', function ($q) use ($supervisor) {
                $q->where('project_supervisors.supervisor_id', $supervisor->user_id)
                    ->where('project_supervisors.is_active', true);
            })
            ->first();

        // Login as supervisor
        $this->actingAs($supervisor);
        
        // Request the timeline page
        $response = $this->get('/supervisor/timeline');

        // Check for successful response
        $response->assertStatus(200);
        $response->assertViewIs('supervisor.timeline');
        
        // Verify projects data is passed to view
        $projectsData = $response->viewData('projectsWithStats');
        $this->assertNotNull($projectsData);
        
        // If supervisor has projects, verify they're in the data
        if ($project) {
            $this->assertTrue(
                $projectsData->contains(function ($item) use ($project) {
                    return $item['id'] == $project->project_id;
                })
            );
        }
    }

    /**
     * Test client timeline view loads correctly
     */
    public function test_client_timeline_view_loads()
    {
        // Get or create client user
        $clientUser = User::query()->where('role', 'client')->first();
        
        if (!$clientUser) {
            $this->markTestSkipped('No client user found in database');
        }

        // Login as client
        $this->actingAs($clientUser);
        
        // Request the timeline page
        $response = $this->get('/client/timeline');

        // Check for successful response
        $response->assertStatus(200);
        $response->assertViewIs('client.timeline');
        
        // Verify projects data is passed to view
        $projectsData = $response->viewData('projectsWithStats');
        $this->assertNotNull($projectsData);
    }

    /**
     * Test that supervisor cannot access admin timeline
     */
    public function test_supervisor_cannot_access_admin_timeline()
    {
        $supervisor = User::query()->where('role', 'site_supervisor')->first();
        
        if (!$supervisor) {
            $this->markTestSkipped('No supervisor user found');
        }

        $this->actingAs($supervisor);
        $response = $this->get('/admin/timeline');
        
        // Should be unauthorized (403) or redirected
        $this->assertTrue(
            $response->status() === 403 || $response->status() === 302,
            "Expected 403 or 302, got {$response->status()}"
        );
    }

    /**
     * Test that client cannot access admin timeline
     */
    public function test_client_cannot_access_admin_timeline()
    {
        $client = User::query()->where('role', 'client')->first();
        
        if (!$client) {
            $this->markTestSkipped('No client user found');
        }

        $this->actingAs($client);
        $response = $this->get('/admin/timeline');
        
        // Should be unauthorized (403) or redirected
        $this->assertTrue(
            $response->status() === 403 || $response->status() === 302,
            "Expected 403 or 302, got {$response->status()}"
        );
    }

    /**
     * Test timeline data structure contains required fields
     */
    public function test_timeline_data_structure_is_correct()
    {
        $supervisor = User::query()->where('role', 'site_supervisor')->first();
        
        if (!$supervisor) {
            $this->markTestSkipped('No supervisor user found');
        }

        $this->actingAs($supervisor);
        $response = $this->get('/supervisor/timeline');

        $projectsData = $response->viewData('projectsWithStats');
        
        // If there are projects, verify data structure
        if ($projectsData && $projectsData->count() > 0) {
            $project = $projectsData->first();
            
            // Verify required fields
            $this->assertArrayHasKey('id', $project);
            $this->assertArrayHasKey('name', $project);
            $this->assertArrayHasKey('phases', $project);
            $this->assertArrayHasKey('progress', $project);
            $this->assertArrayHasKey('completedPhases', $project);
            $this->assertArrayHasKey('inProgressPhases', $project);
            $this->assertArrayHasKey('upcomingPhases', $project);
            
            // Verify phases expose the real database-backed fields used by the timeline UI
            $phases = $project['phases'];
            if ($phases && $phases->count() > 0) {
                foreach ($phases as $phase) {
                    $this->assertArrayHasKey('name', $phase);
                    $this->assertArrayHasKey('start', $phase);
                    $this->assertArrayHasKey('end', $phase);
                    $this->assertArrayHasKey('progress', $phase);
                    $this->assertArrayHasKey('milestones', $phase);
                    $this->assertTrue(
                        in_array($phase->display_status, ['completed', 'in-progress', 'planning']),
                        "Phase {$phase->phase_name} has invalid display_status: {$phase->display_status}"
                    );
                }
            }
        }
    }
}
