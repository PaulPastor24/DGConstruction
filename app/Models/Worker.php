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
        'role',
        'schedule_start',
        'schedule_end',
        'break_minutes',
        'contact_number',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'break_minutes' => 'integer',
    ];

    public function getScheduleStartAttribute($value): string
    {
        return $value ?: ($this->role === 'staff' ? '07:00:00' : '07:00:00');
    }

    public function getScheduleEndAttribute($value): string
    {
        return $value ?: ($this->role === 'staff' ? '15:00:00' : '17:00:00');
    }

    public function getBreakMinutesAttribute($value): int
    {
        return (int) ($value ?: 60);
    }

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