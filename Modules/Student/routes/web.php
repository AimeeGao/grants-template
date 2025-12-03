<?php

use Illuminate\Support\Facades\Route;
use Modules\Student\Http\Controllers\StudentController;


Route::prefix('student')->middleware(['auth', 'verified'])->group(function () {
    Route::group(['as' => 'student.'], function () {
        Route::get('/dashboard', [StudentController::class, 'index'])
            ->name('dashboard');
        Route::prefix('profile')->group(function () {
            Route::get('/', [StudentController::class, 'profile'])->name('profile.index');
            Route::put('/', [StudentController::class, 'update'])->name('profile.update');
            Route::delete('/', [StudentController::class, 'destroy'])->name('profile.destroy');
        });
    });

});