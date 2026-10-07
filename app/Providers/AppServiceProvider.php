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
            $daysLeft = $wedding ? max(0, (int) Carbon::now()->diffInDays($wedding->wedding_date, false)) : 0;
            $view->with('daysLeft', $daysLeft);
            $view->with('wedding', $wedding);
        });
    }
}
