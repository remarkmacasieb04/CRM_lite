<?php

namespace App\Http\Controllers;

use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\ClientActivity;
use App\Models\ClientNote;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $baseQuery = Client::query()
            ->ownedBy($user)
            ->withArchivedFilter(null);

        $recentlyUpdatedClients = Client::query()
            ->ownedBy($user)
            ->withArchivedFilter(null)
            ->select(['id', 'name', 'company', 'status', 'updated_at', 'follow_up_at'])
            ->recentFirst()
            ->limit(5)
            ->get();

        $recentNotes = ClientNote::query()
            ->whereBelongsTo($user)
            ->with([
                'client' => fn ($query) => $query->select(['id', 'user_id', 'name', 'status', 'archived_at']),
            ])
            ->latest()
            ->limit(6)
            ->get()
            ->filter(fn (ClientNote $note): bool => $note->client !== null && ! $note->client->isArchived())
            ->values();

        $overdueFollowUps = Client::query()
            ->ownedBy($user)
            ->withArchivedFilter(null)
            ->whereNotNull('follow_up_at')
            ->where('follow_up_at', '<', now()->startOfDay())
            ->select(['id', 'name', 'company', 'status', 'follow_up_at', 'last_contacted_at', 'email', 'phone'])
            ->orderBy('follow_up_at')
            ->limit(5)
            ->get();

        $upcomingFollowUps = Client::query()
            ->ownedBy($user)
            ->withArchivedFilter(null)
            ->whereNotNull('follow_up_at')
            ->whereBetween('follow_up_at', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
            ->select(['id', 'name', 'company', 'status', 'follow_up_at', 'last_contacted_at', 'email', 'phone'])
            ->orderBy('follow_up_at')
            ->limit(5)
            ->get();

        $recentActivities = ClientActivity::query()
            ->whereBelongsTo($user)
            ->with([
                'client' => fn ($query) => $query->select(['id', 'user_id', 'name', 'status', 'archived_at']),
            ])
            ->latest()
            ->limit(8)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalClients' => (clone $baseQuery)->count(),
                'activeClients' => (clone $baseQuery)->withStatus(ClientStatus::Active)->count(),
                'leads' => (clone $baseQuery)->withStatus(ClientStatus::Lead)->count(),
                'overdueFollowUps' => (clone $baseQuery)
                    ->whereNotNull('follow_up_at')
                    ->where('follow_up_at', '<', now()->startOfDay())
                    ->count(),
                'followUpsDueSoon' => (clone $baseQuery)
                    ->whereNotNull('follow_up_at')
                    ->whereBetween('follow_up_at', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
                    ->count(),
            ],
            'recentlyUpdatedClients' => $recentlyUpdatedClients->map(fn (Client $client): array => [
                'id' => $client->id,
                'name' => $client->name,
                'company' => $client->company,
                'status' => $client->status?->value,
                'status_label' => $client->status?->label(),
                'follow_up_at' => $client->follow_up_at?->toIso8601String(),
                'updated_at' => $client->updated_at?->toIso8601String(),
            ]),
            'recentNotes' => $recentNotes->map(fn (ClientNote $note): array => [
                'id' => $note->id,
                'content' => $note->content,
                'created_at' => $note->created_at?->toIso8601String(),
                'client' => [
                    'id' => $note->client?->id,
                    'name' => $note->client?->name,
                    'status' => $note->client?->status?->value,
                    'status_label' => $note->client?->status?->label(),
                ],
            ]),
            'followUpReminders' => [
                'overdue' => $overdueFollowUps->map(fn (Client $client): array => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'company' => $client->company,
                    'status' => $client->status?->value,
                    'status_label' => $client->status?->label(),
                    'follow_up_at' => $client->follow_up_at?->toIso8601String(),
                    'last_contacted_at' => $client->last_contacted_at?->toIso8601String(),
                    'email' => $client->email,
                    'phone' => $client->phone,
                ]),
                'upcoming' => $upcomingFollowUps->map(fn (Client $client): array => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'company' => $client->company,
                    'status' => $client->status?->value,
                    'status_label' => $client->status?->label(),
                    'follow_up_at' => $client->follow_up_at?->toIso8601String(),
                    'last_contacted_at' => $client->last_contacted_at?->toIso8601String(),
                    'email' => $client->email,
                    'phone' => $client->phone,
                ]),
            ],
            'recentActivity' => $recentActivities->map(fn (ClientActivity $activity): array => [
                'id' => $activity->id,
                'type' => $activity->type?->value,
                'type_label' => $activity->type?->label(),
                'description' => $activity->description,
                'created_at' => $activity->created_at?->toIso8601String(),
                'client' => [
                    'id' => $activity->client?->id,
                    'name' => $activity->client?->name ?? $activity->properties['client_name'] ?? 'Deleted client',
                    'status' => $activity->client?->status?->value,
                    'status_label' => $activity->client?->status?->label(),
                ],
            ]),
        ]);
    }
}
