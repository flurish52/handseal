<?php

namespace App\Providers;

use App\Models\Business;
use App\Models\Certificate;
use App\Observers\BusinessObserver;
use App\Observers\CertificateObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);

        if ($this->app->environment('local') && str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
        // AppServiceProvider::boot()
        Business::observe(BusinessObserver::class);
        Certificate::observe(CertificateObserver::class);
    }
}
