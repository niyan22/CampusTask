<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'day' => fake()->numberBetween(1, 7),
            'start_time' => '08:00',
            'end_time' => '09:40',
            'room' => 'R. '.fake()->numberBetween(101, 410),
        ];
    }
}
