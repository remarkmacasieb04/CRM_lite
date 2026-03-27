<?php

namespace App\Services\Infrastructure;

use Illuminate\Support\Facades\File;
use RuntimeException;

class SqliteBackupService
{
    /**
     * @return array{database_path: string, backup_path: string}
     */
    public function backup(?string $targetDirectory = null): array
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver !== 'sqlite') {
            throw new RuntimeException('SQLite backups are only available when your active database connection uses the sqlite driver.');
        }

        /** @var string|null $databasePath */
        $databasePath = config("database.connections.{$connection}.database");

        if ($databasePath === null || $databasePath === '' || $databasePath === ':memory:' || ! File::exists($databasePath)) {
            throw new RuntimeException('The configured SQLite database file could not be found.');
        }

        $backupDirectory = $targetDirectory ?: storage_path('app/backups/sqlite');
        File::ensureDirectoryExists($backupDirectory);

        $backupPath = rtrim($backupDirectory, DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR
            .'crm-lite-sqlite-backup-'.now()->format('Y-m-d-His').'.sqlite';

        File::copy($databasePath, $backupPath);

        return [
            'database_path' => $databasePath,
            'backup_path' => $backupPath,
        ];
    }
}
