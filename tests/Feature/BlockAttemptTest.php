<?php

use App\Models\BlockAttempt;
use App\Models\LessonBlock;
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

test('non-graded block types return 404 on attempt submission', function () {
    $user = User::factory()->create();

    $hintBlock = LessonBlock::factory()->hint()->create();
    $textBlock = LessonBlock::factory()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $hintBlock), [
            'answer' => 'x',
        ])
        ->assertNotFound();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $textBlock), [
            'answer' => 'x',
        ])
        ->assertNotFound();
});

test('answer is required for mcq single', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->mcqSingle()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), [])
        ->assertSessionHasErrors(['answer']);
});
