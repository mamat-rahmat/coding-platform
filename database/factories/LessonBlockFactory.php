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

    public function mcqMultiple(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LessonBlockType::MCQ_MULTIPLE,
            'content' => [
                'question' => 'Mana yang termasuk tipe data Python?',
                'options' => [
                    ['id' => 'a', 'text' => 'int'],
                    ['id' => 'b', 'text' => 'string'],
                    ['id' => 'c', 'text' => 'loop'],
                    ['id' => 'd', 'text' => 'float'],
                ],
                'correct_answers' => ['a', 'b', 'd'],
            ],
        ]);
    }

    public function codeFill(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LessonBlockType::CODE_FILL,
            'content' => [
                'code_template' => 'name = {{A}}\nprint({{A}})',
                'blanks' => [
                    ['id' => 'A', 'answer' => '"Budi"'],
                ],
            ],
        ]);
    }

    public function codeReorder(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LessonBlockType::CODE_REORDER,
            'content' => [
                'lines' => ['print("Hello")', 'x = 1', 'x = x + 1'],
                'correct_order' => [1, 2, 0],
            ],
        ]);
    }

    public function codeChallenge(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LessonBlockType::CODE_CHALLENGE,
            'content' => [
                'prompt' => 'Print "Hello, World!"',
                'starter_code' => '# Tulis kode di sini',
                'testcases' => [
                    [
                        'id' => 'tc1',
                        'input' => '',
                        'expected_output' => 'Hello, World!',
                        'hidden' => false,
                    ],
                ],
            ],
        ]);
    }

    public function hint(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LessonBlockType::HINT,
            'content' => [
                'title' => 'Petunjuk',
                'text' => 'Ingat: variabel harus di-assign sebelum dipakai.',
            ],
        ]);
    }
}
