<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialDelivery extends Model
{
    protected $table = 'material_deliveries';

    protected $primaryKey = 'delivery_id';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'material_id',
        'quantity',
        'unit',
        'total_price',
        'supplier_name',
        'delivered_at',
        'notes',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id', 'id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }
}
