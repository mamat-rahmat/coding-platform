<?php

use App\LessonBlockType;
use App\Models\Lesson;
use App\Models\LessonBlock;
use App\Models\LessonProgress;
use App\Models\User;

function attachBlock(Lesson $lesson, string $state): LessonBlock
{
    return match ($state) {
        'mcq' => LessonBlock::factory()->mcqSingle()->create([
            'lesson_id' => $lesson->id,
        ]),
        'text' => LessonBlock::factory()->create([
            'lesson_id' => $lesson->id,
        ]),
    };
}

test('lesson with single graded block auto-completes on correct attempt', function () {
    $user = User::factory()->create();
    $lesson = Lesson::factory()->create(['is_published' => true]);
    $block = attachBlock($lesson, 'mcq');

    $this->actingAs($user)->post(
        route('lesson-blocks.attempts.store', $block),
        ['answer' => 'c'],
    );

    expect(
        LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->whereNotNull('completed_at')
            ->exists(),
    )->toBeTrue();
});

test('lesson with multiple graded blocks auto-completes only when all correct', function () {
    $user = User::factory()->create();
    $lesson = Lesson::factory()->create(['is_published' => true]);
    $blockA = LessonBlock::factory()->mcqSingle()->create([
        'lesson_id' => $lesson->id,
        'sort_order' => 1,
    ]);
    $blockB = LessonBlock::factory()->mcqSingle()->create([
        'lesson_id' => $lesson->id,
        'sort_order' => 2,
    ]);

    $this->actingAs($user)->post(
        route('lesson-blocks.attempts.store', $blockA),
        ['answer' => 'c'],
    );

    expect(
        LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->exists(),
    )->toBeFalse();

    $this->actingAs($user)->post(
        route('lesson-blocks.attempts.store', $blockB),
        ['answer' => 'c'],
    );

    expect(
        LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->whereNotNull('completed_at')
            ->exists(),
    )->toBeTrue();
});

test('incorrect attempt does not auto-complete lesson', function () {
    $user = User::factory()->create();
    $lesson = Lesson::factory()->create(['is_published' => true]);
    $block = attachBlock($lesson, 'mcq');

    $this->actingAs($user)->post(
        route('lesson-blocks.attempts.store', $block),
        ['answer' => 'a'],
    );

    expect(
        LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->exists(),
    )->toBeFalse();
});

test('lesson without graded blocks is not auto-completed', function () {
    $user = User::factory()->create();
    $lesson = Lesson::factory()->create(['is_published' => true]);
    attachBlock($lesson, 'text');

    expect(
        LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->exists(),
    )->toBeFalse();
});

test('lesson auto-completion is idempotent', function () {
    $user = User::factory()->create();
    $lesson = Lesson::factory()->create(['is_published' => true]);
    $block = attachBlock($lesson, 'mcq');

    $this->actingAs($user)->post(
        route('lesson-blocks.attempts.store', $block),
        ['answer' => 'c'],
    );

    $firstCompletedAt = LessonProgress::where('user_id', $user->id)
        ->where('lesson_id', $lesson->id)
        ->value('completed_at');

    $this->actingAs($user)->post(
        route('lesson-blocks.attempts.store', $block),
        ['answer' => 'c'],
    );

    $count = LessonProgress::where('user_id', $user->id)
        ->where('lesson_id', $lesson->id)
        ->count();

    expect($count)->toBe(1);
});

test('lesson show page includes block status with graded counts', function () {
    $user = User::factory()->create();
    $lesson = Lesson::factory()->create(['is_published' => true]);
    $block = attachBlock($lesson, 'mcq');

    $response = $this->actingAs($user)->get(
        route('lessons.show', $lesson->slug),
    );

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->has('blockStatus')
            ->where('blockStatus.totalGraded', 1)
            ->where('blockStatus.correctGraded', 0)
            ->where('blockStatus.allCorrect', false),
    );
});

test('lesson show page reflects correct graded count after correct attempt', function () {
    $user = User::factory()->create();
    $lesson = Lesson::factory()->create(['is_published' => true]);
    $block = attachBlock($lesson, 'mcq');

    $this->actingAs($user)->post(
        route('lesson-blocks.attempts.store', $block),
        ['answer' => 'c'],
    );

    $response = $this->actingAs($user)->get(
        route('lessons.show', $lesson->slug),
    );

    $response->assertInertia(
        fn ($page) => $page
            ->where('blockStatus.totalGraded', 1)
            ->where('blockStatus.correctGraded', 1)
            ->where('blockStatus.allCorrect', true)
            ->where('isCompleted', true),
    );
});
