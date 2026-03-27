<?php

namespace App\Support;

use App\Enums\SocialProvider;

class SocialAuth
{
    /**
     * @return array<int, array{name: string, label: string, icon: string, href: string}>
     */
    public static function enabledProviders(): array
    {
        return array_values(array_map(
            fn (SocialProvider $provider): array => [
                'name' => $provider->value,
                'label' => config("social-auth.providers.{$provider->value}.label", $provider->label()),
                'icon' => config("social-auth.providers.{$provider->value}.icon", $provider->value),
                'href' => route('social.redirect', $provider->value),
            ],
            array_values(array_filter(
                SocialProvider::cases(),
                static fn (SocialProvider $provider): bool => self::isEnabled($provider),
            )),
        ));
    }

    public static function isEnabled(SocialProvider $provider): bool
    {
        $config = config("services.{$provider->value}");

        if (! is_array($config)) {
            return false;
        }

        return filled($config['client_id'] ?? null)
            && filled($config['client_secret'] ?? null)
            && filled($config['redirect'] ?? null);
    }

    /**
     * @return array<int, string>
     */
    public static function scopes(SocialProvider $provider): array
    {
        $scopes = config("social-auth.providers.{$provider->value}.scopes", []);

        return is_array($scopes) ? array_values($scopes) : [];
    }
}
