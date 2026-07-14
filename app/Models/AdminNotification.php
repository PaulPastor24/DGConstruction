<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AdminNotification extends Model
{
    use HasFactory;

    protected $table = 'admin_notifications';

    protected $fillable = [
        'admin_id',
        'type',
        'title',
        'message',
        'data',
        'related_id',
        'related_type',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id', 'user_id');
    }

    /**
     * Human friendly badge label derived from the notification type.
     * Used by the admin alerts UI (severity/category badge).
     */
    public function getSeverityAttribute(): string
    {
        return match ($this->type) {
            'project', 'assignment' => 'assignment',
            'alert', 'critical', 'danger' => 'danger',
            'system', 'user' => 'system',
            'reminder' => 'reminder',
            default => 'info',
        };
    }

    /**
     * Project / entity name this notification relates to, when available.
     */
    public function getSourceAttribute(): ?string
    {
        $data = $this->data ?? [];

        return $data['project_name'] ?? $data['source'] ?? null;
    }

    /**
     * Recipient label for display purposes (which role this alert concerns).
     */
    public function getRecipientAttribute(): ?string
    {
        $data = $this->data ?? [];

        return $data['recipient'] ?? null;
    }
}