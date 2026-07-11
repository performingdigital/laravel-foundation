<?php

declare(strict_types=1);

namespace Performing\LaravelFoundation\Console\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;

final class DatabaseImportCommand extends Command
{
    protected $signature = 'db:import
        {path : Path to the SQL dump}
        {--connection= : Database connection}
        {--database= : Override the database name}
        {--force : Skip confirmation}';

    protected $description = 'Import a MySQL database dump';

    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('Database imports are disabled in production.');

            return self::FAILURE;
        }

        $path = $this->argument('path');

        if (! is_file($path)) {
            $this->error('Database dump not found.');

            return self::FAILURE;
        }

        $configuration = $this->configuration();

        if ($configuration === null) {
            return self::FAILURE;
        }

        $database = (string) $configuration['database'];

        if (! $this->option('force') && ! confirm("Replace database [{$database}]?")) {
            $this->warn('Import cancelled.');

            return self::FAILURE;
        }

        $mysql = escapeshellcmd((string) config('foundation.database.binaries.mysql', 'mysql'));
        $connection = $this->mysqlConnectionArguments($configuration);
        $escapedDatabase = str_replace('`', '``', $database);
        $password = escapeshellarg((string) ($configuration['password'] ?? ''));

        exec("MYSQL_PWD={$password} {$mysql} {$connection} --execute=".escapeshellarg("CREATE DATABASE IF NOT EXISTS `{$escapedDatabase}`"), $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error('Unable to create the target database.');

            return self::FAILURE;
        }

        exec("MYSQL_PWD={$password} {$mysql} {$connection} ".escapeshellarg($database).' < '.escapeshellarg($path), $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error('Database import failed.');

            return self::FAILURE;
        }

        $this->info("Database [{$database}] imported successfully.");

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function configuration(): ?array
    {
        $connection = $this->option('connection') ?: config('database.default');
        $configuration = config("database.connections.{$connection}");

        if (! is_array($configuration)) {
            $this->error("Database connection [{$connection}] is not configured.");

            return null;
        }

        if (! in_array($configuration['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            $this->error('The db:import command only supports MySQL and MariaDB.');

            return null;
        }

        $database = $this->option('database');

        if (is_string($database) && $database !== '') {
            $configuration['database'] = $database;
        }

        return $configuration;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function mysqlConnectionArguments(array $configuration): string
    {
        return implode(' ', [
            '--host='.escapeshellarg((string) ($configuration['host'] ?? '127.0.0.1')),
            '--port='.escapeshellarg((string) ($configuration['port'] ?? '3306')),
            '--user='.escapeshellarg((string) ($configuration['username'] ?? 'root')),
        ]);
    }
}
