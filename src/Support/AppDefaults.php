<?php

declare(strict_types=1);

namespace Performing\LaravelFoundation\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;

final class AppDefaults
{
    public static function configure(): void
    {
        if (config('foundation.defaults.immutable_dates', true)) {
            Date::use(CarbonImmutable::class);
        }

        if (config('foundation.defaults.prohibit_destructive_commands', true)) {
            DB::prohibitDestructiveCommands(app()->isProduction());
        }

        if (app()->isProduction() && config('foundation.defaults.force_https_in_production', true)) {
            URL::forceScheme('https');
        }

        if (config('foundation.defaults.passwords.enabled', true)) {
            Password::defaults(static fn (): ?Password => app()->isProduction()
                ? self::productionPasswordRule()
                : null,
            );
        }
    }

    private static function productionPasswordRule(): Password
    {
        /** @var array{min?: int, mixed_case?: bool, letters?: bool, numbers?: bool, symbols?: bool, uncompromised?: bool} $config */
        $config = config('foundation.defaults.passwords', []);

        $rule = Password::min($config['min'] ?? 12);

        if ($config['mixed_case'] ?? true) {
            $rule->mixedCase();
        }

        if ($config['letters'] ?? true) {
            $rule->letters();
        }

        if ($config['numbers'] ?? true) {
            $rule->numbers();
        }

        if ($config['symbols'] ?? true) {
            $rule->symbols();
        }

        if ($config['uncompromised'] ?? true) {
            $rule->uncompromised();
        }

        return $rule;
    }
}
