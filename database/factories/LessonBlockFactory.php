<?php

namespace Database\Factories;

use App\LessonBlockType;
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
            'type' => LessonBlockType::TEXT,
            'content' => [
                'text' => fake()->paragraph(),
            ],
            'sort_order' => 1,
        ];
    }

    public function mcqSingle(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LessonBlockType::MCQ_SINGLE,
            'content' => [
                'question' => 'Apa output dari kode berikut?',
                'code' => 'print("Hello World")',
                'options' => [
                    ['id' => 'a', 'text' => 'Hello'],
                    ['id' => 'b', 'text' => 'World'],
                    ['id' => 'c', 'text' => 'Hello World'],
                    ['id' => 'd', 'text' => 'Error'],
                ],
                'correct_answer' => 'c',
            ],
        ]);
    }
}
