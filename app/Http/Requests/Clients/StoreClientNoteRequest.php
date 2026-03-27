<?php

namespace App\Http\Requests\Clients;

use App\Models\Client;
use App\Models\ClientNote;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $client = $this->route('client');

        return $client instanceof Client
            && ($this->user()?->can('create', [ClientNote::class, $client]) ?? false);
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:2', 'max:5000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $content = $this->input('content');

        $this->merge([
            'content' => is_string($content) ? trim($content) : $content,
        ]);
    }
}
