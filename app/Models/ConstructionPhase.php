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
    ];

    protected $casts = [
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'completion_percentage' => 'decimal:2',
        'admin_progress_override' => 'decimal:2',
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
        if (!$this->isReadyToComplete() || $this->status === self::STATUS_COMPLETED) {
            return;
        }

        $payload = ['status' => self::STATUS_COMPLETED];
        if (Schema::hasColumn('construction_phases', 'actual_end_date')) {
            $payload['actual_end_date'] = $this->actual_end_date?->toDateString() ?? now()->toDateString();
        }

        $this->forceFill($payload)->save();
    }

    public function getProgressPercentageAttribute(): float
    {
        if (!Schema::hasTable('timeline_milestones')) {
            return (float) ($this->admin_progress_override ?? 0);
        }

        $milestones = $this->relationLoaded('milestones')
            ? $this->milestones
            : $this->milestones()->get(['milestone_id', 'is_completed', 'progress_percentage']);

        $total = $milestones->count();

        if ($total === 0) {
            return (float) ($this->admin_progress_override ?? 0);
        }

        $totalProgress = $milestones->sum(function ($milestone) {
            $progress = (float) ($milestone->progress_percentage ?? 0);
            if ($milestone->is_completed) {
                $progress = max($progress, 100.0);
            }
            return min($progress, 100.0);
        });

        $milestoneProgress = round($totalProgress / $total, 2);
        $override = (float) ($this->admin_progress_override ?? $milestoneProgress);

        return round(($milestoneProgress * 0.7) + ($override * 0.3), 2);
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