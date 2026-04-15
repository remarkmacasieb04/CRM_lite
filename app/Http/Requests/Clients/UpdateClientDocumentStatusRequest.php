<?php

namespace App\Http\Requests\Clients;

use App\Enums\DocumentStatus;
use App\Models\ClientDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientDocumentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('document');

        return $document instanceof ClientDocument
            && ($this->user()?->can('update', $document) ?? false);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(DocumentStatus::class)],
        ];
    }
}
