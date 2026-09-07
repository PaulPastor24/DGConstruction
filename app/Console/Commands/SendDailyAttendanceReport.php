<?php

namespace App\Console\Commands;

use App\Mail\DailyAttendanceReport;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyAttendanceReport extends Command
{
    protected $signature = 'attendance:daily-report {date? : Date to report, defaults to yesterday}';
    protected $description = 'Email the daily attendance report to administrators.';

    public function handle(): int
    {
        $date = Carbon::parse($this->argument('date') ?: 'yesterday')->startOfDay();
        $records = Attendance::with(['worker', 'deployment.project'])
            ->whereDate('log_date', $date->toDateString())
            ->orderBy('time_in')
            ->get();

        $admins = User::query()
            ->whereIn('role', ['engineer', 'admin', 'administrator'])
            ->where('is_active', true)
            ->whereNotNull('email')
            ->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new DailyAttendanceReport($date, $records));
        }

        $this->info("Sent {$admins->count()} attendance report email(s) for {$date->toDateString()}.");

        return self::SUCCESS;
    }
}
