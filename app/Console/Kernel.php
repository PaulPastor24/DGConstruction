<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\NotifyPhaseDeadlines;
use App\Console\Commands\ScanMilestonesAndMaterials;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        NotifyPhaseDeadlines::class,
        ScanMilestonesAndMaterials::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('notify:phases-deadlines')->dailyAt('08:00');
        $schedule->command('notifications:scan')->dailyAt('07:00');
    }

    protected function commands()
    {
        // load commands automatically
        if (file_exists(app_path('Console/Commands'))) {
            foreach (glob(app_path('Console/Commands').'/*.php') as $file) {
                // noop - autoloaded by composer
            }
        }
    }
}
