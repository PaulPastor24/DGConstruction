<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;

class TimelineTest extends TestCase
{
    /**
     * Test status mapping logic
     */
    public function test_phases_have_correct_database_status_values()
    {
        // Get the first project from seeded data
        $project = Project::with('phases')->first();
        
        $this->assertNotNull($project, 'Should have at least one project');
        
        // Check each phase has valid database status
        foreach ($project->phases as $phase) {
            $this->assertTrue(
                in_array($phase->status, ['completed', 'in_progress', 'not_started', 'delayed']),
                "Phase {$phase->phase_name} has invalid status: {$phase->status}"
            );
        }
    }

    /**
     * Test that we have the correct phase counts
     */
    public function test_project_has_expected_phases()
    {
        $project = Project::with('phases')->first();
        
        // First project should have 5 phases
        $this->assertEquals(5, $project->phases->count(), 'Project 1 should have 5 phases');
        
        // Verify phase statuses match expected counts
        $completedCount = $project->phases->where('status', 'completed')->count();
        $inProgressCount = $project->phases->where('status', 'in_progress')->count();
        $notStartedCount = $project->phases->where('status', 'not_started')->count();
        
        $this->assertEquals(2, $completedCount, 'Should have 2 completed phases');
        $this->assertEquals(1, $inProgressCount, 'Should have 1 in-progress phase');
        $this->assertEquals(2, $notStartedCount, 'Should have 2 not-started phases');
    }

    /**
     * Test status mapping transformation
     */
    public function test_status_mapping_transformation()
    {
        // Simulate the status mapping logic from controller
        $statusMap = [
            'completed' => 'completed',
            'in_progress' => 'in-progress',
            'not_started' => 'planning',
            'delayed' => 'planning',
        ];
        
        // Get the actual phases and map them
        $project = Project::with('phases')->first();
        $mappedPhases = $project->phases->map(function ($phase) use ($statusMap) {
            return [
                'name' => $phase->phase_name,
                'database_status' => $phase->status,
                'display_status' => $statusMap[$phase->status] ?? 'planning'
            ];
        });
        
        // Verify all phases got mapped correctly
        foreach ($mappedPhases as $phase) {
            $this->assertTrue(
                in_array($phase['display_status'], ['completed', 'in-progress', 'planning']),
                "Phase {$phase['name']} mapped status should be valid"
            );
        }
        
        // Count the display statuses
        $completedCount = $mappedPhases->where('display_status', 'completed')->count();
        $inProgressCount = $mappedPhases->where('display_status', 'in-progress')->count();
        $planningCount = $mappedPhases->where('display_status', 'planning')->count();
        
        $this->assertEquals(2, $completedCount, 'Should have 2 phases displaying as completed');
        $this->assertEquals(1, $inProgressCount, 'Should have 1 phase displaying as in-progress');
        $this->assertEquals(2, $planningCount, 'Should have 2 phases displaying as planning');
    }
}

