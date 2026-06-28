<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'materials';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'category',
        'unit',
        'description',
    ];

    public function deliveries()
    {
        return $this->hasMany(MaterialDelivery::class, 'material_id', 'id');
    }
}
