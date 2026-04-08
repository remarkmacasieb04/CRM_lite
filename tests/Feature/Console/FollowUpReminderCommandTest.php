<?php

namespace Tests\Feature\Console;

use App\Mail\FollowUpReminderDigestMail;
use App\Models\Client;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FollowUpReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_follow_up_reminders_queues_digest_for_eligible_users(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'owner@example.com',
        ]);

        Client::factory()->for($user)->create([
            'name' => 'Follow Up Client',
            'follow_up_at' => CarbonImmutable::parse('2026-03-29'),
        ]);

        $this->artisan('crm:send-followup-reminders', [
            '--user' => $user->email,
            '--date' => '2026-03-30',
        ])->assertSuccessful();

        Mail::assertQueued(FollowUpReminderDigestMail::class, function (FollowUpReminderDigestMail $mail) use ($user): bool {
            return $mail->hasTo($user->email)
                && $mail->digest['total'] === 1;
        });

        $this->assertSame('2026-03-30', $user->fresh()->last_follow_up_digest_sent_at?->toDateString());
    }

    public function test_preview_follow_up_reminders_does_not_queue_mail(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'owner@example.com',
        ]);

        Client::factory()->for($user)->create([
            'name' => 'Preview Client',
            'follow_up_at' => CarbonImmutable::parse('2026-03-30'),
        ]);

        $this->artisan('crm:preview-followup-reminders', [
            '--user' => $user->email,
            '--date' => '2026-03-30',
        ])->assertSuccessful();

        Mail::assertNothingQueued();
        $this->assertNull($user->fresh()->last_follow_up_digest_sent_at);
    }

    public function test_users_with_disabled_reminders_are_skipped(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'receives_follow_up_reminders' => false,
        ]);

        Client::factory()->for($user)->create([
            'name' => 'Muted Client',
            'follow_up_at' => CarbonImmutable::parse('2026-03-29'),
        ]);

        $this->artisan('crm:send-followup-reminders', [
            '--user' => $user->email,
            '--date' => '2026-03-30',
        ])->assertSuccessful();

        Mail::assertNothingQueued();
        $this->assertNull($user->fresh()->last_follow_up_digest_sent_at);
    }
}
