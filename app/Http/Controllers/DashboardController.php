<?php

namespace App\Http\Controllers;

use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\ClientActivity;
use App\Models\ClientCommunication;
use App\Models\ClientNote;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $workspace = $request->attributes->get('currentWorkspace');
        $baseQuery = Client::query()
            ->forWorkspace($workspace)
            ->withArchivedFilter(null);

        $recentlyUpdatedClients = Client::query()
            ->forWorkspace($workspace)
            ->withArchivedFilter(null)
            ->select(['id', 'name', 'company', 'status', 'updated_at', 'follow_up_at'])
            ->recentFirst()
            ->limit(5)
            ->get();

        $recentNotes = ClientNote::query()
            ->whereBelongsTo($workspace)
            ->with([
                'client' => fn ($query) => $query->select(['id', 'workspace_id', 'user_id', 'name', 'status', 'archived_at']),
            ])
            ->latest()
            ->limit(6)
            ->get()
            ->filter(fn (ClientNote $note): bool => $note->client !== null && ! $note->client->isArchived())
            ->values();

        $overdueFollowUps = Client::query()
            ->forWorkspace($workspace)
            ->withArchivedFilter(null)
            ->whereNotNull('follow_up_at')
            ->where('follow_up_at', '<', now()->startOfDay())
            ->select(['id', 'name', 'company', 'status', 'follow_up_at', 'last_contacted_at', 'email', 'phone'])
            ->orderBy('follow_up_at')
            ->limit(5)
            ->get();

        $upcomingFollowUps = Client::query()
            ->forWorkspace($workspace)
            ->withArchivedFilter(null)
            ->whereNotNull('follow_up_at')
            ->whereBetween('follow_up_at', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
            ->select(['id', 'name', 'company', 'status', 'follow_up_at', 'last_contacted_at', 'email', 'phone'])
            ->orderBy('follow_up_at')
            ->limit(5)
            ->get();

        $recentActivities = ClientActivity::query()
            ->whereBelongsTo($workspace)
            ->with([
                'client' => fn ($query) => $query->select(['id', 'workspace_id', 'user_id', 'name', 'status', 'archived_at']),
            ])
            ->latest()
            ->limit(8)
            ->get();

        $upcomingTasks = Task::query()
            ->forWorkspace($workspace)
            ->with(['client:id,name', 'assignee:id,name,email', 'creator:id,name,email'])
            ->openOnly()
            ->orderBy('due_at')
            ->latest('id')
            ->limit(6)
            ->get();

        $recentCommunications = ClientCommunication::query()
            ->whereBelongsTo($workspace)
            ->with(['client:id,name', 'user:id,name,email'])
            ->latest('happened_at')
            ->limit(6)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalClients' => (clone $baseQuery)->count(),
                'activeClients' => (clone $baseQuery)->withStatus(ClientStatus::Active)->count(),
                'leads' => (clone $baseQuery)->withStatus(ClientStatus::Lead)->count(),
                'openTasks' => Task::query()->forWorkspace($workspace)->openOnly()->count(),
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
            'upcomingTasks' => $upcomingTasks->map(fn (Task $task): array => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status?->value,
                'status_label' => $task->status?->label(),
                'priority' => $task->priority?->value,
                'priority_label' => $task->priority?->label(),
                'due_at' => $task->due_at?->toIso8601String(),
                'completed_at' => $task->completed_at?->toIso8601String(),
                'client' => [
                    'id' => $task->client?->id,
                    'name' => $task->client?->name,
                ],
                'assignee' => [
                    'id' => $task->assignee?->id,
                    'name' => $task->assignee?->name,
                    'email' => $task->assignee?->email,
                ],
                'creator' => [
                    'id' => $task->creator?->id,
                    'name' => $task->creator?->name,
                    'email' => $task->creator?->email,
                ],
            ]),
            'recentCommunications' => $recentCommunications->map(fn (ClientCommunication $communication): array => [
                'id' => $communication->id,
                'channel' => $communication->channel?->value,
                'channel_label' => $communication->channel?->label(),
                'direction' => $communication->direction?->value,
                'direction_label' => $communication->direction?->label(),
                'subject' => $communication->subject,
                'summary' => $communication->summary,
                'happened_at' => $communication->happened_at?->toIso8601String(),
                'author' => [
                    'id' => $communication->user?->id,
                    'name' => $communication->user?->name,
                    'email' => $communication->user?->email,
                ],
                'client' => [
                    'id' => $communication->client?->id,
                    'name' => $communication->client?->name,
                ],
            ]),
        ]);
    }
}
