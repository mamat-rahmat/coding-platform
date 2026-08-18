<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::get('/courses', [CourseController::class, 'index'])
        ->name('courses.index');
    Route::get('/courses/{course:slug}', [CourseController::class, 'show'])
        ->name('courses.show');
    Route::get('/lessons/{lesson:slug}', [LessonController::class, 'show'])
        ->name('lessons.show');
});


require __DIR__.'/settings.php';
