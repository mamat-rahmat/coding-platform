<?php

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\User;

uses()->group('feature');

test('admin can move a lesson to another module in the same course', function () {
    $course = Course::factory()->create();
    $sourceModule = CourseModule::factory()->create(['course_id' => $course->id]);
    $targetModule = CourseModule::factory()->create(['course_id' => $course->id]);

    $lessonA = Lesson::factory()->create(['course_module_id' => $sourceModule->id, 'sort_order' => 1]);
    $lessonB = Lesson::factory()->create(['course_module_id' => $sourceModule->id, 'sort_order' => 2]);
    Lesson::factory()->create(['course_module_id' => $targetModule->id, 'sort_order' => 1]);

    $this->actingAs(adminUser())
        ->patch(route('admin.lessons.move', $lessonA->id), [
            'target_module_id' => $targetModule->id,
        ])
        ->assertRedirect(route('admin.lessons.index', $targetModule->id))
        ->assertSessionHas('success');

    expect($lessonA->fresh()->course_module_id)->toBe($targetModule->id)
        ->and($lessonA->fresh()->sort_order)->toBe(2);

    $remaining = $sourceModule->fresh()->lessons()->orderBy('sort_order')->get();
    expect($remaining)->toHaveCount(1)
        ->and($remaining->first()->id)->toBe($lessonB->id)
        ->and($remaining->first()->sort_order)->toBe(1);
});

test('moving a lesson to its current module is rejected', function () {
    $course = Course::factory()->create();
    $module = CourseModule::factory()->create(['course_id' => $course->id]);
    $lesson = Lesson::factory()->create(['course_module_id' => $module->id]);

    $this->actingAs(adminUser())
        ->patch(route('admin.lessons.move', $lesson->id), [
            'target_module_id' => $module->id,
        ])
        ->assertInvalid('target_module_id');

    expect($lesson->fresh()->course_module_id)->toBe($module->id);
});

test('non-admin users cannot move lessons', function () {
    $course = Course::factory()->create();
    $sourceModule = CourseModule::factory()->create(['course_id' => $course->id]);
    $targetModule = CourseModule::factory()->create(['course_id' => $course->id]);
    $lesson = Lesson::factory()->create(['course_module_id' => $sourceModule->id]);

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.lessons.move', $lesson->id), [
            'target_module_id' => $targetModule->id,
        ])
        ->assertForbidden();
});
