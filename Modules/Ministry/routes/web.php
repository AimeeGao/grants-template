<?php

use Illuminate\Support\Facades\Route;
use Modules\Ministry\Http\Controllers\MinistryController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('ministries', MinistryController::class)->names('ministry');
});
