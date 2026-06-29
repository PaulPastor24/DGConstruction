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

    /**
     * Disable Laravel's automatic created_at and updated_at handling.
     */
    public $timestamps = false;

    protected $fillable = [
        'deployment_id',
        'recorded_by',
        'log_date',
        'time_in',
        'time_out',
        'status',
        'remarks',
        'biometric_matched',
    ];

    protected $casts = [
        'log_date' => 'date',
        'biometric_matched' => 'boolean',
    ];

    /**
     * Deployment connected to the attendance record.
     *
     * attendance_logs.deployment_id
     * connects to project_workers.deployment_id.
     */
    public function deployment(): BelongsTo
    {
        return $this->belongsTo(
            ProjectWorker::class,
            'deployment_id',
            'deployment_id'
        );
    }

    /**
     * User who recorded the attendance.
     *
     * attendance_logs.recorded_by
     * connects to users.user_id.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'recorded_by',
            'user_id'
        );
    }

    /**
     * Convenient access to the worker through the deployment.
     */
    public function getWorkerAttribute()
    {
        return $this->deployment?->worker;
    }

    /**
     * Convenient access to the project through the deployment.
     */
    public function getProjectAttribute()
    {
        return $this->deployment?->project;
    }
}
