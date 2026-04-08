<?php

use App\Services\Infrastructure\SqliteBackupService;
use App\Services\Reminders\FollowUpReminderService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

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

Artisan::command('crm:preview-followup-reminders
    {--user= : Only preview a specific user email}
    {--date= : Override the digest date (YYYY-MM-DD)}
', function (FollowUpReminderService $reminderService): int {
    $date = $this->option('date')
        ? CarbonImmutable::parse((string) $this->option('date'))
        : today();

    $users = $reminderService->eligibleUsers($this->option('user'));

    if ($users->isEmpty()) {
        $this->comment('No eligible users found for follow-up reminders.');

        return self::SUCCESS;
    }

    foreach ($users as $user) {
        $digest = $reminderService->buildDigest($user, $date);

        $this->newLine();
        $this->info("Preview for {$user->email}");
        $this->line("Total reminders: {$digest['total']}");

        if ($digest['total'] === 0) {
            $this->comment('No overdue or upcoming follow-ups for this user.');

            continue;
        }

        foreach ([
            'overdue' => 'Overdue',
            'due_today' => 'Due today',
            'upcoming' => 'Upcoming',
        ] as $groupKey => $label) {
            $items = $digest[$groupKey];

            if ($items === []) {
                continue;
            }

            $this->line("{$label}:");

            foreach ($items as $item) {
                $company = $item['company'] ? " ({$item['company']})" : '';
                $followUpDate = $item['follow_up_at'] ?? 'no date';

                $this->line(" - {$item['name']}{$company} due {$followUpDate}");
            }
        }
    }

    return self::SUCCESS;
})->purpose('Preview follow-up reminder digests without queueing any email');

Artisan::command('crm:send-followup-reminders
    {--user= : Only process a specific user email}
    {--date= : Override the digest date (YYYY-MM-DD)}
    {--force : Send even if a digest was already sent today}
    {--pretend : Preview digests without queuing any email}
', function (FollowUpReminderService $reminderService): int {
    $date = $this->option('date')
        ? CarbonImmutable::parse((string) $this->option('date'))
        : today();

    $users = $reminderService->eligibleUsers($this->option('user'));

    if ($users->isEmpty()) {
        $this->comment('No eligible users found for follow-up reminders.');

        return self::SUCCESS;
    }

    $sent = 0;
    $skipped = 0;

    foreach ($users as $user) {
        $digest = $reminderService->buildDigest($user, $date);

        if ($this->option('pretend')) {
            $this->line("Preview for {$user->email}: {$digest['total']} reminders");

            continue;
        }

        $result = $reminderService->sendDigest($user, $date, (bool) $this->option('force'));

        if ($result['sent']) {
            $sent++;
            $this->info("Queued reminder digest for {$user->email} ({$result['digest']['total']} reminders).");

            continue;
        }

        $skipped++;
        $this->comment("Skipped {$user->email}: {$result['skipped_reason']}.");
    }

    $this->line("Sent: {$sent}");
    $this->line("Skipped: {$skipped}");

    return self::SUCCESS;
})->purpose('Queue daily follow-up reminder digests for eligible users');

Schedule::command('crm:send-followup-reminders')->dailyAt('08:00');
