<?php

namespace Database\Factories;

use App\Models\BlockAttempt;
use App\Models\LessonBlock;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlockAttempt>
 */
class BlockAttemptFactory extends Factory
{
    protected $model = BlockAttempt::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'lesson_block_id' => LessonBlock::factory(),
            'selected_answer' => fake()->randomElement(['a', 'b', 'c', 'd']),
            'is_correct' => fake()->boolean(),
            'attempt_data' => null,
            'score' => null,
            'answered_at' => now(),
        ];
    }

    public function correct(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_correct' => true,
            'score' => 100,
        ]);
    }

    public function incorrect(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_correct' => false,
            'score' => 0,
        ]);
    }
}
