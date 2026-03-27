<?php

namespace App\Http\Requests\Clients;

use App\Support\ClientData;
use Illuminate\Foundation\Http\FormRequest;

abstract class ClientRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(ClientData::normalize($this->all()));
    }

    protected function clientRules(): array
    {
        return ClientData::rules();
    }
}
