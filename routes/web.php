<?php

use App\Http\Controllers\BlockAttemptController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\LessonProgressController;
use App\Http\Controllers\PlaygroundController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Landing')->name('home');

Route::get('/courses', [CourseController::class, 'index'])
    ->name('courses.index');

Route::get('/courses/{course:slug}/leaderboard', [
    CourseController::class,
    'leaderboard',
])->name('courses.leaderboard');

Route::get('/courses/{course:slug}/leaderboard/{user}/progress', [
    CourseController::class,
    'leaderboardUserProgress',
])->name('courses.leaderboard.user-progress');

Route::get('/users/{user:uuid}', [ProfileController::class, 'show'])
    ->name('users.show');

Route::get('/users/{user:uuid}/progress/{course:slug}', [
    ProfileController::class,
    'userCourseProgress',
])->name('users.course-progress');

Route::middleware(['auth'])->group(function () {
    Route::inertia('pending-approval', 'auth/PendingApproval')
        ->name('pending-approval');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'index'])
        ->name('profile.index');

    Route::get('/profile/courses/{course:slug}', [
        ProfileController::class,
        'courseProgress',
    ])->name('profile.course-progress');

    Route::get('/courses/{course:slug}', [CourseController::class, 'show'])
        ->name('courses.show');

    Route::get('/courses/{course:slug}/syllabus', [
        CourseController::class,
        'syllabus',
    ])->name('courses.syllabus');

    Route::get('/lessons/{lesson:slug}', [LessonController::class, 'show'])
        ->name('lessons.show');

    Route::post('/lessons/{lesson:slug}/complete', [
        LessonProgressController::class,
        'complete',
    ])->name('lessons.complete');

    Route::post('/lesson-blocks/{lessonBlock}/attempts', [
        BlockAttemptController::class,
        'store',
    ])->name('lesson-blocks.attempts.store');

    Route::get('/playground', [PlaygroundController::class, 'index'])
        ->name('playground.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
