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
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }
}