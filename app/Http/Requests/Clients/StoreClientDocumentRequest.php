<?php

namespace App\Http\Requests\Clients;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientDocumentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => is_string($this->title) ? trim($this->title) : null,
            'notes' => is_string($this->notes) ? trim($this->notes) : null,
            'currency' => is_string($this->currency) ? strtoupper(trim($this->currency)) : 'USD',
            'is_portal_visible' => $this->boolean('is_portal_visible'),
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
            'type' => ['required', Rule::enum(DocumentType::class)],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(DocumentStatus::class)],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'issued_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_portal_visible' => ['required', 'boolean'],
        ];
    }
}
