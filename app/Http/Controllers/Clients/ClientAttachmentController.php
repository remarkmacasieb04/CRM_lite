<?php

namespace App\Http\Controllers\Clients;

use App\Enums\ClientActivityType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\StoreClientAttachmentRequest;
use App\Models\Client;
use App\Models\ClientAttachment;
use App\Services\Clients\ClientActivityLogger;
use App\Services\Clients\ClientAttachmentStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientAttachmentController extends Controller
{
    public function __construct(
        private readonly ClientAttachmentStorage $clientAttachmentStorage,
        private readonly ClientActivityLogger $clientActivityLogger,
    ) {}

    public function store(StoreClientAttachmentRequest $request, Client $client): RedirectResponse
    {
        DB::transaction(function () use ($request, $client): void {
            $attachment = $this->clientAttachmentStorage->store(
                $request->user(),
                $client,
                $request->file('file'),
            );

            $this->clientActivityLogger->record(
                $request->user(),
                $client,
                ClientActivityType::AttachmentAdded,
                "Uploaded attachment {$attachment->original_name}.",
            );
        });

        return back()->with('success', 'Attachment uploaded successfully.');
    }

    public function download(ClientAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $attachment);

        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $attachment->original_name,
        );
    }

    public function destroy(ClientAttachment $attachment): RedirectResponse
    {
        $this->authorize('delete', $attachment);

        DB::transaction(function () use ($attachment): void {
            $attachment->loadMissing(['client', 'user']);

            $client = $attachment->client;
            $user = $attachment->user;
            $attachmentName = $attachment->original_name;

            $this->clientAttachmentStorage->delete($attachment);

            if ($client !== null && $user !== null) {
                $this->clientActivityLogger->record(
                    $user,
                    $client,
                    ClientActivityType::AttachmentDeleted,
                    "Removed attachment {$attachmentName}.",
                );
            }
        });

        return back()->with('success', 'Attachment removed successfully.');
    }
}
