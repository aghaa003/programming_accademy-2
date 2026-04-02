<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'question' => fake()->paragraph(),
            'difficulty' => fake()->numberBetween(1, 3),
            'assignment_order' => fake()->numberBetween(1, 10),
            'is_active' => 1,
        ];
    }
}
