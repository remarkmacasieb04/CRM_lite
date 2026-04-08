<?php

namespace App\Http\Controllers\Clients;

use App\Enums\ClientActivityType;
use App\Enums\ClientStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\ClientIndexRequest;
use App\Http\Requests\Clients\ImportClientsRequest;
use App\Http\Requests\Clients\StoreClientRequest;
use App\Http\Requests\Clients\UpdateClientRequest;
use App\Models\Client;
use App\Models\User;
use App\Services\Clients\ClientActivityLogger;
use App\Services\Clients\ClientAttachmentStorage;
use App\Services\Clients\ClientImportService;
use App\Services\Clients\ClientTagSyncService;
use App\Support\ClientFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientController extends Controller
{
    public function __construct(
        private readonly ClientAttachmentStorage $clientAttachmentStorage,
        private readonly ClientActivityLogger $clientActivityLogger,
        private readonly ClientImportService $clientImportService,
        private readonly ClientTagSyncService $clientTagSyncService,
    ) {}

    public function index(ClientIndexRequest $request): Response
    {
        $this->authorize('viewAny', Client::class);

        $filters = $request->validated();
        $user = $request->user();

        $clients = $this->filteredClientsQuery($user, $filters)
            ->with('tags:id,name,slug')
            ->withCount('notes')
            ->recentFirst()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Client $client): array => [
                'id' => $client->id,
                'name' => $client->name,
                'company' => $client->company,
                'email' => $client->email,
                'phone' => $client->phone,
                'status' => $client->status?->value,
                'status_label' => $client->status?->label(),
                'budget' => $client->budget,
                'source' => $client->source,
                'last_contacted_at' => $client->last_contacted_at?->toDateString(),
                'follow_up_at' => $client->follow_up_at?->toDateString(),
                'archived_at' => $client->archived_at?->toIso8601String(),
                'notes_count' => $client->notes_count,
                'updated_at' => $client->updated_at?->toIso8601String(),
                'tags' => $client->tags->map(fn ($tag): array => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ])->all(),
            ]);

        return Inertia::render('clients/Index', [
            'clients' => $clients,
            'filters' => $this->clientListFilters($filters),
            'statusOptions' => ClientStatus::options(),
            'tagOptions' => $this->availableTags($user),
            'savedViews' => $this->savedViews($user, $filters),
            'smartViews' => $this->smartViews($user, $filters),
        ]);
    }

    public function exportCsv(ClientIndexRequest $request): StreamedResponse
    {
        $this->authorize('viewAny', Client::class);

        $filters = $request->validated();
        $user = $request->user();
        $fileName = $this->exportFileName($filters['archived'] ?? null);

        $clients = $this->filteredClientsQuery($user, $filters)
            ->recentFirst()
            ->get([
                'name',
                'company',
                'email',
                'phone',
                'status',
                'budget',
                'source',
                'last_contacted_at',
                'follow_up_at',
                'archived_at',
                'created_at',
                'updated_at',
            ]);

        return response()->streamDownload(function () use ($clients): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                abort(HttpResponse::HTTP_INTERNAL_SERVER_ERROR, 'Unable to generate the export file.');
            }

            fputcsv($handle, [
                'Name',
                'Company',
                'Email',
                'Phone',
                'Status',
                'Budget',
                'Source',
                'Last Contacted',
                'Follow Up',
                'Archived At',
                'Created At',
                'Updated At',
            ]);

            foreach ($clients as $client) {
                fputcsv($handle, [
                    $client->name,
                    $client->company,
                    $client->email,
                    $client->phone,
                    $client->status?->label(),
                    $client->budget,
                    $client->source,
                    $client->last_contacted_at?->toDateString(),
                    $client->follow_up_at?->toDateString(),
                    $client->archived_at?->toDateTimeString(),
                    $client->created_at?->toDateTimeString(),
                    $client->updated_at?->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Client::class);

        return Inertia::render('clients/Create', [
            'statusOptions' => ClientStatus::options(),
            'availableTags' => $this->availableTags(request()->user()),
        ]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = DB::transaction(function () use ($request): Client {
            $attributes = collect($request->validated())
                ->except('tags')
                ->all();

            $client = $request->user()->clients()->create($attributes);

            $this->clientTagSyncService->sync(
                $request->user(),
                $client,
                $request->validated('tags'),
            );

            $this->clientActivityLogger->record(
                $request->user(),
                $client,
                ClientActivityType::Created,
                'Created this client record.',
            );

            return $client;
        });

        return to_route('clients.show', $client)->with('success', 'Client created successfully.');
    }

    public function importCsv(ImportClientsRequest $request): RedirectResponse
    {
        $result = $this->clientImportService->import(
            $request->user(),
            $request->file('file'),
        );

        $summaryParts = collect([
            $result['created'] > 0 ? "{$result['created']} created" : null,
            $result['updated'] > 0 ? "{$result['updated']} updated" : null,
            $result['skipped'] > 0 ? "{$result['skipped']} unchanged" : null,
        ])->filter()->join(', ');

        return to_route('clients.index')->with(
            'success',
            $summaryParts === ''
                ? 'CSV import completed.'
                : "CSV import completed: {$summaryParts}."
        );
    }

    public function show(Client $client): Response
    {
        $this->authorize('view', $client);

        $client->load([
            'tags' => fn ($query) => $query->orderBy('name'),
            'notes' => fn ($query) => $query
                ->with('user:id,name,email')
                ->latest(),
            'activities' => fn ($query) => $query
                ->with('user:id,name,email')
                ->latest()
                ->limit(10),
            'attachments' => fn ($query) => $query
                ->latest(),
        ]);

        return Inertia::render('clients/Show', [
            'client' => $this->clientPayload($client),
            'statusOptions' => ClientStatus::options(),
        ]);
    }

    public function edit(Client $client): Response
    {
        $this->authorize('update', $client);

        $client->loadMissing([
            'tags' => fn ($query) => $query->orderBy('name'),
        ]);

        return Inertia::render('clients/Edit', [
            'client' => $this->clientPayload($client),
            'statusOptions' => ClientStatus::options(),
            'availableTags' => $this->availableTags(request()->user()),
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client = DB::transaction(function () use ($request, $client): Client {
            $attributes = collect($request->validated())
                ->except('tags')
                ->all();
            $originalTags = $client->tags()
                ->orderBy('name')
                ->pluck('name')
                ->values()
                ->all();

            $client->fill($attributes);
            $changedFields = array_keys($client->getDirty());

            if ($changedFields !== []) {
                $client->save();
            }

            $this->clientTagSyncService->sync(
                $request->user(),
                $client,
                $request->validated('tags'),
            );

            $updatedTags = $client->tags()
                ->orderBy('name')
                ->pluck('name')
                ->values()
                ->all();

            if ($originalTags !== $updatedTags) {
                $changedFields[] = 'tags';
            }

            if ($changedFields !== []) {
                $this->clientActivityLogger->record(
                    $request->user(),
                    $client,
                    ClientActivityType::Updated,
                    'Updated client details.',
                    ['changed_fields' => $changedFields],
                );
            }

            return $client;
        });

        return to_route('clients.show', $client)->with('success', 'Client updated successfully.');
    }

    public function archive(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('archive', $client);

        DB::transaction(function () use ($request, $client): void {
            $client->forceFill([
                'archived_at' => now(),
            ])->save();

            $this->clientActivityLogger->record(
                $request->user(),
                $client,
                ClientActivityType::Archived,
                'Archived this client.',
            );
        });

        return to_route('clients.show', $client)->with('success', 'Client archived successfully.');
    }

    public function restore(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('restore', $client);

        DB::transaction(function () use ($request, $client): void {
            $client->forceFill([
                'archived_at' => null,
            ])->save();

            $this->clientActivityLogger->record(
                $request->user(),
                $client,
                ClientActivityType::Restored,
                'Restored this client to the active workspace.',
            );
        });

        return to_route('clients.show', $client)->with('success', 'Client restored successfully.');
    }

    public function destroy(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        DB::transaction(function () use ($request, $client): void {
            $this->clientAttachmentStorage->purgeForClient($client);

            $this->clientActivityLogger->record(
                $request->user(),
                $client,
                ClientActivityType::Deleted,
                'Permanently deleted this client.',
            );

            $client->delete();
        });

        return to_route('clients.index', ['archived' => 'only'])->with('success', 'Client permanently deleted.');
    }

    public function markContacted(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        DB::transaction(function () use ($request, $client): void {
            $client->forceFill([
                'last_contacted_at' => now(),
            ])->save();

            $this->clientActivityLogger->record(
                $request->user(),
                $client,
                ClientActivityType::Contacted,
                'Marked this client as contacted today.',
                ['last_contacted_at' => $client->last_contacted_at?->toIso8601String()],
            );
        });

        return back()->with('success', 'Last contacted date updated to today.');
    }

    /**
     * @param  array{
     *     search?: ?string,
     *     status?: ?string,
     *     archived?: ?string,
     *     tag?: ?string,
     *     follow_up?: ?string,
     *     stale?: ?string,
     *     page?: ?int
     * }  $filters
     */
    private function filteredClientsQuery(User $user, array $filters): Builder
    {
        return Client::query()
            ->ownedBy($user)
            ->search($filters['search'] ?? null)
            ->withStatus($filters['status'] ?? null)
            ->withTagSlug($user, $filters['tag'] ?? null)
            ->withFollowUpFilter($filters['follow_up'] ?? null)
            ->withStaleContactFilter($filters['stale'] ?? null)
            ->withArchivedFilter($filters['archived'] ?? null);
    }

    private function exportFileName(?string $archivedFilter): string
    {
        $prefix = $archivedFilter === 'only' ? 'crm-lite-archived-clients' : 'crm-lite-clients';

        return "{$prefix}-".now()->format('Y-m-d').'.csv';
    }

    private function clientPayload(Client $client): array
    {
        return [
            'id' => $client->id,
            'name' => $client->name,
            'company' => $client->company,
            'email' => $client->email,
            'phone' => $client->phone,
            'status' => $client->status?->value,
            'status_label' => $client->status?->label(),
            'budget' => $client->budget,
            'source' => $client->source,
            'last_contacted_at' => $client->last_contacted_at?->toDateString(),
            'follow_up_at' => $client->follow_up_at?->toDateString(),
            'archived_at' => $client->archived_at?->toIso8601String(),
            'created_at' => $client->created_at?->toIso8601String(),
            'updated_at' => $client->updated_at?->toIso8601String(),
            'tags' => $client->relationLoaded('tags')
                ? $client->tags->map(fn ($tag): array => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ])->all()
                : [],
            'notes' => $client->relationLoaded('notes')
                ? $client->notes->map(fn ($note): array => [
                    'id' => $note->id,
                    'content' => $note->content,
                    'created_at' => $note->created_at?->toIso8601String(),
                    'author' => [
                        'id' => $note->user?->id,
                        'name' => $note->user?->name,
                        'email' => $note->user?->email,
                    ],
                ])->all()
                : [],
            'activities' => $client->relationLoaded('activities')
                ? $client->activities->map(fn ($activity): array => [
                    'id' => $activity->id,
                    'type' => $activity->type?->value,
                    'type_label' => $activity->type?->label(),
                    'description' => $activity->description,
                    'created_at' => $activity->created_at?->toIso8601String(),
                    'actor' => [
                        'id' => $activity->user?->id,
                        'name' => $activity->user?->name,
                        'email' => $activity->user?->email,
                    ],
                    'properties' => $activity->properties ?? [],
                ])->all()
                : [],
            'attachments' => $client->relationLoaded('attachments')
                ? $client->attachments->map(fn ($attachment): array => [
                    'id' => $attachment->id,
                    'original_name' => $attachment->original_name,
                    'mime_type' => $attachment->mime_type,
                    'size' => $attachment->size,
                    'created_at' => $attachment->created_at?->toIso8601String(),
                ])->all()
                : [],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function clientListFilters(array $filters): array
    {
        return [
            'search' => $filters['search'] ?? null,
            'status' => $filters['status'] ?? null,
            'archived' => $filters['archived'] ?? null,
            'tag' => $filters['tag'] ?? null,
            'follow_up' => $filters['follow_up'] ?? null,
            'stale' => $filters['stale'] ?? null,
        ];
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string}>
     */
    private function availableTags(User $user): array
    {
        return $user->tags()
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn ($tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $currentFilters
     * @return array<int, array<string, mixed>>
     */
    private function savedViews(User $user, array $currentFilters): array
    {
        return $user->savedClientViews()
            ->latest()
            ->get()
            ->map(fn ($savedView): array => [
                'id' => $savedView->id,
                'name' => $savedView->name,
                'filters' => $savedView->filters ?? [],
                'href' => route('clients.index', $savedView->filters ?? []),
                'is_active' => ClientFilters::matches($currentFilters, $savedView->filters ?? []),
            ])->all();
    }

    /**
     * @param  array<string, mixed>  $currentFilters
     * @return array<int, array<string, mixed>>
     */
    private function smartViews(User $user, array $currentFilters): array
    {
        $baseQuery = Client::query()
            ->ownedBy($user)
            ->withArchivedFilter(null);

        $views = [
            [
                'key' => 'overdue',
                'name' => 'Overdue follow-ups',
                'description' => 'Clients whose follow-up date has already passed.',
                'filters' => ['follow_up' => 'overdue'],
                'count' => (clone $baseQuery)->withFollowUpFilter('overdue')->count(),
            ],
            [
                'key' => 'due_this_week',
                'name' => 'Due this week',
                'description' => 'Upcoming follow-ups scheduled in the next seven days.',
                'filters' => ['follow_up' => 'week'],
                'count' => (clone $baseQuery)->withFollowUpFilter('week')->count(),
            ],
            [
                'key' => 'stale',
                'name' => 'Stale contacts',
                'description' => 'Clients you have not contacted in at least two weeks.',
                'filters' => ['stale' => 'yes'],
                'count' => (clone $baseQuery)->withStaleContactFilter('yes')->count(),
            ],
            [
                'key' => 'leads',
                'name' => 'Lead pipeline',
                'description' => 'Lead records that still need nurturing.',
                'filters' => ['status' => ClientStatus::Lead->value],
                'count' => (clone $baseQuery)->withStatus(ClientStatus::Lead)->count(),
            ],
        ];

        return array_map(fn (array $view): array => [
            ...$view,
            'href' => route('clients.index', $view['filters']),
            'is_active' => ClientFilters::matches($currentFilters, $view['filters']),
        ], $views);
    }
}
