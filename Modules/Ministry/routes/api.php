<?php

use Illuminate\Support\Facades\Route;
use Modules\Ministry\Http\Controllers\MinistryController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('ministry', MinistryController::class)->names('ministry');
});
