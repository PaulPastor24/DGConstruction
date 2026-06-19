<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConstructionPhase extends Model
{
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
        'actual_start_date',
        'actual_end_date',
        'completion_percentage',
        'status',
    ];

    protected $casts = [
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'completion_percentage' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'phase_id', 'phase_id');
    }
}