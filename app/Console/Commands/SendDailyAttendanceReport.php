<?php

namespace App\Console\Commands;

use App\Mail\DailyAttendanceReport;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class SendDailyAttendanceReport extends Command
{
    protected $signature = 'attendance:send-daily-report {date? : Date in YYYY-MM-DD format; defaults to yesterday}';
    protected $description = 'Email the daily attendance summary to administrator accounts';

    public function handle(): int
    {
        if (! Schema::hasTable('attendance_logs')) {
            $this->warn('Attendance logs table is not available.');
            return self::SUCCESS;
        }

        try {
            $date = Carbon::parse($this->argument('date') ?: yesterday())->toDateString();
        } catch (\Throwable) {
            $this->error('Invalid date. Use YYYY-MM-DD.');
            return self::FAILURE;
        }

        $records = Attendance::query()
            ->with('worker')
            ->whereDate('log_date', $date)
            ->orderBy('time_in')
            ->get()
            ->map(function (Attendance $attendance) {
                $worker = $attendance->display_worker;
                return [
                    'worker' => trim(($worker?->first_name ?? '').' '.($worker?->last_name ?? '')) ?: 'Unknown worker',
                    'time_in' => $attendance->time_in,
                    'time_out' => $attendance->time_out,
                    'overtime_label' => $attendance->overtime_label,
                    'overtime_minutes' => $attendance->overtime_minutes,
                    'status' => ucwords(str_replace('_', ' ', (string) $attendance->status)),
                ];
            });

        if ($records->isEmpty()) {
            $this->info("No attendance records found for {$date}; no email was sent.");
            return self::SUCCESS;
        }

        $summary = [
            'total' => $records->count(),
            'present' => $records->where('status', 'Present')->count(),
            'late' => $records->filter(fn ($record) => in_array($record['status'], ['Late', 'Half Day'], true))->count(),
            'absent' => $records->where('status', 'Absent')->count(),
            'overtime_records' => $records->filter(fn ($record) => $record['overtime_minutes'] > 0)->count(),
            'overtime_hours' => round($records->sum('overtime_minutes') / 60, 2),
        ];

        $recipients = User::query()
            ->whereIn('role', ['engineer', 'admin', 'administrator'])
            ->where('is_active', true)
            ->whereNotNull('email')
            ->pluck('email');

        if ($recipients->isEmpty()) {
            $this->warn('No active admin recipients were found.');
            return self::SUCCESS;
        }

        foreach ($recipients as $email) {
            Mail::to($email)->send(new DailyAttendanceReport($date, $summary, $records));
        }

        $this->info("Attendance report for {$date} sent to {$recipients->count()} admin recipient(s).");
        return self::SUCCESS;
    }
}
