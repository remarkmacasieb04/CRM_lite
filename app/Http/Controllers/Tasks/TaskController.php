<?php

namespace App\Http\Controllers\Tasks;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Models\Client;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Task::class);

        /** @var Workspace $workspace */
        $workspace = $request->attributes->get('currentWorkspace');

        $filters = [
            'status' => $request->string('status')->value() ?: null,
            'priority' => $request->string('priority')->value() ?: null,
            'assigned_to' => $request->integer('assigned_to') ?: null,
            'client_id' => $request->integer('client_id') ?: null,
        ];

        $tasks = Task::query()
            ->forWorkspace($workspace)
            ->with([
                'client:id,name',
                'assignee:id,name,email',
                'creator:id,name,email',
            ])
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['priority'], fn (Builder $query, string $priority) => $query->where('priority', $priority))
            ->when($filters['assigned_to'], fn (Builder $query, int $assignedTo) => $query->where('assigned_to_user_id', $assignedTo))
            ->when($filters['client_id'], fn (Builder $query, int $clientId) => $query->where('client_id', $clientId))
            ->orderBy('completed_at')
            ->orderBy('due_at')
            ->latest('id')
            ->get()
            ->map(fn (Task $task): array => $this->taskPayload($task))
            ->all();

        $clients = Client::query()
            ->forWorkspace($workspace)
            ->withArchivedFilter(null)
            ->orderBy('name')
            ->get(['id', 'name']);

        $members = $workspace->members()
            ->orderBy('name')
            ->get(['users.id', 'users.name', 'users.email']);

        return Inertia::render('tasks/Index', [
            'tasks' => $tasks,
            'filters' => $filters,
            'statusOptions' => TaskStatus::options(),
            'priorityOptions' => TaskPriority::options(),
            'clientOptions' => $clients->map(fn (Client $client): array => [
                'id' => $client->id,
                'name' => $client->name,
            ])->all(),
            'memberOptions' => $members->map(fn ($member): array => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
            ])->all(),
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        /** @var Workspace $workspace */
        $workspace = $request->attributes->get('currentWorkspace');

        $task = Task::query()->create([
            ...$request->validated(),
            'workspace_id' => $workspace->id,
            'created_by_user_id' => $request->user()->id,
            'completed_at' => $request->validated('status') === TaskStatus::Done->value ? now() : null,
        ]);

        return back()->with('success', "Task \"{$task->title}\" created.");
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $task->fill($request->validated());
        $task->completed_at = $task->status === TaskStatus::Done
            ? ($task->completed_at ?? now())
            : null;
        $task->save();

        return back()->with('success', "Task \"{$task->title}\" updated.");
    }

    public function toggle(Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->forceFill([
            'status' => $task->status === TaskStatus::Done ? TaskStatus::Todo : TaskStatus::Done,
            'completed_at' => $task->status === TaskStatus::Done ? null : now(),
        ])->save();

        return back()->with('success', "Task \"{$task->title}\" updated.");
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $title = $task->title;
        $task->delete();

        return back()->with('success', "Task \"{$title}\" deleted.");
    }

    /**
     * @return array<string, mixed>
     */
    private function taskPayload(Task $task): array
    {
        return [
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
        ];
    }
}
