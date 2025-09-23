<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\View\Composers\LanguageComposer;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Snappy PDF service
        $this->app->register(\Barryvdh\Snappy\ServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the language composer for all views
        View::composer('*', LanguageComposer::class);
        if (config('app.env') === 'staging' || config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
