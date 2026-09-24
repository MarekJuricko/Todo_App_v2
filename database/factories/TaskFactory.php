<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional(0.7)->paragraph(),
            'is_completed' => fake()->boolean(30),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn() => ['is_completed' => true]);
    }
}
