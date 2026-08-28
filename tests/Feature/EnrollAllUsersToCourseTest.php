<?php

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

uses()->group('feature');

test('enrolls all users into the course without duplicating', function () {
    $course = Course::factory()->create(['slug' => 'enroll-cmd-test']);
    $alreadyEnrolled = User::factory()->create();
    User::factory()->count(3)->create();

    $course->users()->attach($alreadyEnrolled->id);

    Artisan::call('app:enroll-all-users-to-course', ['course' => $course->slug]);

    expect($course->users()->count())->toBe(4)
        ->and(User::count())->toBe(4);
});

test('resolves the course by id', function () {
    $course = Course::factory()->create();
    User::factory()->count(2)->create();

    Artisan::call('app:enroll-all-users-to-course', ['course' => (string) $course->id]);

    expect($course->users()->count())->toBe(2);
});
