<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DevelopmentDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Identify supervisor and project dynamically (created by DatabaseSeeder)
        $supervisorId = DB::table('users')->where('role', 'supervisor')->value('user_id') ?? 2;
        $projectId = DB::table('projects')->orderBy('project_id')->value('project_id') ?? 1;

        // 1. Create 25 realistic workers and assign to project
        $names = [
            'Alvin Raza','Miguel Santos','John Miller','Omar Hassan','Luis Reyes',
            'Carlos Dela Cruz','Pedro Garcia','Mark Tan','Ramon Lopez','Eugene Cruz',
            'Ricky Lim','Enrique Bautista','Simon Ong','Daniel Navarro','Hector Ramos',
            'Jose Marquez','Fernando Salazar','Victor Cortez','Rafael Molina','Andres Pineda',
            'Julian Mercado','Sergio Alvarez','Nestor Velez','Bernard Aquino','Felix Ortega'
        ];

        $trades = ['Steel Fixing','Masonry','Electrical','Plumbing','Carpentry','Painting','Finishing','HVAC','Surveyor','General Labor'];

        $workerIds = [];

        foreach ($names as $i => $n) {
            $trade = $trades[array_rand($trades)];
            // split name into first/last
            $parts = explode(' ', $n, 2);
            $first = $parts[0] ?? $n;
            $last = $parts[1] ?? '';

            $id = DB::table('workers')->insertGetId([
                'first_name' => $first,
                'last_name' => $last,
                'trade' => $trade,
                'contact_number' => '+639170000' . str_pad((string)($i + 10), 3, '0', STR_PAD_LEFT),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $workerIds[] = $id;

            DB::table('project_workers')->insertGetId([
                'project_id' => $projectId,
                'worker_id' => $id,
                'deployed_date' => Carbon::now()->toDateString(),
                'is_active' => 1,
                'created_at' => now(),
            ]);
        }

        // 2. Enroll biometric profiles for a portion of workers (simulate enrolled vs not)
        $enrolled = array_slice($workerIds, 0, floor(count($workerIds) * 0.7)); // 70% enrolled
        foreach ($enrolled as $wid) {
            DB::table('worker_biometric_profiles')->insert([
                'worker_id' => $wid,
                // store a short binary fingerprint placeholder
                'fingerprint_template' => substr(md5($wid . now()), 0, 64),
                'enrolled_at' => now(),
                'enrolled_by' => $supervisorId,
            ]);
        }

        // 3. Generate attendance logs for the last 14 days
        $days = 14;
        for ($d = 0; $d < $days; $d++) {
            $date = Carbon::today()->subDays($d)->toDateString();

            foreach ($workerIds as $wid) {
                // Weighted random for status
                $rand = rand(1, 100);

                if ($rand <= 5) {
                    $status = 'on_leave';
                    $timeIn = null;
                    $timeOut = null;
                    $remarks = 'Official Leave';
                } elseif ($rand <= 12) {
                    $status = 'absent';
                    $timeIn = null;
                    $timeOut = null;
                    $remarks = 'No scan recorded';
                } elseif ($rand <= 20) {
                    $status = 'half_day';
                    // half day: morning then out at lunchtime
                    $timeIn = Carbon::createFromTime(rand(6,8), rand(0,59), 0)->format('H:i:s');
                    $timeOut = Carbon::createFromTime(12, rand(0,59), 0)->format('H:i:s');
                    $remarks = 'Half shift';
                } else {
                    $status = 'present';
                    // Most present; some will be late
                    $lateChance = rand(1, 100);
                    if ($lateChance <= 12) {
                        // late
                        $timeIn = Carbon::createFromTime(8, rand(1,50), 0)->format('H:i:s');
                        $remarks = 'Late due to traffic';
                    } else {
                        $timeIn = Carbon::createFromTime(6, rand(45,59), rand(0,59))->format('H:i:s');
                        $remarks = 'On schedule';
                    }
                    $timeOut = Carbon::createFromTime(17, rand(0,30), 0)->format('H:i:s');
                }

                // find a deployment_id for this worker in the project
                $deployment = DB::table('project_workers')
                    ->where('project_id', $projectId)
                    ->where('worker_id', $wid)
                    ->orderBy('deployment_id', 'desc')
                    ->first();

                $deploymentId = $deployment->deployment_id ?? null;

                // Insert log using deployment_id
                DB::table('attendance_logs')->insert([
                    'deployment_id' => $deploymentId,
                    'project_id' => $projectId,
                    'worker_id' => $wid,
                    'recorded_by' => $supervisorId,
                    'log_date' => $date,
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'status' => $status,
                    'remarks' => $remarks,
                    'biometric_matched' => (bool) rand(1, 100) > 10, // most scans match
                    'created_at' => now(),
                ]);
            }
        }

        // 4. Create timeline milestones for each phase of project 1
        $phases = DB::table('construction_phases')->where('project_id', $projectId)->get();
        foreach ($phases as $phase) {
            // create 2 milestones per phase
            for ($m = 1; $m <= 2; $m++) {
                $planned = Carbon::parse($phase->planned_start_date)->addDays($m * 7);
                $isCompleted = ($phase->completion_percentage >= ($m * 40));

                DB::table('timeline_milestones')->insert([
                    'phase_id' => $phase->phase_id,
                    'milestone_name' => $phase->phase_name . ' - Milestone ' . $m,
                    'start_date' => $planned->toDateString(),
                    'end_date' => $isCompleted ? $planned->toDateString() : null,
                    'is_completed' => $isCompleted ? 1 : 0,
                    'is_delayed' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 5. Generate a few accomplishment reports across phases
        foreach ($phases as $phase) {
            $reportDate = Carbon::parse($phase->actual_start_date ?? $phase->planned_start_date)->addDays(7);
            DB::table('accomplishment_reports')->insert([
                'project_id' => $projectId,
                'phase_id' => $phase->phase_id,
                'submitted_by' => $supervisorId,
                'report_date' => $reportDate->toDateString(),
                'report_text' => 'Site progress report for ' . $phase->phase_name . '. Activities proceeding as planned. Some adjustments to sequence due to material deliveries.',
                'site_images' => null,
                'ai_status' => 'processed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
