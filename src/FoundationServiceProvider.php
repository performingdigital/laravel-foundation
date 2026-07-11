<?php

declare(strict_types=1);

namespace Performing\LaravelFoundation;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Performing\LaravelFoundation\Console\Commands\DatabaseBackupCommand;
use Performing\LaravelFoundation\Console\Commands\DatabaseImportCommand;
use Performing\LaravelFoundation\Console\Commands\UsersCreateCommand;
use Performing\LaravelFoundation\Console\Commands\UsersCreateTokenCommand;
use Performing\LaravelFoundation\Console\Commands\UsersListCommand;
use Performing\LaravelFoundation\Console\Commands\UsersPasswordResetCommand;
use Performing\LaravelFoundation\Support\AppDefaults;

final class FoundationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/foundation.php', 'foundation');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/foundation.php' => config_path('foundation.php'),
            ], 'performing-foundation-config');

            $this->commands([
                DatabaseBackupCommand::class,
                DatabaseImportCommand::class,
                UsersCreateCommand::class,
                UsersCreateTokenCommand::class,
                UsersListCommand::class,
                UsersPasswordResetCommand::class,
            ]);
        }

        if (config('foundation.defaults.enabled', true)) {
            AppDefaults::configure();
        }

        Inertia::share('foundation', static fn(): array => config('foundation.frontend', []));
    }
}
