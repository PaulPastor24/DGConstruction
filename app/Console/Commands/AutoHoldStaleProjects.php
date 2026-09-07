<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AutoHoldStaleProjects extends Command
{
    protected $signature = 'projects:auto-hold-stale';
    protected $description = 'Place ongoing projects on hold after 14 days without updates';

    public function handle(): int
    {
        $cutoff = Carbon::now()->subDays(14);
        $count = 0;

        // Auto-flag overdue milestones
        $overdueCount = 0;
        \App\Models\Milestone::query()
            ->where('is_completed', false)
            ->where('is_delayed', false)
            ->where('end_date', '<', Carbon::now()->toDateString())
            ->chunkById(100, function ($milestones) use (&$overdueCount): void {
                foreach ($milestones as $milestone) {
                    $milestone->update(['is_delayed' => true]);
                    $overdueCount++;
                }
            }, 'milestone_id');

        if ($overdueCount > 0) {
            $this->info("Auto-flagged {$overdueCount} overdue milestone(s) as delayed.");
        }

        // Auto-start phases whose actual start date has arrived
        $autoStarted = 0;
        \App\Models\ConstructionPhase::query()
            ->where('status', 'not_started')
            ->whereNotNull('actual_start_date')
            ->where('actual_start_date', '<=', Carbon::now()->toDateString())
            ->chunkById(100, function ($phases) use (&$autoStarted): void {
                foreach ($phases as $phase) {
                    $phase->update(['status' => 'in_progress']);
                    $autoStarted++;
                }
            }, 'phase_id');

        if ($autoStarted > 0) {
            $this->info("Auto-started {$autoStarted} phase(s) based on actual start date.");
        }

        Project::query()
            ->whereIn('status', Project::statusVariants(Project::STATUS_ONGOING))
            ->where('updated_at', '<=', $cutoff)
            ->chunkById(100, function ($projects) use (&$count): void {
                foreach ($projects as $project) {
                    $reason = 'Automatically placed on hold after 14 days without project updates.';
                    $project->forceFill([
                        'status' => Project::STATUS_ON_HOLD,
                        'hold_reason' => $reason,
                    ]);
                    $project->setStatusChangeReason($reason);
                    $project->save();
                    $count++;
                }
            }, 'project_id');

        $this->info("Placed {$count} project(s) on hold.");

        return self::SUCCESS;
    }
}
