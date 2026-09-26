<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingGalleryImage extends Model
{
    protected $fillable = [
        'project_id',
        'image_path',
        'sort_order',
        'is_active',
        'is_external',
        'external_project_name',
        'external_project_location',
        'external_project_description',
        'external_project_url',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_external' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->is_external) {
            return $this->external_project_name ?? 'External Project';
        }

        return $this->project->project_name ?? 'Unknown Project';
    }

    public function getDisplayLocationAttribute(): ?string
    {
        if ($this->is_external) {
            return $this->external_project_location;
        }

        return $this->project->location ?? null;
    }

    public function getDisplayDescriptionAttribute(): ?string
    {
        if ($this->is_external) {
            return $this->external_project_description;
        }

        return $this->project->description ?? null;
    }

    public function getDisplayUrlAttribute(): ?string
    {
        if ($this->is_external) {
            return $this->external_project_url;
        }

        return route('landing-gallery.projects.show', $this->project);
    }
}