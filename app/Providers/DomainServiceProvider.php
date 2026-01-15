<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\UseCases\Assemblers\TokenAssembler;
use App\UseCases\Assemblers\FormAssembler;
use App\UseCases\Assemblers\SnapshotAssembler;
use App\UseCases\Assemblers\TelemetryAssembler;

final class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Assemblers（依存があるものは自動解決）
        $this->app->singleton(TokenAssembler::class);
        $this->app->singleton(FormAssembler::class);
        $this->app->singleton(SnapshotAssembler::class);
        $this->app->singleton(TelemetryAssembler::class);
    }
}
