<?php

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonBlock;
use App\Models\User;

uses()->group('feature');

test('admin can move a block to another lesson in the same course', function () {
    $course = Course::factory()->create();
    $module = CourseModule::factory()->create(['course_id' => $course->id]);
    $sourceLesson = Lesson::factory()->create(['course_module_id' => $module->id]);
    $targetLesson = Lesson::factory()->create(['course_module_id' => $module->id]);

    $blockA = LessonBlock::factory()->create(['lesson_id' => $sourceLesson->id, 'sort_order' => 1]);
    $blockB = LessonBlock::factory()->create(['lesson_id' => $sourceLesson->id, 'sort_order' => 2]);
    $targetBlock = LessonBlock::factory()->create(['lesson_id' => $targetLesson->id, 'sort_order' => 1]);

    $this->actingAs(adminUser())
        ->patch(route('admin.blocks.move', $blockA->id), [
            'target_lesson_id' => $targetLesson->id,
        ])
        ->assertRedirect(route('admin.blocks.index', $targetLesson->id))
        ->assertSessionHas('success');

    expect($blockA->fresh()->lesson_id)->toBe($targetLesson->id)
        ->and($blockA->fresh()->sort_order)->toBe(2)
        ->and($targetBlock->fresh()->sort_order)->toBe(1);

    $remaining = $sourceLesson->fresh()->blocks()->orderBy('sort_order')->get();
    expect($remaining)->toHaveCount(1)
        ->and($remaining->first()->id)->toBe($blockB->id)
        ->and($remaining->first()->sort_order)->toBe(1);
});

test('moving a block to its current lesson is rejected', function () {
    $course = Course::factory()->create();
    $module = CourseModule::factory()->create(['course_id' => $course->id]);
    $lesson = Lesson::factory()->create(['course_module_id' => $module->id]);
    $block = LessonBlock::factory()->create(['lesson_id' => $lesson->id]);

    $this->actingAs(adminUser())
        ->patch(route('admin.blocks.move', $block->id), [
            'target_lesson_id' => $lesson->id,
        ])
        ->assertInvalid('target_lesson_id');

    expect($block->fresh()->lesson_id)->toBe($lesson->id);
});

test('non-admin users cannot move blocks', function () {
    $course = Course::factory()->create();
    $module = CourseModule::factory()->create(['course_id' => $course->id]);
    $lessonA = Lesson::factory()->create(['course_module_id' => $module->id]);
    $lessonB = Lesson::factory()->create(['course_module_id' => $module->id]);
    $block = LessonBlock::factory()->create(['lesson_id' => $lessonA->id]);

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.blocks.move', $block->id), [
            'target_lesson_id' => $lessonB->id,
        ])
        ->assertForbidden();
});
