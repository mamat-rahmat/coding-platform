<?php

use App\Models\BlockAttempt;
use App\Models\Course;
use App\Models\LessonProgress;
use App\Models\User;

test('admin can reset another admin progress', function () {
    $admin = User::factory()->admin()->create();
    $targetAdmin = User::factory()->admin()->create();
    $course = Course::factory()->create();

    LessonProgress::factory()->create(['user_id' => $targetAdmin->id]);
    BlockAttempt::factory()->create(['user_id' => $targetAdmin->id]);
    $targetAdmin->courses()->attach($course->id);

    $this->actingAs($admin)
        ->delete(route('admin.users.resetProgress', $targetAdmin->id))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(LessonProgress::where('user_id', $targetAdmin->id)->count())->toBe(0)
        ->and(BlockAttempt::where('user_id', $targetAdmin->id)->count())->toBe(0)
        ->and($targetAdmin->fresh()->courses()->count())->toBe(0);
});

test('admin can reset their own progress', function () {
    $admin = User::factory()->admin()->create();
    LessonProgress::factory()->create(['user_id' => $admin->id]);

    $this->actingAs($admin)
        ->delete(route('admin.users.resetProgress', $admin->id))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(LessonProgress::where('user_id', $admin->id)->count())->toBe(0);
});

test('non-admin cannot reset progress', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('admin.users.resetProgress', $target->id))
        ->assertForbidden();
});
