<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tool extends Model
{
    protected $table = 'tools';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'tool_code',
        'name',
        'category',
        'type',
        'unit',
        'condition',
        'purchase_date',
        'purchase_price',
        'description',
        'status',
        'current_borrower_worker_id',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'status' => 'string',
    ];

    protected $attributes = [
        'status' => 'available',
        'type' => 'tool',
        'condition' => 'good',
    ];

    public function currentBorrower(): BelongsTo
    {
        return $this->belongsTo(Worker::class, 'current_borrower_worker_id', 'worker_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(ToolLoan::class, 'tool_id', 'id');
    }

    public function activeLoan(): HasMany
    {
        return $this->hasMany(ToolLoan::class, 'tool_id', 'id')->where('status', 'borrowed');
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(ToolDeduction::class, 'tool_id', 'id');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'available' => 'badge-available',
            'in_use' => 'badge-in-use',
            'lost' => 'badge-lost',
            'retired' => 'badge-retired',
            default => 'bg-secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'available' => 'Available',
            'in_use' => 'In Use',
            'lost' => 'Lost',
            'retired' => 'Retired',
            default => ucfirst($this->status),
        };
    }
}
