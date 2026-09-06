<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class Project extends Model
{
    public const STATUS_PLANNING = 'planning';
    public const STATUS_ONGOING = 'ongoing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ON_HOLD = 'on_hold';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_PLANNING,
        self::STATUS_ONGOING,
        self::STATUS_COMPLETED,
        self::STATUS_ON_HOLD,
        self::STATUS_ARCHIVED,
    ];

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
        'hold_reason',
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

    protected $appends = [
        'location',
        'workflow_status_label',
        'workflow_status_class',
        'phase_count',
        'milestone_count',
        'report_count',
        'material_count',
        'attendance_count',
    ];
    protected ?string $statusChangeReason = null;

    protected static function booted(): void
    {
        static::saving(function (self $project): void {
            $project->status = self::normalizeStatus($project->getAttribute('status'));
        });

        static::updated(function (self $project): void {
            if (!$project->wasChanged('status') || !Schema::hasTable('project_status_histories')) {
                return;
            }

            ProjectStatusHistory::create([
                'project_id' => $project->project_id,
                'from_status' => $project->getOriginal('status') === null ? null : self::normalizeStatus($project->getOriginal('status')),
                'to_status' => $project->workflowStatus(),
                'changed_by' => Auth::id(),
                'reason' => $project->statusChangeReason ?? $project->hold_reason,
            ]);

            $project->statusChangeReason = null;
        });
    }

    public static function normalizeStatus(?string $status): string
    {
        return match (strtolower(trim((string) $status))) {
            'ongoing', 'in_progress', 'inprogress', 'active' => self::STATUS_ONGOING,
            'completed', 'complete', 'finished' => self::STATUS_COMPLETED,
            'on_hold' => self::STATUS_ON_HOLD,
            'archived' => self::STATUS_ARCHIVED,
            'planning', 'pending', 'not_started', 'paused', 'delayed' => self::STATUS_PLANNING,
            default => self::STATUS_PLANNING,
        };
    }

    public static function statusLabel(?string $status): string
    {
        return match (self::normalizeStatus($status)) {
            self::STATUS_ONGOING => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_ON_HOLD => 'On Hold',
            self::STATUS_ARCHIVED => 'Archived',
            default => 'Planning',
        };
    }

    public function setStatusChangeReason(?string $reason): self
    {
        $this->statusChangeReason = $reason;

        return $this;
    }

    public static function statusVariants(string $status): array
    {
        return match (self::normalizeStatus($status)) {
            self::STATUS_ONGOING => ['ongoing', 'in_progress', 'inprogress', 'active'],
            self::STATUS_COMPLETED => ['completed', 'complete', 'finished'],
            self::STATUS_ON_HOLD => ['on_hold'],
            self::STATUS_ARCHIVED => ['archived'],
            default => ['planning', 'pending', 'not_started', 'paused', 'delayed'],
        };
    }

    public function canTransitionTo(?string $requestedStatus): bool
    {
        $current = self::normalizeStatus($this->getRawOriginal('status') ?? $this->status);
        $requested = self::normalizeStatus($requestedStatus);

        if ($current === self::STATUS_ARCHIVED) {
            return false;
        }

        return match ($current) {
            self::STATUS_PLANNING => in_array($requested, [self::STATUS_PLANNING, self::STATUS_ONGOING], true),
            self::STATUS_ONGOING => in_array($requested, [self::STATUS_ONGOING, self::STATUS_ON_HOLD, self::STATUS_COMPLETED], true),
            self::STATUS_ON_HOLD => in_array($requested, [self::STATUS_ON_HOLD, self::STATUS_PLANNING], true),
            self::STATUS_COMPLETED => $requested === self::STATUS_COMPLETED,
            default => false,
        };
    }

    public function allowedStatusTransitions(): array
    {
        return array_values(array_filter(self::STATUSES, fn (string $status) => $this->canTransitionTo($status)));
    }

    public function getWorkflowStatusLabelAttribute(): string
    {
        return self::statusLabel($this->workflowStatus());
    }

    public function getWorkflowStatusClassAttribute(): string
    {
        return match ($this->workflowStatus()) {
            self::STATUS_ONGOING => 'in-progress',
            self::STATUS_COMPLETED, self::STATUS_ARCHIVED => 'completed',
            self::STATUS_ON_HOLD => 'on-hold',
            default => 'planning',
        };
    }

    public function workflowStatus(): string
    {
        return self::normalizeStatus($this->getRawOriginal('status') ?? $this->status);
    }

    public function syncStatusFromPhases(): void
    {
        if ($this->workflowStatus() === self::STATUS_ARCHIVED) {
            return;
        }

        $phases = $this->phases()->get(['phase_id', 'status', 'completion_percentage']);
        if ($phases->isEmpty()) {
            return;
        }

        $allComplete = $phases->every(fn ($phase) => $phase->progress_percentage >= 100);
        $clearHoldReason = Schema::hasColumn('projects', 'hold_reason');
        if ($this->actual_end_date && $allComplete) {
            $payload = ['status' => self::STATUS_COMPLETED];
            if ($clearHoldReason) $payload['hold_reason'] = null;
            $this->forceFill($payload)->save();
        } elseif ($allComplete && $this->actual_end_date === null) {
            $payload = [
                'status' => self::STATUS_COMPLETED,
                'actual_end_date' => now()->toDateString(),
            ];
            if ($clearHoldReason) $payload['hold_reason'] = null;
            $this->forceFill($payload)->save();
        } elseif ($phases->contains(fn ($phase) => $phase->status === 'in_progress')) {
            $payload = ['status' => self::STATUS_ONGOING];
            if ($clearHoldReason) $payload['hold_reason'] = null;
            $this->forceFill($payload)->save();
        }
    }

    public function statusHistory()
    {
        return $this->hasMany(ProjectStatusHistory::class, 'project_id', 'project_id')->latest();
    }

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

    public function milestones()
    {
        return $this->hasManyThrough(
            Milestone::class,
            ConstructionPhase::class,
            'project_id',
            'phase_id',
            'project_id',
            'phase_id'
        );
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

    public function getPhaseCountAttribute(): int
    {
        if (array_key_exists('phase_count', $this->attributes)) {
            return (int) $this->attributes['phase_count'];
        }

        return $this->phases()->count();
    }

    public function getMilestoneCountAttribute(): int
    {
        if (array_key_exists('milestone_count', $this->attributes)) {
            return (int) $this->attributes['milestone_count'];
        }

        return $this->milestones()->count();
    }

    public function getReportCountAttribute(): int
    {
        if (array_key_exists('report_count', $this->attributes)) {
            return (int) $this->attributes['report_count'];
        }

        return $this->reports()->count();
    }

    public function getMaterialCountAttribute(): int
    {
        if (array_key_exists('material_count', $this->attributes)) {
            return (int) $this->attributes['material_count'];
        }

        return $this->projectMaterials()->count();
    }

    public function getAttendanceCountAttribute(): int
    {
        if (array_key_exists('attendance_count', $this->attributes)) {
            return (int) $this->attributes['attendance_count'];
        }

        return $this->attendanceLogs()->count();
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
        if ($this->relationLoaded('supervisors')) {
            return $this->supervisors->first(function ($supervisor) {
                return (bool) ($supervisor->pivot?->is_active ?? false);
            });
        }

        return $this->supervisors()
            ->wherePivot('is_active', true)
            ->first();
    }

    /**
     * Calculate overall project progress across every phase.
     *
     * Not-started phases remain part of the denominator, so a project with
     * one phase at 65% and two phases at 0% correctly reports 21.67% overall.
     */
    public function getProgressPercentageAttribute()
    {
        $phases = $this->relationLoaded('phases') ? $this->phases : $this->phases()->get();
        if ($phases->isEmpty()) {
            return 0.0;
        }

        return round($phases->avg(fn ($phase) => (float) $phase->progress_percentage), 2);
    }

    /**
     * Explicit project-level progress name for dashboards and management tables.
     * This avoids confusing the project's overall value with a phase progress value.
     */
    public function getOverallProgressPercentageAttribute(): float
    {
        return (float) $this->progress_percentage;
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
        return $this->workflow_status_label;
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->workflowStatus()) {
            self::STATUS_PLANNING => 'secondary',
            self::STATUS_ONGOING => 'primary',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_ON_HOLD => 'warning',
            self::STATUS_ARCHIVED => 'dark',
            default => 'secondary',
        };
    }
}