<?php

use App\Models\BlockAttempt;
use App\Models\Lesson;
use App\Models\LessonBlock;
use App\Models\LessonProgress;
use App\Models\User;

test('guests cannot submit block attempts', function () {
    $block = LessonBlock::factory()->mcqSingle()->create();

    $this->post(route('lesson-blocks.attempts.store', $block), [
        'answer' => 'c',
    ])->assertRedirect(route('login'));
});

test('correct mcq single answer stores a correct block attempt', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->mcqSingle()->create();

    $response = $this->actingAs($user)->post(
        route('lesson-blocks.attempts.store', $block),
        ['answer' => 'c'],
    );

    $response->assertRedirect();
    $response->assertSessionHas('attempt_result', [
        'block_id' => $block->id,
        'selected_answer' => 'c',
        'is_correct' => true,
    ]);

    expect(BlockAttempt::where('user_id', $user->id)
        ->where('lesson_block_id', $block->id)
        ->where('is_correct', true)
        ->exists())->toBeTrue();
});

test('incorrect mcq single answer stores an incorrect block attempt', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->mcqSingle()->create();

    $response = $this->actingAs($user)->post(
        route('lesson-blocks.attempts.store', $block),
        ['answer' => 'a'],
    );

    $response->assertSessionHas('attempt_result', [
        'block_id' => $block->id,
        'selected_answer' => 'a',
        'is_correct' => false,
    ]);

    expect(BlockAttempt::where('user_id', $user->id)
        ->where('lesson_block_id', $block->id)
        ->where('is_correct', false)
        ->exists())->toBeTrue();
});

test('mcq multiple with all correct answers is marked correct', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->mcqMultiple()->create();

    $response = $this->actingAs($user)->post(
        route('lesson-blocks.attempts.store', $block),
        ['answer' => 'a,b,d'],
    );

    $response->assertSessionHas('attempt_result', [
        'block_id' => $block->id,
        'selected_answer' => 'a,b,d',
        'is_correct' => true,
    ]);
});

test('mcq multiple with partial answers is marked incorrect', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->mcqMultiple()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), [
            'answer' => 'a,b',
        ])
        ->assertSessionHas('attempt_result', [
            'block_id' => $block->id,
            'selected_answer' => 'a,b',
            'is_correct' => false,
        ]);
});

test('code fill accepts client-reported correctness', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->codeFill()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), [
            'answer' => 'A:"Budi"',
            'is_correct' => true,
        ])
        ->assertSessionHas('attempt_result', [
            'block_id' => $block->id,
            'selected_answer' => 'A:"Budi"',
            'is_correct' => true,
        ]);
});

test('code reorder with correct order is marked correct', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->codeReorder()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), [
            'answer' => '1,2,0',
        ])
        ->assertSessionHas('attempt_result', [
            'block_id' => $block->id,
            'selected_answer' => '1,2,0',
            'is_correct' => true,
        ]);
});

test('code reorder with wrong order is marked incorrect', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->codeReorder()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), [
            'answer' => '0,1,2',
        ])
        ->assertSessionHas('attempt_result', [
            'block_id' => $block->id,
            'selected_answer' => '0,1,2',
            'is_correct' => false,
        ]);
});

test('code challenge stores attempt data and score', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->codeChallenge()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), [
            'answer' => '1/1',
            'is_correct' => true,
            'attempt_data' => ['tc1' => ['passed' => true]],
            'score' => 100,
        ])
        ->assertSessionHas('attempt_result', [
            'block_id' => $block->id,
            'selected_answer' => '1/1',
            'is_correct' => true,
        ]);

    $attempt = BlockAttempt::where('user_id', $user->id)
        ->where('lesson_block_id', $block->id)
        ->first();

    expect($attempt)
        ->not->toBeNull()
        ->and($attempt->attempt_data)->toBe(['tc1' => ['passed' => true]])
        ->and($attempt->score)->toBe(100);
});

test('access block types are tracked as correct on first submission', function () {
    $user = User::factory()->create();
    $hintBlock = LessonBlock::factory()->hint()->create();
    $textBlock = LessonBlock::factory()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $hintBlock), [
            'answer' => '',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('block_attempts', [
        'user_id' => $user->id,
        'lesson_block_id' => $hintBlock->id,
        'is_correct' => true,
    ]);

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $textBlock), [
            'answer' => '',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('block_attempts', [
        'user_id' => $user->id,
        'lesson_block_id' => $textBlock->id,
        'is_correct' => true,
    ]);
});

test('answer is required for mcq single', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->mcqSingle()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), [])
        ->assertSessionHasErrors(['answer']);
});

test('incorrect answer can be retried and updates the existing attempt', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->mcqSingle()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), ['answer' => 'a'])
        ->assertRedirect();

    $this->assertDatabaseHas('block_attempts', [
        'user_id' => $user->id,
        'lesson_block_id' => $block->id,
        'selected_answer' => 'a',
        'is_correct' => false,
    ]);

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), ['answer' => 'c'])
        ->assertRedirect();

    $this->assertDatabaseHas('block_attempts', [
        'user_id' => $user->id,
        'lesson_block_id' => $block->id,
        'selected_answer' => 'c',
        'is_correct' => true,
    ]);

    expect(BlockAttempt::where('user_id', $user->id)
        ->where('lesson_block_id', $block->id)
        ->count())->toBe(1);
});

