<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\SocialAuth;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $this->resolveUser($request->user()),
            ],
            'flash' => [
                'success' => fn (): ?string => $request->session()->get('success'),
                'error' => fn (): ?string => $request->session()->get('error'),
            ],
            'socialProviders' => SocialAuth::enabledProviders(),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveUser(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        $user->loadMissing('socialAccounts');
        $socialAccounts = $user->socialAccounts
            ->sortBy(fn ($account) => $account->provider->value)
            ->values();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $socialAccounts->pluck('avatar')->filter()->first(),
            'role' => $user->role?->value,
            'role_label' => $user->role?->label(),
            'can_access_admin' => $user->isAdmin(),
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
            'receives_follow_up_reminders' => $user->receives_follow_up_reminders,
            'last_follow_up_digest_sent_at' => $user->last_follow_up_digest_sent_at?->toDateString(),
            'connected_providers' => $socialAccounts->map(fn ($account): array => [
                'provider' => $account->provider->value,
                'label' => $account->provider->label(),
                'avatar' => $account->avatar,
                'provider_email' => $account->provider_email,
                'linked_at' => $account->created_at?->toIso8601String(),
            ])->all(),
        ];
    }
}
