<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMaterial extends Model
{
    protected $table = 'project_materials';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'project_id',
        'material_id',
        'planned_quantity',
        'used_quantity',
        'unit',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id', 'id');
    }
}
