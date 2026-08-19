<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class Attendance extends Model
{
    protected $table = 'attendance_logs';

    protected $primaryKey = 'log_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'worker_id',
        'deployment_id',
        'recorded_by',
        'log_date',
        'time_in',
        'break_out',
        'break_in',
        'time_out',
        'status',
        'remarks',
        'biometric_matched',
        'created_at',
    ];

    protected $appends = [
        'overtime_minutes',
        'overtime_hours',
        'overtime_label',
    ];

    protected $casts = [
        'log_date' => 'date',
        'biometric_matched' => 'boolean',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(
            Worker::class,
            'worker_id',
            'worker_id'
        );
    }

    public function deployment(): BelongsTo
    {
        return $this->belongsTo(
            ProjectWorker::class,
            'deployment_id',
            'deployment_id'
        );
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'recorded_by',
            'user_id'
        );
    }

    public function getDisplayWorkerAttribute()
    {
        return $this->worker ?: $this->deployment?->worker;
    }

    public function getDisplayProjectAttribute()
    {
        return $this->deployment?->project;
    }

    public static function scheduleForRole(string $role = 'worker'): array
    {
        $role = User::normalizeRole($role);

        $rule = Schema::hasTable('attendance_schedule_rules')
            ? AttendanceScheduleRule::query()
                ->where('role', $role)
                ->where('is_active', true)
                ->first()
            : null;

        return $rule
            ? [
                'start_time' => $rule->start_time,
                'end_time' => $rule->end_time,
                'break_start_time' => $rule->break_start_time,
                'break_end_time' => $rule->break_end_time,
            ]
            : AttendanceScheduleRule::defaultsForRole($role);
    }

    public static function calculateOvertimeMinutes($logDate, $timeIn, $timeOut, string $role = 'worker'): int
    {
        if (! $logDate || ! $timeOut) {
            return 0;
        }

        try {
            $timeOutDateTime = Carbon::parse($logDate.' '.$timeOut);
            $schedule = self::scheduleForRole($role);
            $overtimeCutoff = Carbon::parse($logDate.' '.$schedule['end_time']);

            return max(0, $overtimeCutoff->diffInMinutes($timeOutDateTime, false));
        } catch (\Throwable $error) {
            return 0;
        }
    }

    public static function formatOvertimeLabel(int $minutes): string
    {
        if ($minutes <= 0) {
            return '—';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;
        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours.'h';
        }

        if ($remainingMinutes > 0 || empty($parts)) {
            $parts[] = $remainingMinutes.'m';
        }

        return 'OT '.implode(' ', $parts);
    }

    public function getOvertimeMinutesAttribute(): int
    {
        return self::calculateOvertimeMinutes($this->log_date, $this->time_in, $this->time_out);
    }

    public function getOvertimeHoursAttribute(): float
    {
        return round($this->overtime_minutes / 60, 2);
    }

    public function getOvertimeLabelAttribute(): string
    {
        return self::formatOvertimeLabel($this->overtime_minutes);
    }
}
