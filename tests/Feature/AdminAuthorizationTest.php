<?php

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonBlock;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function adminUser(): User
{
    return User::factory()->admin()->create();
}

function regularUser(): User
{
    return User::factory()->create();
}

test('guests are redirected from admin dashboard', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

test('non-admin users get 403 on admin dashboard', function () {
    $this->actingAs(regularUser())
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admin users can access admin dashboard', function () {
    $this->actingAs(adminUser())
        ->get(route('admin.dashboard'))
        ->assertOk();
});

test('non-admin users get 403 on admin courses index', function () {
    $this->actingAs(regularUser())
        ->get(route('admin.courses.index'))
        ->assertForbidden();
});

test('admin users can access admin courses index', function () {
    $this->actingAs(adminUser())
        ->get(route('admin.courses.index'))
        ->assertOk();
});

test('admin can create a course', function () {
    $this->actingAs(adminUser())
        ->post(route('admin.courses.store'), [
            'title' => 'Test Course',
            'slug' => 'test-course',
            'description' => 'Test description',
            'language' => 'python',
            'level' => 'beginner',
            'xp_reward' => 100,
            'is_published' => true,
        ])
        ->assertRedirect();

    expect(Course::where('slug', 'test-course')->exists())->toBeTrue();
});

test('non-admin cannot create a course', function () {
    $this->actingAs(regularUser())
        ->post(route('admin.courses.store'), [
            'title' => 'Test Course',
            'slug' => 'test-course',
            'language' => 'python',
            'level' => 'beginner',
            'xp_reward' => 100,
        ])
        ->assertForbidden();
});

test('admin can update a course', function () {
    $course = Course::factory()->create();
    $admin = adminUser();

    $this->actingAs($admin)
        ->put(route('admin.courses.update', $course), [
            'title' => 'Updated Title',
            'slug' => 'updated-slug',
            'language' => 'python',
            'level' => 'beginner',
            'xp_reward' => 200,
            'is_published' => true,
        ])
        ->assertRedirect();

    expect($course->fresh()->title)->toBe('Updated Title');
});

test('non-admin cannot update a course', function () {
    $course = Course::factory()->create();

    $this->actingAs(regularUser())
        ->put(route('admin.courses.update', $course), [
            'title' => 'Updated Title',
            'slug' => $course->slug,
            'language' => 'python',
            'level' => 'beginner',
            'xp_reward' => 100,
        ])
        ->assertForbidden();
});

test('admin can delete a course', function () {
    $course = Course::factory()->create();

    $this->actingAs(adminUser())
        ->delete(route('admin.courses.destroy', $course))
        ->assertRedirect();

    expect(Course::find($course->id))->toBeNull();
});

test('non-admin cannot delete a course', function () {
    $course = Course::factory()->create();

    $this->actingAs(regularUser())
        ->delete(route('admin.courses.destroy', $course))
        ->assertForbidden();
});

test('admin can create a module', function () {
    $course = Course::factory()->create();

    $this->actingAs(adminUser())
        ->post(route('admin.modules.store', $course), [
            'title' => 'New Module',
            'slug' => 'new-module',
            'sort_order' => 1,
        ])
        ->assertRedirect();

    expect(
        CourseModule::where('course_id', $course->id)
            ->where('slug', 'new-module')
            ->exists(),
    )->toBeTrue();
});

test('admin can create a lesson', function () {
    $module = CourseModule::factory()->create();

    $this->actingAs(adminUser())
        ->post(route('admin.lessons.store', $module), [
            'title' => 'New Lesson',
            'slug' => 'new-lesson',
            'sort_order' => 1,
            'is_published' => true,
        ])
        ->assertRedirect();

    expect(
        Lesson::where('course_module_id', $module->id)
            ->where('slug', 'new-lesson')
            ->exists(),
    )->toBeTrue();
});

test('admin can create a lesson block', function () {
    $lesson = Lesson::factory()->create();

    $this->actingAs(adminUser())
        ->post(route('admin.blocks.store', $lesson), [
            'type' => 'TEXT',
            'content' => ['text' => 'Hello'],
            'sort_order' => 1,
        ])
        ->assertRedirect();

    expect(
        LessonBlock::where('lesson_id', $lesson->id)
            ->where('type', 'TEXT')
            ->exists(),
    )->toBeTrue();
});

test('admin can reorder lesson blocks', function () {
    $lesson = Lesson::factory()->create();
    $blockA = LessonBlock::factory()->create([
        'lesson_id' => $lesson->id,
        'sort_order' => 1,
    ]);
    $blockB = LessonBlock::factory()->create([
        'lesson_id' => $lesson->id,
        'sort_order' => 2,
    ]);

    $this->actingAs(adminUser())
        ->patch(route('admin.blocks.reorder', $lesson), [
            'blocks' => [
                ['id' => $blockB->id, 'sort_order' => 1],
                ['id' => $blockA->id, 'sort_order' => 2],
            ],
        ])
        ->assertRedirect();

    expect($blockA->fresh()->sort_order)->toBe(2)
        ->and($blockB->fresh()->sort_order)->toBe(1);
});

test('non-admin cannot reorder lesson blocks', function () {
    $lesson = Lesson::factory()->create();
    $block = LessonBlock::factory()->create([
        'lesson_id' => $lesson->id,
    ]);

    $this->actingAs(regularUser())
        ->patch(route('admin.blocks.reorder', $lesson), [
            'blocks' => [
                ['id' => $block->id, 'sort_order' => 1],
            ],
        ])
        ->assertForbidden();
});

test('reorder only affects blocks belonging to the lesson', function () {
    $lesson = Lesson::factory()->create();
    $otherLesson = Lesson::factory()->create();
    $ownBlock = LessonBlock::factory()->create([
        'lesson_id' => $lesson->id,
        'sort_order' => 1,
    ]);
    $foreignBlock = LessonBlock::factory()->create([
        'lesson_id' => $otherLesson->id,
        'sort_order' => 99,
    ]);

    $this->actingAs(adminUser())
        ->patch(route('admin.blocks.reorder', $lesson), [
            'blocks' => [
                ['id' => $ownBlock->id, 'sort_order' => 5],
                ['id' => $foreignBlock->id, 'sort_order' => 10],
            ],
        ])
        ->assertRedirect();

    expect($ownBlock->fresh()->sort_order)->toBe(5)
        ->and($foreignBlock->fresh()->sort_order)->toBe(99);
});

test('module show page resolves course from module with shallow routes', function () {
    $course = Course::factory()->create();
    $module = CourseModule::factory()->create([
        'course_id' => $course->id,
    ]);

    $this->actingAs(adminUser())
        ->get(route('admin.modules.show', $module))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/modules/Show')
            ->where('course.id', $course->id)
            ->where('course.title', $course->title)
            ->where('module.id', $module->id));
});

test('block edit page resolves lesson from block with shallow routes', function () {
    $lesson = Lesson::factory()->create();
    $block = LessonBlock::factory()->create([
        'lesson_id' => $lesson->id,
    ]);

    $this->actingAs(adminUser())
        ->get(route('admin.blocks.edit', $block))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/blocks/Edit')
            ->where('lesson.id', $lesson->id)
            ->where('lesson.title', $lesson->title)
            ->where('block.id', $block->id));
});
