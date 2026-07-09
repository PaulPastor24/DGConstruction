<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    protected $table = 'timeline_milestones';
    protected $primaryKey = 'milestone_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'phase_id',
        'milestone_name',
        'start_date',
        'end_date',
        'is_completed',
        'is_delayed',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_completed' => 'boolean',
        'is_delayed' => 'boolean',
    ];

    public function phase()
    {
        return $this->belongsTo(ConstructionPhase::class, 'phase_id', 'phase_id');
    }

    public function project()
    {
        return $this->hasOneThrough(
            Project::class,
            ConstructionPhase::class,
            'phase_id',
            'project_id',
            'phase_id',
            'project_id'
        );
    }

    /**
     * Scope to get delayed milestones
     */
    public function scopeDelayed($query)
    {
        return $query->where('is_delayed', true)->where('is_completed', false);
    }

    /**
     * Scope to get completed milestones
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope to get upcoming milestones
     */
    public function scopeUpcoming($query)
    {
        return $query->where('is_completed', false)
            ->where('is_delayed', false)
            ->where('start_date', '>', now());
    }
}
