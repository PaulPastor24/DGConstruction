<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'accomplishment_reports';
    protected $primaryKey = 'report_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'project_id',
        'phase_id',
        'submitted_by',
        'report_date',
        'report_text',
        'site_images',
        'ai_status',
    ];

    protected $casts = [
        'report_date' => 'date',
        'site_images' => 'array',
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function phase()
    {
        return $this->belongsTo(ConstructionPhase::class, 'phase_id', 'phase_id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by', 'user_id');
    }
}