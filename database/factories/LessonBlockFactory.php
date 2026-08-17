<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\LessonBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonBlock>
 */
class LessonBlockFactory extends Factory
{
    protected $model = LessonBlock::class;

    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'type' => 'TEXT',
            'content' => [
                'text' => fake()->paragraph(),
            ],
            'sort_order' => 1,
        ];
    }
}
