<?php

namespace App\Http\Controllers\Clients;

use App\Enums\ClientActivityType;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientPortalShare;
use App\Services\Clients\ClientActivityLogger;
use App\Services\Clients\ClientPortalShareManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientPortalShareController extends Controller
{
    public function __construct(
        private readonly ClientActivityLogger $clientActivityLogger,
        private readonly ClientPortalShareManager $portalShareManager,
    ) {}

    public function store(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $share = $this->portalShareManager->create($request->user(), $client);

        $this->clientActivityLogger->record(
            $request->user(),
            $client,
            ClientActivityType::PortalShared,
            'Created a new client portal link.',
            ['portal_url' => route('portal.show', $share->token)],
        );

        return back()->with('success', 'Client portal link generated.');
    }

    public function destroy(Request $request, ClientPortalShare $share): RedirectResponse
    {
        $this->authorize('update', $share);

        $client = $share->client;
        $this->portalShareManager->revoke($share);

        if ($client !== null) {
            $this->clientActivityLogger->record(
                $request->user(),
                $client,
                ClientActivityType::PortalRevoked,
                'Revoked the client portal link.',
            );
        }

        return back()->with('success', 'Client portal link revoked.');
    }
}
