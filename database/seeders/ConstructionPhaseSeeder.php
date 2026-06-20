<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ConstructionPhase;
use Carbon\Carbon;

class ConstructionPhaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed phases for "Kulas and Rene" project (project_id = 1)
        $phases = [
            [
                'project_id' => 1,
                'phase_name' => 'Site Preparation & Earthworks',
                'phase_order' => 1,
                'planned_start_date' => Carbon::create(2026, 1, 15),
                'planned_end_date' => Carbon::create(2026, 2, 28),
                'actual_start_date' => Carbon::create(2026, 1, 15),
                'actual_end_date' => Carbon::create(2026, 2, 28),
                'completion_percentage' => 100,
                'status' => 'completed',
            ],
            [
                'project_id' => 1,
                'phase_name' => 'Foundation Works',
                'phase_order' => 2,
                'planned_start_date' => Carbon::create(2026, 3, 1),
                'planned_end_date' => Carbon::create(2026, 4, 10),
                'actual_start_date' => Carbon::create(2026, 3, 1),
                'actual_end_date' => Carbon::create(2026, 4, 7),
                'completion_percentage' => 100,
                'status' => 'completed',
            ],
            [
                'project_id' => 1,
                'phase_name' => 'Structural Works (Current)',
                'phase_order' => 3,
                'planned_start_date' => Carbon::create(2026, 4, 11),
                'planned_end_date' => Carbon::create(2026, 6, 30),
                'actual_start_date' => Carbon::create(2026, 4, 11),
                'actual_end_date' => null,
                'completion_percentage' => 67,
                'status' => 'in_progress',
            ],
            [
                'project_id' => 1,
                'phase_name' => 'MEP Installation',
                'phase_order' => 4,
                'planned_start_date' => Carbon::create(2026, 7, 1),
                'planned_end_date' => Carbon::create(2026, 7, 31),
                'actual_start_date' => null,
                'actual_end_date' => null,
                'completion_percentage' => 0,
                'status' => 'not_started',
            ],
            [
                'project_id' => 1,
                'phase_name' => 'Finishing & Turnover',
                'phase_order' => 5,
                'planned_start_date' => Carbon::create(2026, 8, 1),
                'planned_end_date' => Carbon::create(2026, 8, 31),
                'actual_start_date' => null,
                'actual_end_date' => null,
                'completion_percentage' => 0,
                'status' => 'not_started',
            ],
        ];

        // Insert phases for project 1
        foreach ($phases as $phase) {
            ConstructionPhase::updateOrCreate(
                ['project_id' => $phase['project_id'], 'phase_order' => $phase['phase_order']],
                $phase
            );
        }

        // Seed phases for "Ghost Project" (project_id = 2)
        $ghostPhases = [
            [
                'project_id' => 2,
                'phase_name' => 'Planning & Design',
                'phase_order' => 1,
                'planned_start_date' => Carbon::create(2026, 6, 20),
                'planned_end_date' => Carbon::create(2026, 7, 15),
                'actual_start_date' => Carbon::create(2026, 6, 20),
                'actual_end_date' => null,
                'completion_percentage' => 45,
                'status' => 'in_progress',
            ],
            [
                'project_id' => 2,
                'phase_name' => 'Site Mobilization',
                'phase_order' => 2,
                'planned_start_date' => Carbon::create(2026, 7, 16),
                'planned_end_date' => Carbon::create(2026, 8, 15),
                'actual_start_date' => null,
                'actual_end_date' => null,
                'completion_percentage' => 0,
                'status' => 'not_started',
            ],
            [
                'project_id' => 2,
                'phase_name' => 'Construction',
                'phase_order' => 3,
                'planned_start_date' => Carbon::create(2026, 8, 16),
                'planned_end_date' => Carbon::create(2026, 12, 31),
                'actual_start_date' => null,
                'actual_end_date' => null,
                'completion_percentage' => 0,
                'status' => 'not_started',
            ],
        ];

        // Insert phases for project 2
        foreach ($ghostPhases as $phase) {
            ConstructionPhase::updateOrCreate(
                ['project_id' => $phase['project_id'], 'phase_order' => $phase['phase_order']],
                $phase
            );
        }
    }
}
