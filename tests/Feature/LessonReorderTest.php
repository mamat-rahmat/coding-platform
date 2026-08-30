<?php

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\User;

uses()->group('feature');

test('admin can reorder lessons within a module', function () {
    $course = Course::factory()->create();
    $module = CourseModule::factory()->create(['course_id' => $course->id]);

    $lessonA = Lesson::factory()->create(['course_module_id' => $module->id, 'sort_order' => 1]);
    $lessonB = Lesson::factory()->create(['course_module_id' => $module->id, 'sort_order' => 2]);
    $lessonC = Lesson::factory()->create(['course_module_id' => $module->id, 'sort_order' => 3]);

    $this->actingAs(adminUser())
        ->patch(route('admin.lessons.reorder', $module->id), [
            'lessons' => [
                ['id' => $lessonC->id, 'sort_order' => 1],
                ['id' => $lessonA->id, 'sort_order' => 2],
                ['id' => $lessonB->id, 'sort_order' => 3],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($lessonC->fresh()->sort_order)->toBe(1)
        ->and($lessonA->fresh()->sort_order)->toBe(2)
        ->and($lessonB->fresh()->sort_order)->toBe(3);
});

test('reorder only updates lessons belonging to the target module', function () {
    $course = Course::factory()->create();
    $module = CourseModule::factory()->create(['course_id' => $course->id]);
    $otherModule = CourseModule::factory()->create(['course_id' => $course->id]);

    $lessonA = Lesson::factory()->create(['course_module_id' => $module->id, 'sort_order' => 1]);
    $lessonB = Lesson::factory()->create(['course_module_id' => $module->id, 'sort_order' => 2]);
    $foreignLesson = Lesson::factory()->create(['course_module_id' => $otherModule->id, 'sort_order' => 1]);

    $this->actingAs(adminUser())
        ->patch(route('admin.lessons.reorder', $module->id), [
            'lessons' => [
                ['id' => $lessonB->id, 'sort_order' => 1],
                ['id' => $lessonA->id, 'sort_order' => 2],
                ['id' => $foreignLesson->id, 'sort_order' => 99],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($foreignLesson->fresh()->sort_order)->toBe(1)
        ->and($lessonB->fresh()->sort_order)->toBe(1)
        ->and($lessonA->fresh()->sort_order)->toBe(2);
});

test('non-admin users cannot reorder lessons', function () {
    $course = Course::factory()->create();
    $module = CourseModule::factory()->create(['course_id' => $course->id]);
    $lesson = Lesson::factory()->create(['course_module_id' => $module->id]);

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.lessons.reorder', $module->id), [
            'lessons' => [
                ['id' => $lesson->id, 'sort_order' => 1],
            ],
        ])
        ->assertForbidden();
});
