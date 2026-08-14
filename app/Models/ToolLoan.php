<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolLoan extends Model
{
    protected $table = 'tool_loans';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'tool_id',
        'worker_id',
        'project_id',
        'expected_return_date',
        'condition_at_issue',
        'notes',
        'status',
        'borrowed_at',
        'returned_at',
        'condition_at_return',
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'returned_at' => 'datetime',
        'expected_return_date' => 'date',
    ];

    protected $attributes = [
        'status' => 'borrowed',
    ];

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class, 'tool_id', 'id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class, 'worker_id', 'worker_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function deduction()
    {
        return $this->hasOne(ToolDeduction::class, 'tool_loan_id', 'id');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'borrowed' => 'badge-loan-borrowed',
            'returned' => 'badge-loan-returned',
            'lost' => 'badge-lost',
            default => 'bg-secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'borrowed' => 'Borrowed',
            'returned' => 'Returned',
            'lost' => 'Lost',
            default => ucfirst($this->status),
        };
    }
}
