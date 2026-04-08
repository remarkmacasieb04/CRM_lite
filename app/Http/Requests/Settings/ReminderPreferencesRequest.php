<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class ReminderPreferencesRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'receives_follow_up_reminders' => $this->boolean('receives_follow_up_reminders'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'receives_follow_up_reminders' => ['required', 'boolean'],
        ];
    }
}
