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
        'accomplishment_percentage',
        'is_published_to_client',
        'admin_report_text',
        'admin_site_images',
        'admin_explanation',
        'published_at',
    ];

    protected $casts = [
        'report_date' => 'date',
        'site_images' => 'array',
        'admin_site_images' => 'array',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'published_at' => 'datetime',
        'is_published_to_client' => 'boolean',
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
            'approved' => $this->is_published_to_client ? 'Published to Client' : 'Approved (Hidden)',
            'rejected' => 'Returned for Revision',
            default => 'Pending Review',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->approval_status) {
            'approved' => $this->is_published_to_client ? 'published' : 'approved',
            'rejected' => 'rejected',
            default => 'pending',
        };
    }

    public function getClientReportTextAttribute(): string
    {
        return $this->admin_report_text ?? $this->report_text ?? '';
    }

    public function getClientSiteImagesAttribute(): array
    {
        $images = $this->admin_site_images ?? $this->site_images ?? [];

        return array_values(array_filter(array_map(function ($image) {
            return is_string($image) && $image ? asset('storage/' . ltrim($image, '/')) : null;
        }, (array) $images)));
    }

    public function getClientExplanationAttribute(): string
    {
        return $this->admin_explanation ?? $this->approval_remarks ?? '';
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

    /**
     * Scope for reports published to client
     */
    public function scopePublishedToClient($query)
    {
        return $query->where('approval_status', 'approved')->where('is_published_to_client', true);
    }
}