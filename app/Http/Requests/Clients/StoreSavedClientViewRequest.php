<?php

namespace App\Http\Requests\Clients;

use App\Support\ClientFilters;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSavedClientViewRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(ClientFilters::normalize($this->all()));
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            ...ClientFilters::rules(),
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('saved_client_views', 'name')->where(
                    fn ($query) => $query->where('user_id', $this->user()?->id),
                ),
            ],
        ];
    }
}
