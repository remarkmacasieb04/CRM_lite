<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\Clients\ClientPortalShareManager;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientPortalController extends Controller
{
    public function __construct(
        private readonly ClientPortalShareManager $portalShareManager,
    ) {}

    public function show(Request $request, string $token): Response
    {
        $share = $this->portalShareManager->findActiveByToken($token);

        abort_if($share === null, 404);

        $client = $share->client;

        abort_if($client === null, 404);

        return Inertia::render('portal/Show', [
            'portal' => [
                'workspace' => $share->workspace?->name,
                'client' => [
                    'name' => $client->name,
                    'company' => $client->company,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'status_label' => $client->status?->label(),
                    'follow_up_at' => $client->follow_up_at?->toDateString(),
                ],
                'documents' => $client->documents
                    ->where('is_portal_visible', true)
                    ->values()
                    ->map(fn ($document): array => [
                        'id' => $document->id,
                        'type_label' => $document->type?->label(),
                        'title' => $document->title,
                        'document_number' => $document->document_number,
                        'status_label' => $document->status?->label(),
                        'amount' => $document->amount,
                        'currency' => $document->currency,
                        'issued_at' => $document->issued_at?->toDateString(),
                        'due_at' => $document->due_at?->toDateString(),
                        'notes' => $document->notes,
                    ])->all(),
                'expires_at' => $share->expires_at?->toIso8601String(),
                'last_viewed_at' => $share->last_viewed_at?->toIso8601String(),
            ],
        ]);
    }
}
