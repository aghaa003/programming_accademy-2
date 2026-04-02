<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'       => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'main_points' => null,
            'category'    => fake()->randomElement(['frontend', 'backend', 'basics']),
            'level'       => fake()->randomElement(['Beginner', 'Intermediate', 'Advanced']),
            'is_active'   => 1,
        ];
    }
}
