<?php

use App\Services\Infrastructure\SqliteBackupService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('crm:backup:sqlite {--path= : Custom folder for the backup file}', function (SqliteBackupService $backupService): int {
    try {
        $result = $backupService->backup($this->option('path'));
    } catch (RuntimeException $exception) {
        $this->error($exception->getMessage());

        return self::FAILURE;
    }

    $this->info('SQLite backup created successfully.');
    $this->line('Database: '.$result['database_path']);
    $this->line('Backup: '.$result['backup_path']);

    return self::SUCCESS;
})->purpose('Create a timestamped backup of the active SQLite database file');
