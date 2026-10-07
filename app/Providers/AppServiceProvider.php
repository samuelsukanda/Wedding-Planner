<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Wedding;
use Carbon\Carbon;

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
        View::composer('layouts.app', function ($view) {
            $wedding = Wedding::current();
            $isSuper = (bool) auth()->user()?->is_superadmin;
            $daysLeft = $wedding ? max(0, (int) Carbon::now()->diffInDays($wedding->wedding_date, false)) : 0;

            $view->with('daysLeft', $daysLeft);
            $view->with('wedding', $wedding);
            $view->with('isSuper', $isSuper);
            // Superadmin tidak punya data pernikahan, jadi tampilkan nama akunnya.
            $view->with('brandName', $isSuper
                ? (auth()->user()->name ?? 'Superadmin')
                : ($wedding?->couple_name ?? 'Wedding Planner'));
            $view->with('roleLabel', $isSuper ? 'Superadmin' : 'Bride & Groom');
        });
    }
}
