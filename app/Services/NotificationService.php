<?php

namespace App\Services;

use App\Models\SupervisorNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public static function notifySupervisor(int $supervisorId, array $data): ?SupervisorNotification
    {
        try {
            $payload = [
                'supervisor_id' => $supervisorId,
                'type' => $data['type'] ?? 'system',
                'title' => $data['title'] ?? 'Notification',
                'message' => $data['message'] ?? null,
                'data' => $data['data'] ?? null,
                'related_id' => $data['related_id'] ?? null,
                'related_type' => $data['related_type'] ?? null,
                'is_read' => $data['is_read'] ?? false,
            ];

            return SupervisorNotification::create($payload);
        } catch (\Throwable $e) {
            Log::error('Failed to create supervisor notification: ' . $e->getMessage());
            return null;
        }
    }
}
