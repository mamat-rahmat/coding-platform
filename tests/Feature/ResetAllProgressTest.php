<?php

use App\Models\BlockAttempt;
use App\Models\Course;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('admin can reset progress for all users including admin', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $course = Course::factory()->create();

    LessonProgress::factory()->create(['user_id' => $admin->id]);
    LessonProgress::factory()->create(['user_id' => $user->id]);
    BlockAttempt::factory()->create(['user_id' => $user->id]);
    $admin->courses()->attach($course->id);
    $user->courses()->attach($course->id);

    $this->actingAs($admin)
        ->post(route('admin.users.resetAllProgress'))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    expect(LessonProgress::count())->toBe(0)
        ->and(BlockAttempt::count())->toBe(0)
        ->and(DB::table('course_user')->count())->toBe(0);
});

test('non-admin cannot reset all progress', function () {
    LessonProgress::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.users.resetAllProgress'))
        ->assertForbidden();

    expect(LessonProgress::count())->toBe(1);
});
