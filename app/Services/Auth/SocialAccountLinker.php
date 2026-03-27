<?php

namespace App\Services\Auth;

use App\Enums\SocialProvider;
use App\Models\SocialAccount;
use App\Models\User;
use App\Support\SocialAuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as ProviderUser;

class SocialAccountLinker
{
    public function resolveUser(
        SocialProvider $provider,
        ProviderUser $providerUser,
        ?User $authenticatedUser = null,
    ): User {
        $profile = $this->normalizeProfile($provider, $providerUser);

        if ($profile['provider_user_id'] === '') {
            throw new SocialAuthenticationException('We could not read your social account identity. Please try again.');
        }

        return DB::transaction(function () use ($provider, $profile, $authenticatedUser): User {
            $existingAccount = SocialAccount::query()
                ->with('user')
                ->where('provider', $provider->value)
                ->where('provider_user_id', $profile['provider_user_id'])
                ->first();

            if ($existingAccount !== null) {
                if ($authenticatedUser !== null && ! $existingAccount->user->is($authenticatedUser)) {
                    throw new SocialAuthenticationException("That {$provider->label()} account is already linked to another user.");
                }

                $this->updateAccount($existingAccount, $profile);

                return $existingAccount->user;
            }

            if ($authenticatedUser !== null) {
                $this->ensureEmailDoesNotBelongToAnotherUser($profile['email'], $authenticatedUser);

                $this->ensureProviderIsNotAlreadyLinked($provider, $authenticatedUser);

                $this->createAccount($authenticatedUser, $provider, $profile);

                return $authenticatedUser;
            }

            if (! $profile['email_verified'] || blank($profile['email'])) {
                throw new SocialAuthenticationException(
                    "Your {$provider->label()} account did not provide a verified email address. Please sign in with email and password first, then connect {$provider->label()} from your account page."
                );
            }

            $user = User::query()
                ->where('email', mb_strtolower($profile['email']))
                ->first();

            if ($user === null) {
                $user = User::query()->create([
                    'name' => $profile['name'] ?: $provider->label().' User',
                    'email' => mb_strtolower($profile['email']),
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::password(32)),
                ]);
            }

            if ($user->email_verified_at === null) {
                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();
            }

            $this->createAccount($user, $provider, $profile);

            return $user;
        });
    }

    /**
     * @return array{
     *     provider_user_id: string,
     *     email: ?string,
     *     email_verified: bool,
     *     name: string,
     *     avatar: ?string
     * }
     */
    private function normalizeProfile(SocialProvider $provider, ProviderUser $providerUser): array
    {
        $raw = $providerUser->getRaw();
        $email = $providerUser->getEmail();

        return [
            'provider_user_id' => (string) ($providerUser->getId() ?? ''),
            'email' => is_string($email) ? mb_strtolower(trim($email)) : null,
            'email_verified' => $this->emailIsVerified($provider, $raw, $email),
            'name' => trim((string) ($providerUser->getName() ?? $providerUser->getNickname() ?? '')),
            'avatar' => $providerUser->getAvatar(),
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    private function emailIsVerified(SocialProvider $provider, array $raw, ?string $email): bool
    {
        if (blank($email)) {
            return false;
        }

        return match ($provider) {
            SocialProvider::Google => (bool) ($raw['email_verified'] ?? $raw['verified_email'] ?? false),
            SocialProvider::Facebook => (bool) ($raw['verified'] ?? $raw['email_verified'] ?? false),
            SocialProvider::Github => true,
        };
    }

    /**
     * @param  array{
     *     provider_user_id: string,
     *     email: ?string,
     *     email_verified: bool,
     *     name: string,
     *     avatar: ?string
     * }  $profile
     */
    private function createAccount(User $user, SocialProvider $provider, array $profile): SocialAccount
    {
        return $user->socialAccounts()->create([
            'provider' => $provider->value,
            'provider_user_id' => $profile['provider_user_id'],
            'provider_email' => $profile['email'],
            'provider_email_verified_at' => $profile['email_verified'] ? now() : null,
            'avatar' => $profile['avatar'],
        ]);
    }

    /**
     * @param  array{
     *     provider_user_id: string,
     *     email: ?string,
     *     email_verified: bool,
     *     name: string,
     *     avatar: ?string
     * }  $profile
     */
    private function updateAccount(SocialAccount $account, array $profile): void
    {
        $account->forceFill([
            'provider_email' => $profile['email'],
            'provider_email_verified_at' => $profile['email_verified'] ? now() : null,
            'avatar' => $profile['avatar'],
        ])->save();
    }

    private function ensureEmailDoesNotBelongToAnotherUser(?string $email, User $authenticatedUser): void
    {
        if (blank($email)) {
            return;
        }

        $existingUser = User::query()
            ->where('email', $email)
            ->first();

        if ($existingUser !== null && ! $existingUser->is($authenticatedUser)) {
            throw new SocialAuthenticationException('That verified email already belongs to another user account.');
        }
    }

    private function ensureProviderIsNotAlreadyLinked(SocialProvider $provider, User $authenticatedUser): void
    {
        $existingProvider = $authenticatedUser->socialAccounts()
            ->where('provider', $provider->value)
            ->exists();

        if ($existingProvider) {
            throw new SocialAuthenticationException("Your account is already connected to {$provider->label()}.");
        }
    }
}
