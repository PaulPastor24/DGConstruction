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
