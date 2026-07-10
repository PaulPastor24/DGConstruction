<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'materials';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'category',
        'unit',
        'current_stock',
        'minimum_stock_level',
        'supplier',
        'description',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'minimum_stock_level' => 'decimal:2',
    ];

    public function deliveries()
    {
        return $this->hasMany(MaterialDelivery::class, 'material_id', 'id');
    }

    public function usages()
    {
        return $this->hasMany(MaterialUsage::class, 'material_id', 'id');
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->current_stock <= 0) {
            return 'Out of Stock';
        }

        if ($this->current_stock <= $this->minimum_stock_level) {
            return 'Low Stock';
        }

        return 'Normal';
    }
}
