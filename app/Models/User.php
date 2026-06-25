<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'name',
        'full_name', // ADDED: Must be fillable if your database table or registration form uses it
        'email',
        'password_hash',
        'role',
        'contact_number',
        'is_active',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'user_id';
    }

    /**
     * Get the password for authentication.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Relationship: User has one Client profile
     */
    public function client()
    {
        return $this->hasOne(Client::class, 'user_id', 'user_id');
    }

    /**
     * Relationship: User (Engineer) has many Projects
     */
    public function engineeredProjects()
    {
        return $this->hasMany(Project::class, 'engineer_id', 'user_id');
    }

    /**
     * Relationship: User (Supervisor) supervises many Projects
     */
    public function supervisedProjects()
    {
        return $this->belongsToMany(Project::class, 'project_supervisors', 'supervisor_id', 'project_id', 'user_id', 'project_id')
            ->withPivot('assigned_date', 'is_active');
    }

    /**
     * Relationship: User has many attendance logs recorded by them
     */
    public function attendanceLogs()
    {
        return $this->hasMany(Attendance::class, 'recorded_by', 'user_id');
    }

    /**
     * Relationship: User has many submitted reports
     */
    public function submittedReports()
    {
        return $this->hasMany(Report::class, 'submitted_by', 'user_id');
    }

    /**
     * Get role badge color
     */
    public function getRoleBadgeAttribute()
    {
        return match($this->role) {
            'engineer' => 'danger',
            'site_supervisor' => 'warning',
            'client' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    /**
     * Get formatted role name
     */
    public function getRoleNameAttribute()
    {
        return match($this->role) {
            'engineer' => 'Engineer/Administrator',
            'site_supervisor' => 'Site Supervisor',
            'client' => 'Client',
            default => ucfirst($this->role),
        };
    }
}