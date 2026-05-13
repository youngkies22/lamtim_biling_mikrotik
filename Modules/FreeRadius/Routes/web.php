<?php

use Illuminate\Support\Facades\Route;
use Modules\FreeRadius\Http\Controllers\RadiusConfigController;
use Modules\FreeRadius\Http\Controllers\RadiusSyncController;
use Modules\FreeRadius\Http\Controllers\RadiusUserController;
use Modules\FreeRadius\Http\Controllers\RadiusProfileController;
use Modules\FreeRadius\Http\Controllers\RadiusMigrationController;
use Modules\FreeRadius\Http\Controllers\RadiusDashboardController;
use Modules\FreeRadius\Http\Controllers\RadiusNasController;
use Modules\FreeRadius\Http\Controllers\RadiusMonitorController;
use Modules\FreeRadius\Http\Controllers\RadiusGuideController;

Route::middleware(['web', 'auth'])->prefix('radius')->name('radius.')->group(function () {
    // Dashboard
    Route::get('/', [RadiusDashboardController::class, 'index'])->name('dashboard');

    // Sync
    Route::get('/sync', [RadiusSyncController::class, 'index'])->name('sync.index');
    Route::post('/sync/process', [RadiusSyncController::class, 'sync'])->name('sync.process');
    Route::post('/sync/billing', [RadiusSyncController::class, 'syncFromBilling'])->name('sync.billing');
    Route::post('/sync/nas', [RadiusSyncController::class, 'syncNas'])->name('sync.nas');
    Route::post('/sync/groups', [RadiusSyncController::class, 'syncGroups'])->name('sync.groups');
    Route::post('/sync/import', [RadiusSyncController::class, 'importFromMikrotik'])->name('sync.import');
    Route::post('/sync/all-users', [RadiusSyncController::class, 'syncFromBilling'])->name('sync.all-users');

    // NAS Management
    Route::get('/nas', [RadiusNasController::class, 'index'])->name('nas.index');
    Route::post('/nas/regenerate/{id}', [RadiusNasController::class, 'regenerate'])->name('nas.regenerate');

    // Users
    Route::get('/users', [RadiusUserController::class, 'index'])->name('user.index');
    Route::get('/users/json', [RadiusUserController::class, 'json'])->name('user.json');
    Route::get('/users/add', [RadiusUserController::class, 'add'])->name('user.add');
    Route::post('/users/store', [RadiusUserController::class, 'store'])->name('user.store');
    Route::get('/users/{username}/config', [RadiusUserController::class, 'config'])->name('user.config');
    Route::post('/users/{username}/config', [RadiusUserController::class, 'configUpdate'])->name('user.config.update');
    Route::delete('/users/{username}', [RadiusUserController::class, 'destroy'])->name('user.delete');
    Route::post('/users/sync-from-database', [RadiusUserController::class, 'syncFromDatabase'])->name('user.sync-from-db');
    Route::post('/users/{username}/sync', [RadiusUserController::class, 'syncSingleUser'])->name('user.sync-single');

    // Profile Management
    Route::get('/profiles', [RadiusProfileController::class, 'index'])->name('profile.index');
    Route::get('/profiles/json', [RadiusProfileController::class, 'json'])->name('profile.json');
    Route::post('/profiles/sync', [RadiusProfileController::class, 'sync'])->name('profile.sync');
    Route::post('/profiles/store', [RadiusProfileController::class, 'store'])->name('profile.store');
    Route::delete('/profiles/{groupname}', [RadiusProfileController::class, 'destroy'])->name('profile.delete');

    // Config
    Route::get('/config', [RadiusConfigController::class, 'index'])->name('config.index');
    Route::post('/config/store', [RadiusConfigController::class, 'store'])->name('config.store');
    Route::post('/config/test', [RadiusConfigController::class, 'testConnection'])->name('config.test');
    Route::post('/config/check-server', [RadiusConfigController::class, 'checkServer'])->name('config.check-server');

    // Guide
    Route::get('/guide', [RadiusGuideController::class, 'index'])->name('guide.index');

    // Monitoring
    Route::get('/monitoring', [RadiusMonitorController::class, 'index'])->name('monitor.index');
    Route::get('/history', [RadiusMonitorController::class, 'history'])->name('monitor.history');
    Route::delete('/monitoring/disconnect/{username}', [RadiusMonitorController::class, 'disconnect'])->name('monitor.disconnect');

    // Migration Tool
    Route::get('/migration', [RadiusMigrationController::class, 'index'])->name('migration.index');
    Route::post('/migration/process', [RadiusMigrationController::class, 'migrate'])->name('migration.process');
});
