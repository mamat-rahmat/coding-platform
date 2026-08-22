<?php

use App\Http\Controllers\BlockAttemptController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\LessonProgressController;
use App\Http\Controllers\PlaygroundController;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('/courses/{course:slug}', [CourseController::class, 'show'])
        ->name('courses.show');

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
