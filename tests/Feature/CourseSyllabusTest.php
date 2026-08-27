<?php

use App\LessonBlockType;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonBlock;
use App\Models\User;

uses()->group('feature');

test('course syllabus lists lessons and their blocks', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['is_published' => true]);
    $module = CourseModule::factory()->create(['course_id' => $course->id]);
    $lesson = Lesson::factory()->create([
        'course_module_id' => $module->id,
        'is_published' => true,
        'title' => 'Variabel dan Tipe Data',
    ]);
    $block = LessonBlock::factory()->create([
        'lesson_id' => $lesson->id,
        'type' => LessonBlockType::CODE_CHALLENGE,
        'title' => 'Cetak Hello World',
    ]);

    $this->actingAs($user)
        ->get(route('courses.syllabus', $course->slug))
        ->assertOk()
        ->assertSee('Variabel dan Tipe Data')
        ->assertSee('Cetak Hello World')
        ->assertSee('CODE_CHALLENGE');

    expect($block)->not->toBeNull();
});

test('unpublished course syllabus returns 404', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['is_published' => false]);

    $this->actingAs($user)
        ->get(route('courses.syllabus', $course->slug))
        ->assertNotFound();
});
