<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCourseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');

    Route::resource('admin/courses', AdminCourseController::class)
        ->names('admin.courses');
});