test('correct answer can be retried and keeps completion while persisting new answer', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->mcqSingle()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), ['answer' => 'c'])
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), ['answer' => 'a'])
        ->assertRedirect();

    $attempt = BlockAttempt::where('user_id', $user->id)
        ->where('lesson_block_id', $block->id)
        ->first();

    expect(BlockAttempt::where('user_id', $user->id)
        ->where('lesson_block_id', $block->id)
        ->count())->toBe(1);

    expect($attempt->selected_answer)->toBe('a')
        ->and($attempt->is_correct)->toBeTrue();
});

test('wrong answer persisted then retried correctly updates attempt', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->mcqSingle()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), ['answer' => 'a']);

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), ['answer' => 'c']);

    $attempt = BlockAttempt::where('user_id', $user->id)
        ->where('lesson_block_id', $block->id)
        ->first();

    expect($attempt->selected_answer)->toBe('c')
        ->and($attempt->is_correct)->toBeTrue()
        ->and(BlockAttempt::where('user_id', $user->id)
            ->where('lesson_block_id', $block->id)
            ->count())->toBe(1);
});

test('mcq multiple can be retried after wrong answer', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->mcqMultiple()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), ['answer' => 'a,b'])
        ->assertRedirect();

    expect(BlockAttempt::where('user_id', $user->id)
        ->where('lesson_block_id', $block->id)
        ->value('is_correct'))->toBeFalse();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), ['answer' => 'a,b,d'])
        ->assertRedirect();

    $attempt = BlockAttempt::where('user_id', $user->id)
        ->where('lesson_block_id', $block->id)
        ->first();

    expect($attempt->selected_answer)->toBe('a,b,d')
        ->and($attempt->is_correct)->toBeTrue();
});

test('code fill can be retried after wrong answer', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->codeFill()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), [
            'answer' => 'A:"Wrong"',
            'is_correct' => false,
        ]);

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), [
            'answer' => 'A:"Budi"',
            'is_correct' => true,
        ]);

    $attempt = BlockAttempt::where('user_id', $user->id)
        ->where('lesson_block_id', $block->id)
        ->first();

    expect($attempt->selected_answer)->toBe('A:"Budi"')
        ->and($attempt->is_correct)->toBeTrue();
});

test('code reorder can be retried after wrong order', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->codeReorder()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), ['answer' => '0,1,2']);

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), ['answer' => '1,2,0']);

    $attempt = BlockAttempt::where('user_id', $user->id)
        ->where('lesson_block_id', $block->id)
        ->first();

    expect($attempt->selected_answer)->toBe('1,2,0')
        ->and($attempt->is_correct)->toBeTrue();
});

test('code challenge can be retried after wrong attempt', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->codeChallenge()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), [
            'answer' => '0/1',
            'is_correct' => false,
            'attempt_data' => ['tc1' => ['passed' => false]],
            'score' => 0,
        ]);

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), [
            'answer' => '1/1',
            'is_correct' => true,
            'attempt_data' => ['tc1' => ['passed' => true]],
            'score' => 100,
        ]);

    $attempt = BlockAttempt::where('user_id', $user->id)
        ->where('lesson_block_id', $block->id)
        ->first();

    expect($attempt->selected_answer)->toBe('1/1')
        ->and($attempt->is_correct)->toBeTrue()
        ->and($attempt->attempt_data)->toBe(['tc1' => ['passed' => true]])
        ->and($attempt->score)->toBe(100);
});

test('code challenge retry after correct answer keeps completion and persists new code', function () {
    $user = User::factory()->create();
    $lesson = Lesson::factory()->create();
    $block = LessonBlock::factory()->codeChallenge()->create(['lesson_id' => $lesson->id]);

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), [
            'answer' => '1/1',
            'is_correct' => true,
            'attempt_data' => ['code' => 'print("hello")', 'tc1' => ['passed' => true]],
            'score' => 100,
        ])
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), [
            'answer' => '0/1',
            'is_correct' => false,
            'attempt_data' => ['code' => 'print("x")', 'tc1' => ['passed' => false]],
            'score' => 0,
        ])
        ->assertRedirect();

    $attempt = BlockAttempt::where('user_id', $user->id)
        ->where('lesson_block_id', $block->id)
        ->first();

    expect($attempt->attempt_data['code'])->toBe('print("x")')
        ->and($attempt->selected_answer)->toBe('0/1')
        ->and($attempt->is_correct)->toBeTrue()
        ->and($attempt->score)->toBe(100)
        ->and(BlockAttempt::where('user_id', $user->id)
            ->where('lesson_block_id', $block->id)
            ->count())->toBe(1)
        ->and(LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->exists())->toBeTrue();
});

test('access block types cannot be retried', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->hint()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), ['answer' => '']);

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), ['answer' => '']);

    expect(BlockAttempt::where('user_id', $user->id)
        ->where('lesson_block_id', $block->id)
        ->count())->toBe(1);
});

test('lesson completes when all graded blocks are correct after retry', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->mcqSingle()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), ['answer' => 'a']);

    expect($user->lessonProgresses()
        ->where('lesson_id', $block->lesson_id)
        ->whereNotNull('completed_at')
        ->exists())->toBeFalse();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), ['answer' => 'c']);

    expect($user->lessonProgresses()
        ->where('lesson_id', $block->lesson_id)
        ->whereNotNull('completed_at')
        ->exists())->toBeTrue();
});
