<?php

namespace Database\Factories;

use App\Enum\TaskStatus;
use App\Enum\TaskVisibility;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => fake()->randomElement(TaskStatus::cases()),
            'title' => fake()->sentence(rand(3, 8)),
            'description' => fake()->paragraph(rand(2, 5)),
            'visibility' => fake()->randomElement(TaskVisibility::cases()),
        ];
    }

    /**
     * Set ownership by specifying the user.
     *
     * @param User $user
     *
     * @return static
     */
    public function user(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Set the task status to to-do.
     */
    public function todo(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::ToDo,
        ]);
    }

    /**
     * Set the task status to in-progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::InProgress,
        ]);
    }

    /**
     * Set the task status to done.
     */
    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::Done,
        ]);
    }

    /**
     * Set the task status to canceled.
     */
    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::Canceled,
        ]);
    }

    /**
     * Set the task visibility to public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => TaskVisibility::Public,
        ]);
    }

    /**
     * Set the task visibility to private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => TaskVisibility::Private,
        ]);
    }
}
