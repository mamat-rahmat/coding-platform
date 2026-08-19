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

test('correct answer stores a correct block attempt', function () {
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

test('incorrect answer stores an incorrect block attempt', function () {
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

test('non-mcq-single block returns 404', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->create();

    $this->actingAs($user)->post(
        route('lesson-blocks.attempts.store', $block),
        ['answer' => 'a'],
    )->assertNotFound();
});

test('answer is required', function () {
    $user = User::factory()->create();
    $block = LessonBlock::factory()->mcqSingle()->create();

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block), [])
        ->assertSessionHasErrors(['answer']);
});
