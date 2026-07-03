<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SupervisorNotification extends Model
{
    use HasFactory;

    protected $table = 'supervisor_notifications';
    protected $fillable = [
        'supervisor_id', 'type', 'title', 'message', 'data', 'related_id', 'related_type', 'is_read', 'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id', 'user_id');
    }
}
