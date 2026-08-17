<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => str()->slug($title),
            'description' => fake()->paragraph(),
            'language' => 'python',
            'level' => 'beginner',
            'thumbnail' => null,
            'xp_reward' => 500,
            'is_published' => true,
        ];
    }

}
