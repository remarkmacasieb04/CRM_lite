<?php

namespace App\Http\Requests\Settings;

use App\Enums\WorkspaceMemberRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkspaceMemberRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => is_string($this->email) ? mb_strtolower(trim($this->email)) : null,
            'role' => is_string($this->role) ? trim($this->role) : null,
        ]);
    }

    public function authorize(): bool
    {
        $workspace = $this->route('workspace') ?? $this->attributes->get('currentWorkspace');

        return $workspace !== null
            && ($this->user()?->can('manageMembers', $workspace) ?? false);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'exists:users,email'],
            'role' => [
                'required',
                Rule::in([
                    WorkspaceMemberRole::Admin->value,
                    WorkspaceMemberRole::Member->value,
                ]),
            ],
        ];
    }
}
