<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseModule>
 */
class CourseModuleFactory extends Factory
{
    protected $model = CourseModule::class;

    public function definition(): array
    {
        $title = fake()->sentence(2);

        return [
            'course_id' => Course::factory(),
            'title' => $title,
            'slug' => str()->slug($title),
            'description' => fake()->paragraph(),
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }

}
