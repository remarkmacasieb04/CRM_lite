<?php

namespace App\Http\Requests\Clients;

use App\Enums\CommunicationChannel;
use App\Enums\CommunicationDirection;
use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientCommunicationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'subject' => is_string($this->subject) ? trim($this->subject) : null,
            'summary' => is_string($this->summary) ? trim($this->summary) : null,
        ]);
    }

    public function authorize(): bool
    {
        $client = $this->route('client');

        return $client instanceof Client
            && ($this->user()?->can('update', $client) ?? false);
    }

    public function rules(): array
    {
        return [
            'channel' => ['required', Rule::enum(CommunicationChannel::class)],
            'direction' => ['required', Rule::enum(CommunicationDirection::class)],
            'subject' => ['nullable', 'string', 'max:255'],
            'summary' => ['required', 'string', 'max:5000'],
            'happened_at' => ['required', 'date'],
        ];
    }
}
