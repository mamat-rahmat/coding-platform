<?php

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

function lessonSequence(int $moduleCount, int $perModule): Collection
{
    $course = Course::factory()->create();

    $modules = CourseModule::factory()
        ->count($moduleCount)
        ->create(['course_id' => $course->id]);

    $modules->each(function (CourseModule $module, int $i) use ($perModule) {
        $module->update(['sort_order' => $i + 1]);

        Lesson::factory()
            ->count($perModule)
            ->create(['course_module_id' => $module->id])
            ->each(function (Lesson $lesson, int $j) {
                $lesson->update([
                    'sort_order' => $j + 1,
                    'is_published' => true,
                ]);
            });
    });

    return $modules
        ->flatMap(fn (CourseModule $module) => $module->lessons->sortBy('sort_order'))
        ->sortBy(function (Lesson $lesson) {
            return [$lesson->module->sort_order, $lesson->sort_order];
        })
        ->values();
}

test('first lesson of a course is accessible without completing anything', function () {
    $user = User::factory()->create();
    $lessons = lessonSequence(1, 3);

    $this->actingAs($user)
        ->get(route('lessons.show', $lessons[0]->slug))
        ->assertOk();
});

test('a lesson is locked until the previous lesson is completed', function () {
    $user = User::factory()->create();
    $lessons = lessonSequence(1, 3);

    $this->actingAs($user)
        ->get(route('lessons.show', $lessons[1]->slug))
        ->assertRedirect(route('courses.show', $lessons[1]->module->course->slug));

    $this->actingAs($user)
        ->get(route('lessons.show', $lessons[2]->slug))
        ->assertRedirect(route('courses.show', $lessons[2]->module->course->slug));
});

test('a lesson unlocks after the previous lesson is completed', function () {
    $user = User::factory()->create();
    $lessons = lessonSequence(1, 3);

    LessonProgress::create([
        'user_id' => $user->id,
        'lesson_id' => $lessons[0]->id,
        'completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('lessons.show', $lessons[1]->slug))
        ->assertOk();
});

test('sequential lock crosses module boundaries', function () {
    $user = User::factory()->create();
    $lessons = lessonSequence(2, 2);

    $this->actingAs($user)
        ->get(route('lessons.show', $lessons[2]->slug))
        ->assertRedirect(route('courses.show', $lessons[2]->module->course->slug));

    LessonProgress::create([
        'user_id' => $user->id,
        'lesson_id' => $lessons[1]->id,
        'completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('lessons.show', $lessons[2]->slug))
        ->assertOk();
});

test('completing a locked lesson is blocked', function () {
    $user = User::factory()->create();
    $lessons = lessonSequence(1, 2);

    $this->actingAs($user)
        ->post(route('lessons.complete', $lessons[1]->slug))
        ->assertRedirect(route('courses.show', $lessons[1]->module->course->slug));

    expect(
        LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lessons[1]->id)
            ->exists(),
    )->toBeFalse();
});

test('course show marks locked lessons', function () {
    $user = User::factory()->create();
    $lessons = lessonSequence(1, 3);
    $course = $lessons[0]->module->course;

    $response = $this->actingAs($user)
        ->get(route('courses.show', $course->slug));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('course.modules.0.lessons.0.is_locked', false)
            ->where('course.modules.0.lessons.1.is_locked', true)
            ->where('course.modules.0.lessons.2.is_locked', true));
});

test('course show unlocks the second lesson after the first is completed', function () {
    $user = User::factory()->create();
    $lessons = lessonSequence(1, 2);
    $course = $lessons[0]->module->course;

    LessonProgress::create([
        'user_id' => $user->id,
        'lesson_id' => $lessons[0]->id,
        'completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('courses.show', $course->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->where('course.modules.0.lessons.0.is_locked', false)
            ->where('course.modules.0.lessons.1.is_locked', false));
});
