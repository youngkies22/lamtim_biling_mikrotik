<?php

use Illuminate\Support\Facades\Route;
use Modules\FreeRadius\Http\Controllers\RadiusConfigController;
use Modules\FreeRadius\Http\Controllers\RadiusSyncController;
use Modules\FreeRadius\Http\Controllers\RadiusUserController;
use Modules\FreeRadius\Http\Controllers\RadiusProfileController;
use Modules\FreeRadius\Http\Controllers\RadiusMigrationController;

Route::middleware(['web', 'auth'])->prefix('radius')->name('radius.')->group(function () {
    // Sync
    Route::get('/sync', [RadiusSyncController::class, 'index'])->name('sync.index');
    Route::post('/sync/process', [RadiusSyncController::class, 'sync'])->name('sync.process');
    Route::post('/sync/billing', [RadiusSyncController::class, 'syncFromBilling'])->name('sync.billing');
    
    // Users (Read-Only - data dikelola melalui Billing)
    Route::get('/users', [RadiusUserController::class, 'index'])->name('user.index');
    Route::get('/users/json', [RadiusUserController::class, 'json'])->name('user.json');

    // Profile Management (New)
    Route::get('/profiles', [RadiusProfileController::class, 'index'])->name('profile.index');
    Route::get('/profiles/json', [RadiusProfileController::class, 'json'])->name('profile.json');
    Route::post('/profiles/sync', [RadiusProfileController::class, 'sync'])->name('profile.sync');
    Route::post('/profiles/store', [RadiusProfileController::class, 'store'])->name('profile.store');

    // Migration Tool (New)
    Route::get('/migration', [RadiusMigrationController::class, 'index'])->name('migration.index');
    Route::post('/migration/process', [RadiusMigrationController::class, 'migrate'])->name('migration.process');
});
