<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Infrastructure\Logging\AuditLogRepository;
use App\Infrastructure\Logging\Eloquent\EloquentAuditLogRepository;
use App\Infrastructure\Logging\LogSanitizer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuditLogRepository::class, EloquentAuditLogRepository::class);
        $this->app->singleton(LogSanitizer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
