<?php

namespace App\Providers;

use App\Models\ClientNotification;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL; // ◄ Crucial import added for the secure URL handler
use Illuminate\Support\ServiceProvider;
use App\Models\SupervisorNotification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Existing Layout View Composers
        View::composer(['layouts.client', 'client.*', 'client.partials.*'], function ($view) {
            $user = Auth::user();
            $notifications = collect();
            $notificationCount = 0;
            $unreadCount = 0;

            if ($user && $user->client) {
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('client_notifications')) {
                        $notifications = ClientNotification::query()
                            ->where('client_id', $user->client->client_id)
                            ->latest('created_at')
                            ->limit(3)
                            ->get();

                        $notificationCount = $notifications->count();
                        $unreadCount = ClientNotification::query()
                            ->where('client_id', $user->client->client_id)
                            ->where('is_read', false)
                            ->count('*');
                    }
                } catch (\Throwable $e) {
                    $notifications = collect();
                    $notificationCount = 0;
                    $unreadCount = 0;
                }
            }

            $hasNotifications = ($unreadCount > 0) || $notifications->isNotEmpty();

            $view->with([
                'clientNotifications' => $notifications,
                'clientNotificationCount' => $notificationCount,
                'clientUnreadCount' => $unreadCount,
                'clientHasNotifications' => $hasNotifications,
            ]);
        });

        // Share unread supervisor notification count with supervisor layout/topbar
        View::composer('layouts.supervisor', function ($view) {
            $user = Auth::user();
            $unread = 0;
            if ($user) {
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('supervisor_notifications')) {
                        $unread = SupervisorNotification::query()
                            ->where('supervisor_id', $user->user_id)
                            ->where('is_read', false)
                            ->count('*');
                    }
                } catch (\Throwable $e) {
                    $unread = 0;
                }
            }
            $view->with('supervisorUnreadCount', $unread);
        });
    }
}