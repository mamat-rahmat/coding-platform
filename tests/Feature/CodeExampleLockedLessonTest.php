<?php

use App\LessonBlockType;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonBlock;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

uses()->group('feature');

test('code example run on an unlocked lesson marks the block answered after refresh', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['is_published' => true]);
    $module = CourseModule::factory()->create(['course_id' => $course->id, 'sort_order' => 1]);
    $lesson = Lesson::factory()->create(['course_module_id' => $module->id, 'is_published' => true]);
    $block = LessonBlock::factory()->create([
        'lesson_id' => $lesson->id,
        'type' => LessonBlockType::CODE_EXAMPLE,
        'title' => 'Contoh',
    ]);

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block->id), ['answer' => ''])
        ->assertRedirect();

    $this->actingAs($user)
        ->get(route('lessons.show', $lesson->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('lesson.blocks.0.is_answered', true));
});

test('code example run on a locked lesson is recorded instead of 403', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['is_published' => true]);
    $module1 = CourseModule::factory()->create(['course_id' => $course->id, 'sort_order' => 1]);
    $module2 = CourseModule::factory()->create(['course_id' => $course->id, 'sort_order' => 2]);
    Lesson::factory()->create(['course_module_id' => $module1->id, 'is_published' => true]);
    $lockedLesson = Lesson::factory()->create(['course_module_id' => $module2->id, 'is_published' => true]);
    $block = LessonBlock::factory()->create([
        'lesson_id' => $lockedLesson->id,
        'type' => LessonBlockType::CODE_EXAMPLE,
        'title' => 'Contoh',
    ]);

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block->id), ['answer' => ''])
        ->assertRedirect();

    $this->assertDatabaseHas('block_attempts', [
        'user_id' => $user->id,
        'lesson_block_id' => $block->id,
        'is_correct' => true,
    ]);
});

test('graded attempt on a locked lesson is still forbidden', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['is_published' => true]);
    $module1 = CourseModule::factory()->create(['course_id' => $course->id, 'sort_order' => 1]);
    $module2 = CourseModule::factory()->create(['course_id' => $course->id, 'sort_order' => 2]);
    Lesson::factory()->create(['course_module_id' => $module1->id, 'is_published' => true]);
    $lockedLesson = Lesson::factory()->create(['course_module_id' => $module2->id, 'is_published' => true]);
    $block = LessonBlock::factory()->create([
        'lesson_id' => $lockedLesson->id,
        'type' => LessonBlockType::MCQ_SINGLE,
        'title' => 'Soal',
    ]);

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $block->id), ['answer' => 'a'])
        ->assertForbidden();
});
