<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\NotifyPhaseDeadlines;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        NotifyPhaseDeadlines::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('notify:phases-deadlines')->dailyAt('08:00');
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
