<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCourseController;
use App\Http\Controllers\AdminModuleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');

    Route::resource('admin/courses', AdminCourseController::class)
        ->names('admin.courses');

    Route::resource(
        'admin/courses.modules',
        AdminModuleController::class,
    )->names('admin.modules')->shallow();
});
