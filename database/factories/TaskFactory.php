<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'course' => fake()->randomElement(['Pemrograman Web', 'Basis Data', 'Algoritma', 'Statistika']),
            'description' => fake()->optional()->sentence(),
            'priority' => fake()->randomElement(array_keys(Task::PRIORITIES)),
            'due_date' => fake()->dateTimeBetween('+1 day', '+2 weeks'),
            'is_done' => false,
        ];
    }

    public function done(): static
    {
        return $this->state(['is_done' => true]);
    }

    public function overdue(): static
    {
        return $this->state(['due_date' => today()->subDays(2)]);
    }
}
