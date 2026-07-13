<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'materials';
    protected $primaryKey = 'id';

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

    protected $attributes = [
        'category' => 'General',
        'current_stock' => 0,
        'minimum_stock_level' => 0,
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
        $currentStock = (float) $this->current_stock;
        $minimumStock = (float) $this->minimum_stock_level;

        if ($currentStock <= 0) {
            return 'Out of Stock';
        }

        if ($currentStock <= $minimumStock) {
            return 'Low Stock';
        }

        return 'Available';
    }

    public function getStockBadgeClassAttribute(): string
    {
        return match ($this->stock_status) {
            'Out of Stock' => 'badge-out-of-stock',
            'Low Stock' => 'badge-low-stock',
            default => 'badge-available',
        };
    }
}