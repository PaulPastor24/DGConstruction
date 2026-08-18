<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\NotifyPhaseDeadlines;
use App\Console\Commands\ScanMilestonesAndMaterials;
use App\Console\Commands\SendDailyAttendanceReport;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        NotifyPhaseDeadlines::class,
        ScanMilestonesAndMaterials::class,
        SendDailyAttendanceReport::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('notify:phases-deadlines')->dailyAt('08:00');
        $schedule->command('notifications:scan')->dailyAt('07:00');
        // Run just after midnight so the previous day's attendance is complete.
        $schedule->command('attendance:send-daily-report')->dailyAt('00:05')->withoutOverlapping();
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
