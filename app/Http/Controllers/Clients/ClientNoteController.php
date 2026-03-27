<?php

namespace App\Http\Controllers\Clients;

use App\Enums\ClientActivityType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\StoreClientNoteRequest;
use App\Models\Client;
use App\Models\ClientNote;
use App\Services\Clients\ClientActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ClientNoteController extends Controller
{
    public function __construct(
        private readonly ClientActivityLogger $clientActivityLogger,
    ) {}

    public function store(StoreClientNoteRequest $request, Client $client): RedirectResponse
    {
        DB::transaction(function () use ($request, $client): ClientNote {
            $note = $client->notes()->create([
                'user_id' => $request->user()->id,
                'content' => $request->validated('content'),
            ]);

            $this->clientActivityLogger->record(
                $request->user(),
                $client,
                ClientActivityType::NoteAdded,
                'Added a client note.',
                ['note_preview' => str($note->content)->limit(80)->value()],
            );

            return $note;
        });

        return to_route('clients.show', $client)->with('success', 'Note added successfully.');
    }
}
