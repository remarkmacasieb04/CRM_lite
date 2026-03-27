<?php

namespace App\Http\Requests\Auth;

use App\Enums\SocialProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SocialProviderRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'provider' => $this->route('provider'),
        ]);
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', Rule::in(array_map(
                static fn (SocialProvider $provider): string => $provider->value,
                SocialProvider::cases(),
            ))],
        ];
    }

    public function provider(): SocialProvider
    {
        /** @var string $provider */
        $provider = $this->validated('provider');

        return SocialProvider::from($provider);
    }
}
