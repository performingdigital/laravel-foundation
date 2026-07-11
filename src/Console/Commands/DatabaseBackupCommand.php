<?php

declare(strict_types=1);

namespace Performing\LaravelFoundation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

final class DatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup
        {path? : Backup destination}
        {--connection= : Database connection}
        {--database= : Override the database name}';

    protected $description = 'Create a MySQL database backup';

    public function handle(Filesystem $files): int
    {
        $configuration = $this->configuration();

        if ($configuration === null) {
            return self::FAILURE;
        }

        $database = (string) $configuration['database'];
        $pathArgument = $this->argument('path');
        $path = is_string($pathArgument) && $pathArgument !== ''
            ? $pathArgument
            : storage_path('app/backups/'.$database.'-'.now()->format('Y-m-d_His').'.sql');

        $files->ensureDirectoryExists(dirname($path));

        $mysqldump = escapeshellcmd((string) config('foundation.database.binaries.mysqldump', 'mysqldump'));
        $password = escapeshellarg((string) ($configuration['password'] ?? ''));
        $command = implode(' ', [
            "MYSQL_PWD={$password}",
            $mysqldump,
            '--host='.escapeshellarg((string) ($configuration['host'] ?? '127.0.0.1')),
            '--port='.escapeshellarg((string) ($configuration['port'] ?? '3306')),
            '--user='.escapeshellarg((string) ($configuration['username'] ?? 'root')),
            '--single-transaction',
            '--routines',
            '--no-tablespaces',
            escapeshellarg($database),
            '>',
            escapeshellarg($path),
        ]);

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $files->delete($path);
            $this->error('Database backup failed.');

            return self::FAILURE;
        }

        $this->info("Backup created: {$path}");

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
            $this->error('The db:backup command only supports MySQL and MariaDB.');

            return null;
        }

        $database = $this->option('database');

        if (is_string($database) && $database !== '') {
            $configuration['database'] = $database;
        }

        return $configuration;
    }
}
