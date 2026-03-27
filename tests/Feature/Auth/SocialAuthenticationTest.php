<?php

namespace Tests\Feature\Auth;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class SocialAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google' => [
                'client_id' => 'google-client',
                'client_secret' => 'google-secret',
                'redirect' => 'http://localhost/auth/google/callback',
            ],
            'services.github' => [
                'client_id' => 'github-client',
                'client_secret' => 'github-secret',
                'redirect' => 'http://localhost/auth/github/callback',
            ],
            'services.facebook' => [
                'client_id' => 'facebook-client',
                'client_secret' => 'facebook-secret',
                'redirect' => 'http://localhost/auth/facebook/callback',
            ],
        ]);
    }

    public function test_login_page_shows_configured_social_providers(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('auth/Login')
            ->has('socialProviders', 3)
            ->where('socialProviders.0.name', 'google')
            ->where('socialProviders.1.name', 'facebook')
            ->where('socialProviders.2.name', 'github'));
    }

    public function test_login_page_hides_social_providers_when_keys_are_missing(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
            'services.google.redirect' => null,
            'services.facebook.client_id' => null,
            'services.facebook.client_secret' => null,
            'services.facebook.redirect' => null,
            'services.github.client_id' => null,
            'services.github.client_secret' => null,
            'services.github.redirect' => null,
        ]);

        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('auth/Login')
            ->where('socialProviders', []));
    }

    public function test_existing_linked_social_account_can_authenticate(): void
    {
        $user = User::factory()->create();

        SocialAccount::factory()->for($user)->create([
            'provider' => 'google',
            'provider_user_id' => 'google-123',
            'provider_email' => $user->email,
        ]);

        Socialite::fake('google', $this->socialiteUser(
            id: 'google-123',
            email: $user->email,
            raw: ['email_verified' => true],
        ));

        $response = $this->get(route('social.callback', 'google'));

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_verified_social_email_links_existing_user_without_creating_duplicate(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'email_verified_at' => null,
        ]);

        Socialite::fake('google', $this->socialiteUser(
            id: 'google-456',
            email: 'owner@example.com',
            raw: ['email_verified' => true],
        ));

        $response = $this->get(route('social.callback', 'google'));

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-456',
            'provider_email' => 'owner@example.com',
        ]);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_verified_social_email_creates_a_new_user(): void
    {
        Socialite::fake('google', $this->socialiteUser(
            id: 'google-789',
            email: 'new-owner@example.com',
            raw: ['email_verified' => true],
            name: 'New Owner',
        ));

        $response = $this->get(route('social.callback', 'google'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'new-owner@example.com',
            'name' => 'New Owner',
        ]);

        $user = User::query()->where('email', 'new-owner@example.com')->firstOrFail();

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-789',
        ]);
    }

    public function test_unverified_social_email_is_rejected(): void
    {
        Socialite::fake('google', $this->socialiteUser(
            id: 'google-999',
            email: 'pending@example.com',
            raw: ['email_verified' => false],
        ));

        $response = $this->from(route('login'))->get(route('social.callback', 'google'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_disabled_provider_callback_fails_gracefully(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
            'services.google.redirect' => null,
        ]);

        $response = $this->get(route('social.callback', 'google'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
    }

    public function test_authenticated_user_can_connect_a_provider_from_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.com',
        ]);

        Socialite::fake('github', $this->socialiteUser(
            id: 'github-123',
            email: 'owner@example.com',
            raw: [],
        ));

        $this->actingAs($user)
            ->get(route('social.redirect', 'github'))
            ->assertRedirect('https://socialite.fake/github/authorize');

        $response = $this->actingAs($user)->get(route('social.callback', 'github'));

        $response->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_user_id' => 'github-123',
            'provider_email' => 'owner@example.com',
        ]);
    }

    public function test_authenticated_user_can_disconnect_a_connected_provider(): void
    {
        $user = User::factory()->create();

        $account = SocialAccount::factory()->for($user)->create([
            'provider' => 'github',
        ]);

        $response = $this->actingAs($user)->delete(route('social.destroy', 'github'));

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('social_accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_user_cannot_disconnect_their_last_provider_without_a_verified_email(): void
    {
        $user = User::factory()->unverified()->create();

        $account = SocialAccount::factory()->for($user)->create([
            'provider' => 'google',
        ]);

        $response = $this->actingAs($user)
            ->from(route('profile.edit'))
            ->delete(route('social.destroy', 'google'));

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('social_accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_user_can_disconnect_one_provider_when_another_sign_in_method_is_available(): void
    {
        $user = User::factory()->unverified()->create();

        $googleAccount = SocialAccount::factory()->for($user)->create([
            'provider' => 'google',
        ]);

        SocialAccount::factory()->for($user)->create([
            'provider' => 'github',
        ]);

        $response = $this->actingAs($user)->delete(route('social.destroy', 'google'));

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('social_accounts', [
            'id' => $googleAccount->id,
        ]);

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'github',
        ]);
    }

    private function socialiteUser(
        string $id,
        ?string $email,
        array $raw,
        string $name = 'Social User',
    ): SocialiteUser {
        return (new SocialiteUser)
            ->setRaw(array_merge($raw, [
                'id' => $id,
                'email' => $email,
            ]))
            ->map([
                'id' => $id,
                'nickname' => null,
                'name' => $name,
                'email' => $email,
                'avatar' => 'https://example.com/avatar.png',
            ]);
    }
}
