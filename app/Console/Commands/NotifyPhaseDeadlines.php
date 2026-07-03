<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ConstructionPhase;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class NotifyPhaseDeadlines extends Command
{
    protected $signature = 'notify:phases-deadlines';
    protected $description = 'Notify supervisors of upcoming or delayed phase deadlines';

    public function handle()
    {
        $today = now()->startOfDay();

        // Phases ending within 3 days
        $upcoming = ConstructionPhase::whereNotIn('status', ['completed'])
            ->whereBetween('planned_end_date', [$today, $today->copy()->addDays(3)])
            ->with('project.supervisors')
            ->get();

        foreach ($upcoming as $phase) {
            $days = $today->diffInDays($phase->planned_end_date, false);
            $msg = "{$phase->phase_name} will finish in {$days} day(s).";
            foreach ($phase->project->supervisors as $sup) {
                try {
                    NotificationService::notifySupervisor($sup->user_id, [
                        'type' => 'phase',
                        'title' => 'Phase Deadline Near',
                        'message' => $msg,
                        'data' => ['module' => 'supervisor.phases', 'phase_id' => $phase->phase_id],
                        'related_id' => $phase->phase_id,
                        'related_type' => 'phase',
                    ]);
                } catch (\Throwable $e) {
                    Log::error('NotifyPhaseDeadlines failed: ' . $e->getMessage());
                }
            }
        }

        // Delayed phases
        $delayed = ConstructionPhase::whereNotIn('status', ['completed'])
            ->whereDate('planned_end_date', '<', $today)
            ->with('project.supervisors')
            ->get();

        foreach ($delayed as $phase) {
            foreach ($phase->project->supervisors as $sup) {
                try {
                    NotificationService::notifySupervisor($sup->user_id, [
                        'type' => 'phase',
                        'title' => 'Phase Delayed',
                        'message' => "{$phase->phase_name} is currently delayed.",
                        'data' => ['module' => 'supervisor.phases', 'phase_id' => $phase->phase_id],
                        'related_id' => $phase->phase_id,
                        'related_type' => 'phase',
                    ]);
                } catch (\Throwable $e) {
                    Log::error('NotifyPhaseDeadlines failed: ' . $e->getMessage());
                }
            }
        }

        $this->info('Phase deadline notifications processed.');
        return 0;
    }
}
