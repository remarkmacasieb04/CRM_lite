<?php

namespace App\Http\Controllers\Clients;

use App\Enums\ClientActivityType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\StoreClientCommunicationRequest;
use App\Models\Client;
use App\Models\ClientCommunication;
use App\Services\Clients\ClientActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ClientCommunicationController extends Controller
{
    public function __construct(
        private readonly ClientActivityLogger $clientActivityLogger,
    ) {}

    public function store(StoreClientCommunicationRequest $request, Client $client): RedirectResponse
    {
        DB::transaction(function () use ($request, $client): void {
            $communication = ClientCommunication::query()->create([
                ...$request->validated(),
                'workspace_id' => $client->workspace_id,
                'client_id' => $client->id,
                'user_id' => $request->user()->id,
            ]);

            $this->clientActivityLogger->record(
                $request->user(),
                $client,
                ClientActivityType::CommunicationLogged,
                "Logged a {$communication->channel?->label()} touchpoint.",
                [
                    'channel' => $communication->channel?->value,
                    'direction' => $communication->direction?->value,
                ],
            );
        });

        return back()->with('success', 'Communication logged successfully.');
    }
}
