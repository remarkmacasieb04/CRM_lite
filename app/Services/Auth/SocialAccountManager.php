<?php

namespace App\Services\Auth;

use App\Enums\SocialProvider;
use App\Models\User;
use App\Support\SocialAuthenticationException;
use Illuminate\Support\Facades\DB;

class SocialAccountManager
{
    public function disconnect(User $user, SocialProvider $provider): void
    {
        $account = $user->socialAccounts()
            ->where('provider', $provider->value)
            ->first();

        if ($account === null) {
            throw new SocialAuthenticationException("Your account is not connected to {$provider->label()}.");
        }

        if (! $this->canDisconnectProviders($user)) {
            throw new SocialAuthenticationException(
                "Verify your email address or connect another sign-in method before disconnecting {$provider->label()}."
            );
        }

        DB::transaction(fn (): ?bool => $account->delete());
    }

    public function canDisconnectProviders(User $user): bool
    {
        return $user->email_verified_at !== null || $user->socialAccounts()->count() > 1;
    }
}
