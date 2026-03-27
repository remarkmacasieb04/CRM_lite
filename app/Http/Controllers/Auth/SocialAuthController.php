<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SocialProviderRequest;
use App\Models\User;
use App\Services\Auth\SocialAccountLinker;
use App\Services\Auth\SocialAccountManager;
use App\Support\SocialAuth;
use App\Support\SocialAuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    public function __construct(
        private readonly SocialAccountLinker $socialAccountLinker,
        private readonly SocialAccountManager $socialAccountManager,
    ) {}

    public function redirect(SocialProviderRequest $request): RedirectResponse
    {
        $provider = $request->provider();

        if (! SocialAuth::isEnabled($provider)) {
            return back()->with('error', "{$provider->label()} sign in is not configured right now.");
        }

        $request->session()->put('social_auth.intent', $request->user() === null ? 'login' : 'connect');
        $request->session()->put('social_auth.user_id', $request->user()?->id);

        return Socialite::driver($provider->value)
            ->scopes(SocialAuth::scopes($provider))
            ->redirect();
    }

    public function callback(SocialProviderRequest $request): RedirectResponse
    {
        $provider = $request->provider();

        if (! SocialAuth::isEnabled($provider)) {
            return to_route('login')->with('error', "{$provider->label()} sign in is not configured right now.");
        }

        try {
            $providerUser = Socialite::driver($provider->value)->user();
            $authenticatedUser = $this->resolveAuthenticatedUser($request->user(), $request->session()->pull('social_auth.user_id'));
            $intent = $request->session()->pull('social_auth.intent', 'login');

            $user = $this->socialAccountLinker->resolveUser($provider, $providerUser, $authenticatedUser);

            if ($intent === 'connect' || $authenticatedUser !== null) {
                return to_route('profile.edit')->with('success', "{$provider->label()} is now connected to your account.");
            }

            Auth::login($user, remember: true);
            $request->session()->regenerate();

            return to_route('dashboard')->with('success', "Welcome back. Your {$provider->label()} account is ready to use.");
        } catch (SocialAuthenticationException $exception) {
            $redirectRoute = $request->user() === null ? 'login' : 'profile.edit';

            return to_route($redirectRoute)->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            $redirectRoute = $request->user() === null ? 'login' : 'profile.edit';

            return to_route($redirectRoute)->with('error', "We couldn't complete your {$provider->label()} sign in. Please try again.");
        }
    }

    public function destroy(SocialProviderRequest $request): RedirectResponse
    {
        $provider = $request->provider();

        try {
            $this->socialAccountManager->disconnect($request->user(), $provider);

            return to_route('profile.edit')->with('success', "{$provider->label()} has been disconnected from your account.");
        } catch (SocialAuthenticationException $exception) {
            return to_route('profile.edit')->with('error', $exception->getMessage());
        }
    }

    private function resolveAuthenticatedUser(?User $requestUser, mixed $sessionUserId): ?User
    {
        if ($requestUser !== null) {
            return $requestUser;
        }

        if (! is_numeric($sessionUserId)) {
            return null;
        }

        return User::query()->find((int) $sessionUserId);
    }
}
