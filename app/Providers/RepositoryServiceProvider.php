<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\Contracts\FormRepositoryInterface;
use App\Repositories\Contracts\OrganizerRepositoryInterface;
use App\Repositories\Contracts\ParticipantRepositoryInterface;
use App\Repositories\Contracts\SnapshotRepositoryInterface;
use App\Repositories\Contracts\TokenRepositoryInterface;
use App\Repositories\Contracts\TelemetryRepositoryInterface;

use App\Repositories\Eloquent\FormRepository;
use App\Repositories\Eloquent\OrganizerRepository;
use App\Repositories\Eloquent\ParticipantRepository;
use App\Repositories\Eloquent\SnapshotRepository;
use App\Repositories\Eloquent\TokenRepository;
use App\Repositories\Eloquent\TelemetryRepository;

use App\Services\Scalarizer\ScalarizerInterface;
use App\Services\Scalarizer\ScalarizerService;

final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Core
        $this->app->bind(OrganizerRepositoryInterface::class, OrganizerRepository::class);
        $this->app->bind(FormRepositoryInterface::class, FormRepository::class);
        $this->app->bind(ParticipantRepositoryInterface::class, ParticipantRepository::class);
        $this->app->bind(SnapshotRepositoryInterface::class, SnapshotRepository::class);

        // Cross-cutting
        $this->app->bind(TokenRepositoryInterface::class, TokenRepository::class);
        $this->app->bind(TelemetryRepositoryInterface::class, TelemetryRepository::class);

		$this->app->bind(ScalarizerInterface::class, ScalarizerService::class);
    }

    public function boot(): void
    {
        // いまは特になし（必要になったらここで設定）
    }
}
