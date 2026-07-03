<?php

namespace App\Providers;

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
        View::composer('layouts.client', function ($view) {
            $user = Auth::user();
            $notifications = collect();
            $notificationCount = 0;

            if ($user && $user->client) {
                $notifications = collect([
                    [
                        'title' => 'New milestone scheduled',
                        'message' => 'A new milestone has been added to your active project timeline.',
                        'time' => Carbon::now()->subHours(2)->diffForHumans(),
                    ],
                    [
                        'title' => 'Report uploaded',
                        'message' => 'Engineering report has been submitted for review.',
                        'time' => Carbon::now()->subDay()->diffForHumans(),
                    ],
                ]);
                $notificationCount = $notifications->count();
            }

            $view->with([
                'clientNotifications' => $notifications,
                'clientNotificationCount' => $notificationCount,
            ]);
        });

<<<<<<< HEAD
        // 2. FORCE HTTPS OVER TUNNEL PROXIES (Fixed CSS & Login Blocks on Phone)
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }
=======
        // Share unread supervisor notification count with supervisor layout/topbar
        View::composer('layouts.supervisor', function ($view) {
            $user = Auth::user();
            $unread = 0;
            if ($user) {
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('supervisor_notifications')) {
                        $unread = SupervisorNotification::where('supervisor_id', $user->user_id)
                            ->where('is_read', false)
                            ->count();
                    }
                } catch (\Throwable $e) {
                    $unread = 0;
                }
            }
            $view->with('supervisorUnreadCount', $unread);
        });
>>>>>>> origin/araymopakak
    }
}