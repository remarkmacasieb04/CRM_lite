<?php

namespace App\Http\Controllers\Clients;

use App\Enums\ClientActivityType;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\StoreClientDocumentRequest;
use App\Http\Requests\Clients\UpdateClientDocumentStatusRequest;
use App\Models\Client;
use App\Models\ClientDocument;
use App\Services\Clients\ClientActivityLogger;
use App\Services\Clients\ClientDocumentNumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ClientDocumentController extends Controller
{
    public function __construct(
        private readonly ClientActivityLogger $clientActivityLogger,
        private readonly ClientDocumentNumberGenerator $documentNumberGenerator,
    ) {}

    public function store(StoreClientDocumentRequest $request, Client $client): RedirectResponse
    {
        DB::transaction(function () use ($request, $client): void {
            $type = DocumentType::from($request->validated('type'));

            $document = ClientDocument::query()->create([
                ...$request->validated(),
                'workspace_id' => $client->workspace_id,
                'client_id' => $client->id,
                'user_id' => $request->user()->id,
                'document_number' => $this->documentNumberGenerator->generate($client->workspace, $type),
                'resolved_at' => $this->resolvedAtForStatus($request->validated('status')),
            ]);

            $this->clientActivityLogger->record(
                $request->user(),
                $client,
                ClientActivityType::DocumentCreated,
                "Created {$document->type?->label()} {$document->document_number}.",
                [
                    'document_id' => $document->id,
                    'document_number' => $document->document_number,
                    'document_type' => $document->type?->value,
                ],
            );
        });

        return back()->with('success', 'Document saved successfully.');
    }

    public function updateStatus(UpdateClientDocumentStatusRequest $request, ClientDocument $document): RedirectResponse
    {
        $status = DocumentStatus::from($request->validated('status'));

        $document->forceFill([
            'status' => $status,
            'resolved_at' => $this->resolvedAtForStatus($status->value),
        ])->save();

        $this->clientActivityLogger->record(
            $request->user(),
            $document->client,
            ClientActivityType::DocumentUpdated,
            "Updated {$document->document_number} to {$status->label()}.",
            [
                'document_id' => $document->id,
                'document_number' => $document->document_number,
                'status' => $status->value,
            ],
        );

        return back()->with('success', 'Document status updated.');
    }

    public function destroy(ClientDocument $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $client = $document->client;
        $documentNumber = $document->document_number;

        $document->delete();

        if ($client !== null) {
            $this->clientActivityLogger->record(
                request()->user(),
                $client,
                ClientActivityType::DocumentUpdated,
                "Removed {$documentNumber}.",
                ['document_number' => $documentNumber],
            );
        }

        return back()->with('success', 'Document removed.');
    }

    private function resolvedAtForStatus(string $status): ?Carbon
    {
        return in_array($status, [
            DocumentStatus::Accepted->value,
            DocumentStatus::Declined->value,
            DocumentStatus::Paid->value,
            DocumentStatus::Void->value,
        ], true) ? now() : null;
    }
}
