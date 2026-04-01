<?php

namespace App\Providers;

use App\Models\Kehadiran;
use App\Models\UserNotification;
use App\Observers\KehadiranObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
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
        Kehadiran::observe(KehadiranObserver::class);

        View::composer('components.layouts.dashboard-shell', function ($view): void {
            if (! Auth::check()) {
                return;
            }

            $user = Auth::user();
            $bellNotifications = UserNotification::query()
                ->where('user_id', $user->id)
                ->with('sender:id,name,email')
                ->latest()
                ->limit(25)
                ->get();

            $bellUnreadCount = UserNotification::query()
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();

            $view->with(compact('bellNotifications', 'bellUnreadCount'));
        });
    }
}
