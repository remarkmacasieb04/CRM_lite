<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class SqliteBackupCommandTest extends TestCase
{
    public function test_sqlite_backup_command_copies_the_database_file(): void
    {
        $databasePath = storage_path('framework/testing/'.Str::uuid().'.sqlite');
        $backupDirectory = storage_path('framework/testing/backups-'.Str::uuid());

        File::ensureDirectoryExists(dirname($databasePath));
        File::put($databasePath, 'sqlite-backup-content');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.driver' => 'sqlite',
            'database.connections.sqlite.database' => $databasePath,
        ]);

        $this->artisan('crm:backup:sqlite', [
            '--path' => $backupDirectory,
        ])->assertSuccessful();

        $backupFiles = File::files($backupDirectory);

        $this->assertCount(1, $backupFiles);
        $this->assertSame('sqlite-backup-content', File::get($backupFiles[0]->getPathname()));

        File::delete($databasePath);
        File::deleteDirectory($backupDirectory);
    }

    public function test_sqlite_backup_command_fails_for_non_sqlite_connections(): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.driver' => 'mysql',
        ]);

        $this->artisan('crm:backup:sqlite')
            ->assertFailed();
    }
}
