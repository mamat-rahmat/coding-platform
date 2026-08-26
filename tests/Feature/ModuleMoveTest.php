<?php

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\User;

uses()->group('feature');

test('admin can move a module to another course', function () {
    $sourceCourse = Course::factory()->create();
    $targetCourse = Course::factory()->create();
    $moduleA = CourseModule::factory()->create(['course_id' => $sourceCourse->id, 'sort_order' => 1]);
    $moduleB = CourseModule::factory()->create(['course_id' => $sourceCourse->id, 'sort_order' => 2]);
    CourseModule::factory()->create(['course_id' => $targetCourse->id, 'sort_order' => 1]);

    $this->actingAs(adminUser())
        ->patch(route('admin.modules.move', $moduleA->id), [
            'target_course_id' => $targetCourse->id,
        ])
        ->assertRedirect(route('admin.modules.index', $targetCourse->id))
        ->assertSessionHas('success');

    expect($moduleA->fresh()->course_id)->toBe($targetCourse->id)
        ->and($moduleA->fresh()->sort_order)->toBe(2);

    $remaining = $sourceCourse->fresh()->modules()->orderBy('sort_order')->get();
    expect($remaining)->toHaveCount(1)
        ->and($remaining->first()->id)->toBe($moduleB->id)
        ->and($remaining->first()->sort_order)->toBe(1);
});

test('moving a module to its current course is rejected', function () {
    $course = Course::factory()->create();
    $module = CourseModule::factory()->create(['course_id' => $course->id]);

    $this->actingAs(adminUser())
        ->patch(route('admin.modules.move', $module->id), [
            'target_course_id' => $course->id,
        ])
        ->assertInvalid('target_course_id');

    expect($module->fresh()->course_id)->toBe($course->id);
});

test('non-admin users cannot move modules', function () {
    $sourceCourse = Course::factory()->create();
    $targetCourse = Course::factory()->create();
    $module = CourseModule::factory()->create(['course_id' => $sourceCourse->id]);

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.modules.move', $module->id), [
            'target_course_id' => $targetCourse->id,
        ])
        ->assertForbidden();
});
