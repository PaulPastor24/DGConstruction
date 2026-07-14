<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\ClientNotification;
use App\Models\SupervisorNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class NotificationService
{
    /**
     * Roles that are treated as "admin" for the purposes of the admin
     * notifications panel (this codebase uses the engineer role as the
     * primary admin account, with 'admin'/'administrator' supported too).
     */
    private const ADMIN_ROLES = ['engineer', 'admin', 'administrator'];

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

    public static function notifyClient(int $clientId, array $data): ?ClientNotification
    {
        try {
            $payload = [
                'client_id' => $clientId,
                'type' => $data['type'] ?? 'system',
                'title' => $data['title'] ?? 'Notification',
                'message' => $data['message'] ?? null,
                'data' => $data['data'] ?? null,
                'related_id' => $data['related_id'] ?? null,
                'related_type' => $data['related_type'] ?? null,
                'is_read' => $data['is_read'] ?? false,
            ];

            $notification = ClientNotification::create($payload);
            Log::info('Client notification created', ['client_id' => $clientId, 'notification_id' => $notification->id, 'title' => $notification->title]);
            return $notification;
        } catch (\Throwable $e) {
            Log::error('Failed to create client notification: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send a notification to a single admin (engineer/admin/administrator) user.
     */
    public static function notifyAdmin(int $adminId, array $data): ?AdminNotification
    {
        try {
            if (!Schema::hasTable('admin_notifications')) {
                return null;
            }

            $payload = [
                'admin_id' => $adminId,
                'type' => $data['type'] ?? 'system',
                'title' => $data['title'] ?? 'Notification',
                'message' => $data['message'] ?? null,
                'data' => $data['data'] ?? null,
                'related_id' => $data['related_id'] ?? null,
                'related_type' => $data['related_type'] ?? null,
                'is_read' => $data['is_read'] ?? false,
            ];

            return AdminNotification::create($payload);
        } catch (\Throwable $e) {
            Log::error('Failed to create admin notification: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Broadcast a notification to every active admin-level user
     * (engineer/admin/administrator roles), creating one row per recipient
     * so each admin can track their own read state.
     */
    public static function notifyAdmins(array $data): int
    {
        try {
            if (!Schema::hasTable('admin_notifications')) {
                return 0;
            }

            // Perform a case-insensitive role match to avoid missing admins
            $lowerRoles = array_map(fn($r) => strtolower($r), self::ADMIN_ROLES);

            $adminsQuery = User::query()->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(role)'), $lowerRoles);

            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_active')) {
                $adminsQuery->where('is_active', 1);
            }

            $admins = $adminsQuery->pluck('user_id');

            // Log for debugging when notifications fail to find recipients
            \Illuminate\Support\Facades\Log::info('notifyAdmins: found admin recipients', ['count' => $admins->count(), 'roles' => $lowerRoles]);

            $sent = 0;
            foreach ($admins as $adminId) {
                try {
                    if (self::notifyAdmin((int) $adminId, $data)) {
                        $sent++;
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('notifyAdmins: failed to create notification for admin ' . $adminId . ' - ' . $e->getMessage());
                }
            }

            return $sent;
        } catch (\Throwable $e) {
            Log::error('Failed to broadcast admin notification: ' . $e->getMessage());
            return 0;
        }
    }
}