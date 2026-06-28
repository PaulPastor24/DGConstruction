<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance_logs';
    protected $primaryKey = 'log_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false; // This table only has created_at

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

    // Relationships
    public function project()
    {
        // Attendance now links to a ProjectWorker deployment which links to a project
        return $this->hasOneThrough(Project::class, ProjectWorker::class, 'deployment_id', 'project_id', 'deployment_id', 'project_id');
    }

    public function worker()
    {
        // Prefer accessing via the deployment relation
        return $this->belongsTo(Worker::class, 'worker_id', 'worker_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by', 'user_id');
    }

    public function deployment()
    {
        return $this->belongsTo(ProjectWorker::class, 'deployment_id', 'deployment_id');
    }
}