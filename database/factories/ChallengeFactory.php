<?php

namespace Database\Factories;

use App\Models\Challenge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Challenge>
 */
class ChallengeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'       => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'category'    => fake()->randomElement(['algorithms', 'data-structures', 'web', 'database']),
            'difficulty'  => fake()->randomElement(['easy', 'medium', 'hard']),
            'points'      => fake()->randomElement([30, 50, 75, 100, 150]),
            'is_active'   => 1,
        ];
    }
}
