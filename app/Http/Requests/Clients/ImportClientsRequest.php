<?php

namespace App\Http\Requests\Clients;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;

class ImportClientsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Client::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ];
    }
}
