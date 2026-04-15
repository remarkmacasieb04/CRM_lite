<?php

namespace App\Http\Requests\Tasks;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => is_string($this->title) ? trim($this->title) : null,
            'description' => is_string($this->description) ? trim($this->description) : null,
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('create', Task::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'client_id' => [
                'nullable',
                Rule::exists('clients', 'id')->where(
                    fn ($query) => $query->where('workspace_id', $this->user()?->current_workspace_id),
                ),
            ],
            'assigned_to_user_id' => [
                'nullable',
                Rule::exists('workspace_user', 'user_id')->where(
                    fn ($query) => $query->where('workspace_id', $this->user()?->current_workspace_id),
                ),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(TaskStatus::class)],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'due_at' => ['nullable', 'date'],
        ];
    }
}
