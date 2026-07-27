<?php

namespace App\Providers;

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
        \Illuminate\Support\Facades\View::share('e', function($value) {
            return e($value);
        });

        // Share site settings to all views as $sitePayload
        try {
            $siteSettings = app(\App\Services\SiteSettings::class);
            $sitePayload = $siteSettings->site();
            \Illuminate\Support\Facades\View::share('sitePayload', $sitePayload);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\View::share('sitePayload', []);
        }
    }
}
