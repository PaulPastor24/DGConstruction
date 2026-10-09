<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingGalleryProjectImage extends Model
{
    protected $fillable = [
        'landing_gallery_image_id',
        'image_path',
        'sort_order',
    ];

    protected $casts = [
        'landing_gallery_image_id' => 'integer',
        'sort_order' => 'integer',
    ];
}
