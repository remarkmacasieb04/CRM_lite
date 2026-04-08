<?php

namespace App\Services\Reminders;

use App\Mail\FollowUpReminderDigestMail;
use App\Models\Client;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class FollowUpReminderService
{
    /**
     * @return Collection<int, User>
     */
    public function eligibleUsers(?string $email = null): Collection
    {
        return User::query()
            ->whereNotNull('email_verified_at')
            ->where('receives_follow_up_reminders', true)
            ->when($email !== null && $email !== '', fn ($query) => $query->where('email', $email))
            ->orderBy('email')
            ->get();
    }

    /**
     * @return array{
     *     total: int,
     *     overdue: array<int, array<string, mixed>>,
     *     due_today: array<int, array<string, mixed>>,
     *     upcoming: array<int, array<string, mixed>>
     * }
     */
    public function buildDigest(User $user, CarbonInterface $date): array
    {
        $baseQuery = Client::query()
            ->ownedBy($user)
            ->withArchivedFilter(null)
            ->whereNotNull('follow_up_at');

        $overdue = (clone $baseQuery)
            ->where('follow_up_at', '<', $date->startOfDay())
            ->orderBy('follow_up_at')
            ->get();

        $dueToday = (clone $baseQuery)
            ->whereBetween('follow_up_at', [$date->startOfDay(), $date->endOfDay()])
            ->orderBy('follow_up_at')
            ->get();

        $upcoming = (clone $baseQuery)
            ->whereBetween('follow_up_at', [$date->addDay()->startOfDay(), $date->addDays(7)->endOfDay()])
            ->orderBy('follow_up_at')
            ->get();

        return [
            'total' => $overdue->count() + $dueToday->count() + $upcoming->count(),
            'overdue' => $overdue->map(fn (Client $client): array => $this->reminderPayload($client))->all(),
            'due_today' => $dueToday->map(fn (Client $client): array => $this->reminderPayload($client))->all(),
            'upcoming' => $upcoming->map(fn (Client $client): array => $this->reminderPayload($client))->all(),
        ];
    }

    /**
     * @return array{sent: bool, skipped_reason: ?string, digest: array<string, mixed>}
     */
    public function sendDigest(User $user, CarbonInterface $date, bool $force = false): array
    {
        if (! $force && $user->last_follow_up_digest_sent_at?->toDateString() === $date->toDateString()) {
            return [
                'sent' => false,
                'skipped_reason' => 'already_sent_today',
                'digest' => ['total' => 0, 'overdue' => [], 'due_today' => [], 'upcoming' => []],
            ];
        }

        $digest = $this->buildDigest($user, $date);

        if (($digest['total'] ?? 0) === 0) {
            return [
                'sent' => false,
                'skipped_reason' => 'no_due_follow_ups',
                'digest' => $digest,
            ];
        }

        Mail::to($user->email)->queue(new FollowUpReminderDigestMail(
            $user,
            $digest,
            $date->toFormattedDayDateString(),
        ));

        $user->forceFill([
            'last_follow_up_digest_sent_at' => $date->toDateString(),
        ])->save();

        return [
            'sent' => true,
            'skipped_reason' => null,
            'digest' => $digest,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function reminderPayload(Client $client): array
    {
        return [
            'id' => $client->id,
            'name' => $client->name,
            'company' => $client->company,
            'email' => $client->email,
            'phone' => $client->phone,
            'status' => $client->status?->value,
            'status_label' => $client->status?->label(),
            'follow_up_at' => $client->follow_up_at?->toDateString(),
            'last_contacted_at' => $client->last_contacted_at?->toDateString(),
            'view_url' => route('clients.show', $client),
        ];
    }
}
