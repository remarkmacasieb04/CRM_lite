<?php

namespace App\Http\Requests\Clients;

use App\Enums\ClientStatus;
use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $client = $this->route('client');

        return $client instanceof Client
            && ($this->user()?->can('update', $client) ?? false);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ClientStatus::class)],
        ];
    }
}
