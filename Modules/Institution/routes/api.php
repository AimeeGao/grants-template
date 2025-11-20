<?php

use Illuminate\Support\Facades\Route;
use Modules\Institution\Http\Controllers\InstitutionController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('institution', InstitutionController::class)->names('institution');
});
