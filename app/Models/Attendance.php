<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'overtime_minutes',
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
        'overtime_minutes' => 'integer',
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

    public function getScheduledEndAttribute(): ?string
    {
        return $this->worker?->schedule_end;
    }

    public static function calculateOvertimeMinutes($logDate, $timeIn, $timeOut): int
    {
        if (! $logDate || ! $timeOut) {
            return 0;
        }

        try {
            $timeOutDateTime = Carbon::parse($logDate.' '.$timeOut);
            $overtimeCutoff = Carbon::parse($logDate.' 17:00:00');

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
        return (int) ($this->attributes['overtime_minutes'] ?? self::calculateOvertimeMinutes($this->log_date, $this->time_in, $this->time_out));
    }

    public function getOvertimeHoursAttribute(): float
    {
        return round(((int) $this->overtime_minutes) / 60, 2);
    }

    public function getOvertimeLabelAttribute(): string
    {
        return self::formatOvertimeLabel($this->overtime_minutes);
    }
}