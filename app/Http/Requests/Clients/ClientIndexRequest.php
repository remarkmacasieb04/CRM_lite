<?php

namespace App\Http\Requests\Clients;

use App\Support\ClientFilters;
use Illuminate\Foundation\Http\FormRequest;

class ClientIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return ClientFilters::rules();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(ClientFilters::normalize($this->all()));
    }
}
