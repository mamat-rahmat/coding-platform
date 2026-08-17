<?php

namespace Database\Factories;

use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'course_module_id' => CourseModule::factory(),
            'title' => $title,
            'slug' => str()->slug($title),
            'description' => fake()->paragraph(),
            'sort_order' => fake()->numberBetween(1, 10),
            'is_published' => true,
        ];
    }
}
