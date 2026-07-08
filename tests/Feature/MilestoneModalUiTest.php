<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\ConstructionPhase;

class MilestoneModalUiTest extends TestCase
{
    public function test_admin_timeline_modal_renders_enhanced_phase_guidance()
    {
        if (config('database.default') === 'sqlite' && env('DB_DATABASE') === ':memory:') {
            $this->markTestSkipped('Database-backed timeline tests require a non-memory database for this application.');
        }

        $engineer = User::create([
            'name' => 'Engineer Tester',
            'email' => 'engineer-' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'engineer',
            'is_active' => true,
        ]);

        $project = Project::create([
            'project_name' => 'Test Project',
            'project_location' => 'Test Location',
            'client_id' => 1,
            'engineer_id' => $engineer->user_id,
            'status' => 'ongoing',
        ]);

        ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Planning & Design',
            'phase_order' => 1,
            'status' => 'in_progress',
        ]);

        $this->actingAs($engineer);

        $response = $this->get('/admin/timeline');

        $response->assertStatus(200);
        $response->assertSee('Construction Phase');
        $response->assertSee('Select the construction phase that owns this milestone.');
        $response->assertSee('Required');
    }
}
