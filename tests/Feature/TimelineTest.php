<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\ConstructionPhase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimelineTest extends TestCase
{
    use RefreshDatabase;

    protected function seedDatabase(): void
    {
        $engineerId = User::factory()->create(['role' => 'engineer'])->user_id;
        $supervisorId = User::factory()->create(['role' => 'supervisor'])->user_id;
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

        $statuses = ['completed', 'completed', 'in_progress', 'not_started', 'not_started'];
        for ($i = 0; $i < 5; $i++) {
            ConstructionPhase::create([
                'project_id' => $project->project_id,
                'phase_name' => "Phase " . ($i + 1),
                'phase_order' => $i + 1,
                'planned_start_date' => now()->addDays($i * 30),
                'planned_end_date' => now()->addDays($i * 30 + 29),
                'completion_percentage' => match($i) { 0 => 100, 1 => 100, 2 => 0, 3 => 0, 4 => 0, default => 0 },
                'status' => $statuses[$i] ?? 'not_started',
            ]);
        }
    }

    public function test_phases_have_correct_database_status_values(): void
    {
        $this->seedDatabase();
        $project = Project::with('phases')->first();
        
        $this->assertNotNull($project, 'Should have at least one project');
        
        foreach ($project->phases as $phase) {
            $this->assertTrue(
                in_array($phase->status, ['completed', 'in_progress', 'not_started', 'delayed']),
                "Phase {$phase->phase_name} has invalid status: {$phase->status}"
            );
        }
    }

    public function test_project_has_expected_phases(): void
    {
        $this->seedDatabase();
        $project = Project::with('phases')->first();
        
        $this->assertEquals(5, $project->phases->count(), 'Project 1 should have 5 phases');
        
        $completedCount = $project->phases->where('status', 'completed')->count();
        $inProgressCount = $project->phases->where('status', 'in_progress')->count();
        $notStartedCount = $project->phases->where('status', 'not_started')->count();
        
        $this->assertEquals(2, $completedCount, 'Should have 2 completed phases');
        $this->assertEquals(1, $inProgressCount, 'Should have 1 in-progress phase');
        $this->assertEquals(2, $notStartedCount, 'Should have 2 not-started phases');
    }

    public function test_status_mapping_transformation(): void
    {
        $this->seedDatabase();
        $statusMap = [
            'completed' => 'completed',
            'in_progress' => 'in-progress',
            'not_started' => 'planning',
            'delayed' => 'planning',
        ];
        
        $project = Project::with('phases')->first();
        $mappedPhases = $project->phases->map(function ($phase) use ($statusMap) {
            return [
                'name' => $phase->phase_name,
                'database_status' => $phase->status,
                'display_status' => $statusMap[$phase->status] ?? 'planning'
            ];
        });
        
        foreach ($mappedPhases as $phase) {
            $this->assertTrue(
                in_array($phase['display_status'], ['completed', 'in-progress', 'planning']),
                "Phase {$phase['name']} mapped status should be valid"
            );
        }
        
        $completedCount = $mappedPhases->where('display_status', 'completed')->count();
        $inProgressCount = $mappedPhases->where('display_status', 'in-progress')->count();
        $planningCount = $mappedPhases->where('display_status', 'planning')->count();
        
        $this->assertEquals(2, $completedCount, 'Should have 2 phases displaying as completed');
        $this->assertEquals(1, $inProgressCount, 'Should have 1 phase displaying as in-progress');
        $this->assertEquals(2, $planningCount, 'Should have 2 phases displaying as planning');
    }
}

