<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectWorker extends Model
{
    protected $table = 'project_workers';
    protected $primaryKey = 'deployment_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'worker_id',
        'assigned_role',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id', 'worker_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function attendanceLogs()
    {
        return $this->hasMany(Attendance::class, 'deployment_id', 'deployment_id');
    }
}
