<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $creator = User::factory();

        return [
            'workspace_id' => fn (array $attributes): ?int => User::query()
                ->find($attributes['created_by_user_id'])
                ?->resolveCurrentWorkspace()
                ?->id,
            'client_id' => null,
            'created_by_user_id' => $creator,
            'assigned_to_user_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(TaskStatus::cases()),
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'due_at' => fake()->optional()->dateTimeBetween('-2 days', '+7 days'),
            'completed_at' => null,
        ];
    }
}
