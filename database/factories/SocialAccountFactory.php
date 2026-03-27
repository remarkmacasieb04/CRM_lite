<?php

namespace Database\Factories;

use App\Enums\SocialProvider;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialAccount>
 */
class SocialAccountFactory extends Factory
{
    protected $model = SocialAccount::class;

    public function definition(): array
    {
        $provider = fake()->randomElement(SocialProvider::cases());

        return [
            'user_id' => User::factory(),
            'provider' => $provider,
            'provider_user_id' => (string) fake()->unique()->numberBetween(100000, 999999),
            'provider_email' => fake()->unique()->safeEmail(),
            'provider_email_verified_at' => now(),
            'avatar' => fake()->imageUrl(160, 160, 'people'),
        ];
    }
}
