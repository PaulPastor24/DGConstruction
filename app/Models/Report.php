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
        'reviewed_by',
        'approved_by',
        'report_date',
        'report_text',
        'site_images',
        'approval_status',
        'ai_status',
        'approval_remarks',
        'reviewed_at',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'report_date' => 'date',
        'site_images' => 'array',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function getReportTitleAttribute(): string
    {
        $projectName = optional($this->project)->project_name ?? 'Construction Report';
        $phaseName = optional($this->phase)->phase_name ?? 'Project Phase';

        return trim("{$projectName} - {$phaseName}");
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->approval_status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Pending Review',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->approval_status) {
            'approved' => 'approved',
            'rejected' => 'rejected',
            default => 'pending',
        };
    }

    public function getReportIdentifierAttribute(): string
    {
        return 'RPT-' . str_pad((string) $this->report_id, 4, '0', STR_PAD_LEFT);
    }

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

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    /**
     * Scope for pending reports
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    /**
     * Scope for approved reports
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Scope for rejected reports
     */
    public function scopeRejected($query)
    {
        return $query->where('approval_status', 'rejected');
    }
}