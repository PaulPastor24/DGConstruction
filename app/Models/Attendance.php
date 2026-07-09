<?php

namespace App\Models;

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
        'status',
        'remarks',
        'biometric_matched',
        'created_at',
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
}