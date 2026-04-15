<?php

namespace App\Services\Clients;

use App\Models\Client;
use App\Models\ClientPortalShare;
use App\Models\User;
use Illuminate\Support\Str;

class ClientPortalShareManager
{
    public function create(User $user, Client $client, ?string $expiry = null): ClientPortalShare
    {
        $client->portalShares()
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        return ClientPortalShare::query()->create([
            'workspace_id' => $client->workspace_id,
            'client_id' => $client->id,
            'created_by_user_id' => $user->id,
            'token' => Str::random(64),
            'expires_at' => $expiry ? now()->addDays((int) $expiry) : now()->addDays(14),
        ]);
    }

    public function revoke(ClientPortalShare $share): void
    {
        $share->forceFill([
            'revoked_at' => now(),
        ])->save();
    }

    public function findActiveByToken(string $token): ?ClientPortalShare
    {
        $share = ClientPortalShare::query()
            ->where('token', $token)
            ->with([
                'client' => fn ($query) => $query->with([
                    'documents' => fn ($documentQuery) => $documentQuery
                        ->where('is_portal_visible', true)
                        ->latest('issued_at'),
                ]),
            ])
            ->first();

        if ($share === null || ! $share->isActive()) {
            return null;
        }

        $share->forceFill([
            'last_viewed_at' => now(),
        ])->save();

        return $share;
    }
}
