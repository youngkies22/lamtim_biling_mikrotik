<?php

use Illuminate\Support\Facades\Route;
use Modules\Setting\Http\Controllers\SettingController;

Route::prefix('setting')->as('setting.')->middleware(['auth', 'role:1'])->group(function () {
    Route::get('/', [SettingController::class, 'index'])->name('index');
    Route::post('/hapus-bersih', [SettingController::class, 'hapusBersih'])->name('hapus-bersih');
});
