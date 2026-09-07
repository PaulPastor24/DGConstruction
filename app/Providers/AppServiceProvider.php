<?php

namespace App\Providers;

use App\Models\AdminNotification;
use App\Models\ClientNotification;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Report;
use App\Models\SupervisorNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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

        // Share unread admin notification count with admin layout/topbar
        View::composer('layouts.admin', function ($view) {
            $user = Auth::user();
            $unread = 0;
            if ($user) {
                try {
                    $hasAdminNotifications = Cache::rememberForever('schema.admin_notifications.exists', function () {
                        return \Illuminate\Support\Facades\Schema::hasTable('admin_notifications');
                    });

                    if ($hasAdminNotifications) {
                        $unread = Cache::remember(
                            "admin_notifications.unread.{$user->user_id}",
                            now()->addSeconds(10),
                            fn () => AdminNotification::query()
                                ->where('admin_id', $user->user_id)
                                ->where('is_read', false)
                                ->count()
                        );
                    }
                } catch (\Throwable $e) {
                    $unread = 0;
                }
            }
            $view->with('adminUnreadCount', $unread);
        });

        // Use the forwarded public host for tunnel requests; leave LAN requests local.
        $requestHost = (string) request()->getHost();
        $forwardedHost = (string) request()->header('X-Forwarded-Host');
        $publicHost = $forwardedHost ?: $requestHost;
        $isPublicTunnelHost = str_contains($publicHost, 'ngrok-free.dev')
            || str_contains($publicHost, 'asse.devtunnels.ms');

        if ($isPublicTunnelHost) {
            URL::forceRootUrl('https://'.$publicHost);
            URL::forceScheme('https');
            
            // This forces Spatie's package to trust the active public link.
            config(['passkeys.relying_party.id' => $publicHost]);
            config(['passkeys.relying_party.name' => 'D&G Construction Inc.']);
        }
    }
}