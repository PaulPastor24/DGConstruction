<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects';
    protected $primaryKey = 'project_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'project_name',
        'project_location',
        'client_id',
        'engineer_id',
        'start_date',
        'target_end_date',
        'actual_end_date',
        'status',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'target_end_date' => 'date',
        'actual_end_date' => 'date',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'project_id';
    }

    /**
     * Relationship: Project belongs to a Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    /**
     * Relationship: Project belongs to an Engineer (User)
     */
    public function engineer()
    {
        return $this->belongsTo(User::class, 'engineer_id', 'user_id');
    }

    /**
     * Relationship: Project has many Supervisors (many-to-many)
     */
    public function supervisors()
    {
        return $this->belongsToMany(User::class, 'project_supervisors', 'project_id', 'supervisor_id', 'project_id', 'user_id')
            ->withPivot('assigned_date', 'is_active');
        // FIXED: Removed ->withTimestamps() because project_supervisors table doesn't have updated_at
    }

    /**
     * Relationship: Project has many Construction Phases
     */
    public function phases()
    {
        return $this->hasMany(ConstructionPhase::class, 'project_id', 'project_id');
    }

    /**
     * Relationship: Project has many attendance logs
     */
    public function attendanceLogs()
    {
        return $this->hasMany(Attendance::class, 'project_id', 'project_id');
    }

    /**
     * Relationship: Project has many workers through the pivot table
     */
    public function workers()
    {
        return $this->belongsToMany(Worker::class, 'project_workers', 'project_id', 'worker_id', 'project_id', 'worker_id');
    }

    /**
     * Relationship: Project has many reports through phases
     */
    public function reports()
    {
        return $this->hasManyThrough(Report::class, ConstructionPhase::class, 'project_id', 'phase_id', 'project_id', 'phase_id');
    }

    /**
     * Get active supervisor for the project
     */
    public function getActiveSupervisorAttribute()
    {
        return $this->supervisors()
            ->wherePivot('is_active', true)
            ->first();
    }

    /**
     * Get progress percentage from phases
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->relationLoaded('phases') && $this->phases->count() > 0) {
            return round($this->phases->avg('completion_percentage'), 2);
        }
        return 0;
    }

    /**
     * Get current phase
     */
    public function getCurrentPhaseAttribute()
    {
        if ($this->relationLoaded('phases')) {
            $currentPhase = $this->phases
                ->where('status', 'in_progress')
                ->sortBy('phase_order')
                ->first();

            return $currentPhase ? $currentPhase->phase_name : null;
        }
        return null;
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'planning' => 'secondary',
            'ongoing' => 'primary',
            'completed' => 'success',
            'on_hold' => 'warning',
            'archived' => 'dark',
            default => 'secondary',
        };
    }
}