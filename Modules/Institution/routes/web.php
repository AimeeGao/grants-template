<?php

use Illuminate\Support\Facades\Route;
use Modules\Institution\Http\Controllers\InstitutionController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::prefix('institution')->group(function () {
    Route::group(
        [
            'middleware' => ['auth', 'institution_active'],
            'as' => 'institution.',
        ], function () {
            Route::get('/dashboard', [InstitutionController::class, 'index'])->name('dashboard');
            Route::get('/profile', [InstitutionController::class, 'profile'])->name('profile');
            Route::put('/profile', [InstitutionController::class, 'updateProfile'])->name('profile.update');

            // TODO: Implement applications feature
            // Route::get('/applications', [InstitutionController::class, 'applications'])->name('applications');
            // Route::get('/applications/{id}', [InstitutionController::class, 'viewApplication'])->name('applications.view');
            // Route::put('/applications/{id}/review', [InstitutionController::class, 'reviewApplication'])->name('applications.review');

            // TODO: Implement attestations feature
            // Route::get('/attestations', [InstitutionController::class, 'attestations'])->name('attestations');
            // Route::get('/attestations/{id}', [InstitutionController::class, 'viewAttestation'])->name('attestations.view');
            // Route::put('/attestations/{id}/revoke', [InstitutionController::class, 'revokeAttestation'])->name('attestations.revoke');

            // TODO: Implement reports feature
            // Route::get('/reports', [InstitutionController::class, 'reports'])->name('reports');
            // Route::get('/reports/export/{type}', [InstitutionController::class, 'exportReport'])->name('reports.export');
    });

});
