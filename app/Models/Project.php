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
        'project_id',
        'project_name',
        'project_location',
        'location',
        'client_id',
        'engineer_id',
        'start_date',
        'target_end_date',
        'actual_end_date',
        'time_in',
        'time_out',
        'status',
        'description',
        'project_image',
    ];

    protected $casts = [
        'start_date' => 'date',
        'target_end_date' => 'date',
        'actual_end_date' => 'date',
        'time_in' => 'datetime:H:i',
        'time_out' => 'datetime:H:i',
    ];

    protected $appends = ['location'];

    /**
     * Full public URL for the project cover image (or null when not set).
     */
    public function getImageUrlAttribute()
    {
        $image = $this->project_image;

        if (empty($image)) {
            return null;
        }

        if (is_string($image) && preg_match('#^https?://#i', $image)) {
            return $image;
        }

        if (is_string($image)) {
            $path = ltrim($image, '/');
            if (str_starts_with($path, 'storage/')) {
                return asset($path);
            }

            return asset('storage/' . $path);
        }

        return null;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'project_id';
    }

    /*
    |--------------------------------------------------------------------------
    | BLADE VIEW COMPATIBILITY ACCESSORS
    |--------------------------------------------------------------------------
    | These align your column names with the expressions inside status.blade.php
    */

    /**
     * Maps $project->name to project_name
     */
    public function getNameAttribute()
    {
        return $this->project_name;
    }

    /**
     * Maps $project->location to the stored project location field.
     */
    public function getLocationAttribute()
    {
        return $this->attributes['project_location'] ?? $this->attributes['location'] ?? null;
    }

    /**
     * Maps $project->status_text to your formatted status
     */
    public function getStatusTextAttribute()
    {
        return $this->status_label;
    }

    /**
     * Maps $project->current_phase_name to your current phase helper
     */
    public function getCurrentPhaseNameAttribute()
    {
        return $this->current_phase;
    }

    /**
     * Maps $project->manager_name to the assigned Engineer's name
     */
    public function getManagerNameAttribute()
    {
        return $this->engineer ? $this->engineer->name : 'Not Assigned';
    }

    /**
     * Human-readable "7:00 AM - 4:00 PM" style label for the project's
     * assigned attendance schedule, or a fallback message when unset.
     */
    public function getScheduleLabelAttribute()
    {
        if (!$this->time_in || !$this->time_out) {
            return 'Not set';
        }

        return $this->time_in->format('g:i A') . ' - ' . $this->time_out->format('g:i A');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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
    }

    /**
     * Relationship: Project has many Construction Phases
     */
    public function phases()
    {
        return $this->hasMany(ConstructionPhase::class, 'project_id', 'project_id');
    }

    /**
     * Relationship: Project has many attendance logs through deployments
     */
    public function attendanceLogs()
    {
        return $this->hasManyThrough(
            Attendance::class,
            ProjectWorker::class,
            'project_id',
            'deployment_id',
            'project_id',
            'deployment_id'
        );
    }

    /**
     * Relationship: Project has many workers through the pivot table
     */
    public function workers()
    {
        return $this->belongsToMany(Worker::class, 'project_workers', 'project_id', 'worker_id', 'project_id', 'worker_id');
    }

    /**
     * Relationship: raw project_workers deployments
     */
    public function projectWorkers()
    {
        return $this->hasMany(ProjectWorker::class, 'project_id', 'project_id');
    }

    /**
     * Relationship: Project has many reports through phases
     */
    public function reports()
    {
        return $this->hasManyThrough(Report::class, ConstructionPhase::class, 'project_id', 'phase_id', 'project_id', 'phase_id');
    }

    /**
     * Relationship: Project has many material assignments
     */
    public function projectMaterials()
    {
        return $this->hasMany(ProjectMaterial::class, 'project_id', 'project_id');
    }

    /**
     * Relationship: Project has many material usage records
     */
    public function materialUsages()
    {
        return $this->hasMany(MaterialUsage::class, 'project_id', 'project_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes / Computed Attributes
    |--------------------------------------------------------------------------
    */

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
     * Calculate progress automatically whether relations are eager-loaded or lazy-loaded
     */
    public function getProgressPercentageAttribute()
    {
        $phasesCount = $this->phases()->count();
        if ($phasesCount > 0) {
            return round($this->phases()->avg('completion_percentage'), 2);
        }
        return 0;
    }

    /**
     * Retrieve current phase cleanly with query fallbacks
     */
    public function getCurrentPhaseAttribute()
    {
        $currentPhase = $this->phases()
            ->where('status', 'in_progress')
            ->orderBy('phase_order', 'asc')
            ->first();

        return $currentPhase ? $currentPhase->phase_name : 'Phase 1: Mobilization';
    }

    /**
     * Maps back to controller custom field wrapper targets if missing from structural objects
     */
    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status ?? 'Planning');
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