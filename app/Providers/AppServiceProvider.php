<?php

namespace App\Providers;

use App\Services\EscposPrinterService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EscposPrinterService::class, function () {
            return new EscposPrinterService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        mb_internal_encoding('UTF-8');
        mb_http_output('UTF-8');
    }
}
