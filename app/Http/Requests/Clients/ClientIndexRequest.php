<?php

namespace App\Http\Requests\Clients;

use App\Enums\ClientStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::enum(ClientStatus::class)],
            'archived' => ['nullable', Rule::in(['only'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => $this->normalizeString($this->input('search')),
            'status' => $this->normalizeString($this->input('status')),
            'archived' => $this->normalizeString($this->input('archived')),
        ]);
    }

    private function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
