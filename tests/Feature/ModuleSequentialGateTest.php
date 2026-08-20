<?php

use App\Models\BlockAttempt;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonBlock;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

function moduleSequence(int $moduleCount, int $perModule): Collection
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

    return $modules->sortBy('sort_order')->values();
}

function completeLesson(User $user, Lesson $lesson): void
{
    LessonProgress::create([
        'user_id' => $user->id,
        'lesson_id' => $lesson->id,
        'completed_at' => now(),
    ]);
}

test('first module of a course is accessible without completing anything', function () {
    $user = User::factory()->create();
    $modules = moduleSequence(2, 1);

    $this->actingAs($user)
        ->get(route('lessons.show', $modules[0]->lessons->first()->slug))
        ->assertOk();
});

test('a module lesson stays locked until every lesson of previous modules is completed', function () {
    $user = User::factory()->create();
    $modules = moduleSequence(2, 2);

    $secondModuleLesson = $modules[1]->lessons->first();

    $this->actingAs($user)
        ->get(route('lessons.show', $secondModuleLesson->slug))
        ->assertRedirect(route('courses.show', $secondModuleLesson->module->course->slug));

    completeLesson($user, $modules[0]->lessons->get(0));

    $this->actingAs($user)
        ->get(route('lessons.show', $secondModuleLesson->slug))
        ->assertRedirect(route('courses.show', $secondModuleLesson->module->course->slug));

    completeLesson($user, $modules[0]->lessons->get(1));

    $this->actingAs($user)
        ->get(route('lessons.show', $secondModuleLesson->slug))
        ->assertOk();
});

test('completing a locked lesson is blocked even when a previous module lesson is done', function () {
    $user = User::factory()->create();
    $modules = moduleSequence(2, 2);

    completeLesson($user, $modules[0]->lessons->get(0));

    $lockedLesson = $modules[1]->lessons->first();

    $this->actingAs($user)
        ->post(route('lessons.complete', $lockedLesson->slug))
        ->assertRedirect(route('courses.show', $lockedLesson->module->course->slug));

    expect(
        LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lockedLesson->id)
            ->exists(),
    )->toBeFalse();
});

test('course show marks locked modules on the module level', function () {
    $user = User::factory()->create();
    $modules = moduleSequence(2, 1);
    $course = $modules[0]->course;

    $this->actingAs($user)
        ->get(route('courses.show', $course->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('course.modules.0.is_locked', false)
            ->where('course.modules.1.is_locked', true)
            ->where('course.modules.1.lessons.0.is_locked', true));
});

test('course show unlocks a module after the previous module is fully completed', function () {
    $user = User::factory()->create();
    $modules = moduleSequence(2, 1);

    completeLesson($user, $modules[0]->lessons->first());

    $course = $modules[0]->course;

    $this->actingAs($user)
        ->get(route('courses.show', $course->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->where('course.modules.0.is_locked', false)
            ->where('course.modules.0.is_completed', true)
            ->where('course.modules.1.is_locked', false)
            ->where('course.modules.1.lessons.0.is_locked', false));
});

test('block attempt in a locked module is rejected and records nothing', function () {
    $user = User::factory()->create();
    $modules = moduleSequence(2, 2);

    completeLesson($user, $modules[0]->lessons->get(0));

    $lockedLesson = $modules[1]->lessons->first();

    $lockedBlock = LessonBlock::factory()->mcqSingle()->create([
        'lesson_id' => $lockedLesson->id,
    ]);

    $this->actingAs($user)
        ->post(route('lesson-blocks.attempts.store', $lockedBlock), ['answer' => 'c'])
        ->assertForbidden();

    expect(
        BlockAttempt::where('user_id', $user->id)
            ->where('lesson_block_id', $lockedBlock->id)
            ->exists(),
    )->toBeFalse();
});
