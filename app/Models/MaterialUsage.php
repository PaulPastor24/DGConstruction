<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialUsage extends Model
{
    protected $table = 'material_usages';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'project_id',
        'phase_id',
        'material_id',
        'quantity_used',
        'unit',
        'usage_date',
        'remarks',
        'recorded_by',
        'site_photo_path',
    ];

    protected $casts = [
        'usage_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function phase()
    {
        return $this->belongsTo(ConstructionPhase::class, 'phase_id', 'phase_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id', 'id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by', 'user_id');
    }
}
