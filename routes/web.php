<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Support both GET and POST for logout for compatibility
Route::match(['GET', 'POST'], '/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/portal-login', [AuthController::class, 'portalLogin'])->name('portalLogin');
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::get('/idir-login', [AuthController::class, 'idirLogin'])->name('idir-login');
Route::get('/bceid-login', [AuthController::class, 'bceidLogin'])->name('bceid-login');
Route::get('/bcsc-login', [AuthController::class, 'bcscLogin'])->name('bcsc-login');
