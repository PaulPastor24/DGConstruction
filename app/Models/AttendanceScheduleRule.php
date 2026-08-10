<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceScheduleRule extends Model
{
    protected $table = 'attendance_schedule_rules';

    protected $fillable = [
        'role',
        'start_time',
        'end_time',
        'break_start_time',
        'break_end_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function defaultsForRole(string $role): array
    {
        $normalizedRole = strtolower(trim($role));

        return match ($normalizedRole) {
            'staff', 'admin', 'administrator', 'engineer' => [
                'role' => $normalizedRole,
                'start_time' => '07:00:00',
                'end_time' => '15:00:00',
                'break_start_time' => '12:00:00',
                'break_end_time' => '13:00:00',
            ],
            'worker', 'supervisor', 'client' => [
                'role' => $normalizedRole,
                'start_time' => '07:00:00',
                'end_time' => '17:00:00',
                'break_start_time' => '12:00:00',
                'break_end_time' => '13:00:00',
            ],
            default => [
                'role' => $normalizedRole,
                'start_time' => '07:00:00',
                'end_time' => '17:00:00',
                'break_start_time' => '12:00:00',
                'break_end_time' => '13:00:00',
            ],
        };
    }
}