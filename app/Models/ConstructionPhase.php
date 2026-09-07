<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ConstructionPhase extends Model
{
    public const STATUS_PENDING = 'not_started';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DELAYED = 'delayed';
    public const STATUS_COMPLETED = 'completed';

    public const DELAY_REASONS = [
        'weather', 'materials', 'permits', 'design_change', 'labor', 'equipment', 'other',
    ];

    protected $table = 'construction_phases';
    protected $primaryKey = 'phase_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'project_id',
        'phase_name',
        'phase_order',
        'planned_start_date',
        'planned_end_date',
        'completion_percentage',
        'status',
        'actual_start_date',
        'actual_end_date',
        'delay_reason',
        'delay_notes',
        'depends_on_phase_id',
        'notes',
        'admin_progress_override',
        'override_reason',
        'override_applied_at',
        'override_applied_by',
    ];

    protected $casts = [
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'completion_percentage' => 'decimal:2',
        'admin_progress_override' => 'decimal:2',
        'override_applied_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'phase_id', 'phase_id');
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class, 'phase_id', 'phase_id');
    }

    public function dependency()
    {
        return $this->belongsTo(self::class, 'depends_on_phase_id', 'phase_id');
    }

    public function dependents()
    {
        return $this->hasMany(self::class, 'depends_on_phase_id', 'phase_id');
    }

    public function overrideAppliedBy()
    {
        return $this->belongsTo(User::class, 'override_applied_by', 'user_id');
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isReadyToStart(): bool
    {
        return !$this->dependency || $this->dependency->status === self::STATUS_COMPLETED;
    }

    public function hasDependencyCycle(?int $dependencyId): bool
    {
        $seen = [];
        while ($dependencyId) {
            if ($dependencyId === (int) $this->phase_id || isset($seen[$dependencyId])) {
                return true;
            }

            $seen[$dependencyId] = true;
            $dependencyId = (int) (self::whereKey($dependencyId)->value('depends_on_phase_id') ?? 0);
        }

        return false;
    }

    public function isReadyToComplete(): bool
    {
        return $this->progress_percentage >= 100
            && (!Schema::hasTable('timeline_milestones') || !$this->milestones()->where('is_completed', false)->exists());
    }

    public function syncStatusFromMilestones(): void
    {
        if (!Schema::hasTable('timeline_milestones')) {
            return;
        }

        $progress = (float) $this->progress_percentage;
        $payload = ['completion_percentage' => $progress];

        if ($this->isReadyToComplete()) {
            $payload['status'] = self::STATUS_COMPLETED;
            if (Schema::hasColumn('construction_phases', 'actual_end_date')) {
                $payload['actual_end_date'] = $this->actual_end_date?->toDateString() ?? now()->toDateString();
            }
        } elseif ($this->status === self::STATUS_COMPLETED) {
            // A deleted or reset milestone must not leave its phase permanently completed.
            $payload['status'] = $this->actual_start_date ? self::STATUS_IN_PROGRESS : self::STATUS_PENDING;
            if (Schema::hasColumn('construction_phases', 'actual_end_date')) {
                $payload['actual_end_date'] = null;
            }
        }

        if ($this->getDirtyValues($payload)) {
            $this->forceFill($payload)->save();
        }
    }

    private function getDirtyValues(array $payload): array
    {
        return array_filter($payload, function ($value, $key) {
            return $this->getAttribute($key) != $value;
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function getProgressPercentageAttribute(): float
    {
        if (!$this->relationLoaded('milestones') && !Schema::hasTable('timeline_milestones')) {
            return (float) ($this->admin_progress_override ?? $this->getRawOriginal('completion_percentage', 0));
        }

        $milestones = $this->relationLoaded('milestones')
            ? $this->milestones
            : $this->milestones()->get(['milestone_id', 'is_completed']);

        $total = $milestones->count();

        if ($total === 0) {
            return (float) ($this->admin_progress_override ?? 0);
        }

        if ($this->admin_progress_override !== null && $this->admin_progress_override !== '') {
            return (float) $this->admin_progress_override;
        }

        return round(($milestones->where('is_completed', true)->count() / $total) * 100, 2);
    }

    public function getMilestoneProgressSummaryAttribute(): string
    {
        if (!Schema::hasTable('timeline_milestones')) {
            return '0/0 milestones';
        }

        $milestones = $this->relationLoaded('milestones')
            ? $this->milestones
            : $this->milestones()->get(['milestone_id', 'is_completed']);

        return $milestones->where('is_completed', true)->count() . '/' . $milestones->count() . ' milestones';
    }

    public function canTransitionTo(string $requestedStatus): bool
    {
        if ($this->isLocked()) {
            return $requestedStatus === self::STATUS_COMPLETED;
        }

        return match ($this->status) {
            self::STATUS_PENDING => in_array($requestedStatus, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS], true),
            self::STATUS_IN_PROGRESS => in_array($requestedStatus, [self::STATUS_IN_PROGRESS, self::STATUS_DELAYED, self::STATUS_COMPLETED], true),
            self::STATUS_DELAYED => in_array($requestedStatus, [self::STATUS_DELAYED, self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED], true),
            default => false,
        };
    }
}