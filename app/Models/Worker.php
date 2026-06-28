<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    protected $table = 'workers';
    protected $primaryKey = 'worker_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'first_name',
        'last_name',
        'trade',
        'contact_number',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function attendanceLogs()
    {
        return $this->hasMany(Attendance::class, 'worker_id', 'worker_id');
    }

    /**
     * Compatibility accessor for templates expecting `full_name`.
     */
    public function getFullNameAttribute()
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }
}