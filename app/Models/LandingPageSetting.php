<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPageSetting extends Model
{
    protected $casts = ['customer_feedback' => 'array'];
    protected $fillable = [
        'customer_feedback',
        'hero_image_path',
    ];
}
