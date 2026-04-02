<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    public function definition(): array
    {
        static $order = 0;

        return [
            'course_id'       => Course::factory(),
            'title'           => fake()->sentence(4),
            'description'     => fake()->paragraph(),
            'sort_order'      => ++$order,
            'video_path'      => null,
            'video_mime_type' => null,
            'resources_code'  => null,
            'views'           => 0,
        ];
    }
}
