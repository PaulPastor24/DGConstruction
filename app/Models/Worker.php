<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

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
        'profile_image',
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

    public function getProfileImageUrlAttribute(): ?string
    {
        if (! $this->profile_image) {
            return null;
        }

        return asset('storage/' . ltrim($this->profile_image, '/'));
    }

    public function getAvatarAttribute(): HtmlString
    {
        $name = $this->full_name ?: 'Worker';
        $initials = strtoupper(substr($this->first_name ?? '', 0, 1) . substr($this->last_name ?? '', 0, 1));

        if ($initials === '') {
            $initials = strtoupper(substr(preg_replace('/\s+/', '', $name), 0, 2));
        }

        if ($this->profile_image_url) {
            return new HtmlString('<img class="worker-avatar-img" src="' . e($this->profile_image_url) . '" alt="' . e($name) . '">');
        }

        return new HtmlString('<div class="worker-avatar" aria-label="' . e($name) . '">' . e($initials ?: 'W') . '</div>');
    }
}