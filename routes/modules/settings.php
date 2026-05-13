<?php

use App\Http\Controllers\Settings\SyncController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')->as('settings.')->group(function () {
    Route::prefix('sync')->as('sync.')->controller(SyncController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/kategori', 'syncKategori')->name('kategori');
        Route::post('/jaringan', 'syncJaringan')->name('jaringan');
        Route::post('/paket', 'syncPaket')->name('paket');
        Route::post('/pelanggan-list', 'fetchPelangganList')->name('pelanggan.list');
        Route::post('/pelanggan-batch', 'syncPelangganBatch')->name('pelanggan.batch');
    });
});
