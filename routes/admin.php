<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCourseController;
use App\Http\Controllers\AdminLessonBlockController;
use App\Http\Controllers\AdminLessonController;
use App\Http\Controllers\AdminModuleController;
use App\Http\Controllers\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');

    Route::resource('admin/courses', AdminCourseController::class)
        ->names('admin.courses');

    Route::resource('admin/users', AdminUserController::class)
        ->except(['create', 'store', 'show'])
        ->names('admin.users');

    Route::resource(
        'admin/courses.modules',
        AdminModuleController::class,
    )->names('admin.modules')->shallow();

    Route::resource(
        'admin/modules.lessons',
        AdminLessonController::class,
    )->names('admin.lessons')->shallow();

    Route::resource(
        'admin/lessons.blocks',
        AdminLessonBlockController::class,
    )->names('admin.blocks')->shallow()->except(['show']);

    Route::patch(
        'admin/lessons/{lesson}/blocks/reorder',
        [AdminLessonBlockController::class, 'reorder'],
    )->name('admin.blocks.reorder');
});
