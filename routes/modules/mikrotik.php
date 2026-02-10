<?php
// 📡 Mikrotik Routes
use App\Http\Controllers\Mikrotik\MikrotikController;

Route::prefix('mikrotik')->as('mikrotik.')->controller(MikrotikController::class)->group(function () {
  // === Cek Koneksi ===
  Route::get('/connection-check', 'connectionCheck')->name('connection-check');
  Route::get('/connection-check/test/{id}', 'testConnection')->name('connection-check.test');
  Route::get('/connection-check/test-all', 'testAllConnections')->name('connection-check.test-all');

  // === Kelola PPPoE ===
  Route::get('/pppoe-manage', 'pppoeManage')->name('pppoe-manage');
  Route::get('/pppoe-manage/users/{idMikrotik}', 'getPppoeUsers')->name('pppoe-manage.users');
  Route::post('/pppoe-manage/disable', 'disablePppoe')->name('pppoe-manage.disable');
  Route::post('/pppoe-manage/enable', 'enablePppoe')->name('pppoe-manage.enable');

  // === Sinkron Pelanggan ===
  Route::get('/sync-customers', 'syncCustomers')->name('sync-customers');
  Route::get('/sync-customers/fetch/{idMikrotik}', 'fetchSecretsForSync')->name('sync-customers.fetch');
  Route::post('/sync-customers/execute', 'executeSyncCustomers')->name('sync-customers.execute');

  // === Kelola Isolir ===
  Route::get('/kelola-isolir', 'kelolaIsolir')->name('kelola-isolir');
  Route::get('/kelola-isolir/data', 'getIsolirData')->name('kelola-isolir.data');
  Route::post('/kelola-isolir/isolir', 'isolirUser')->name('kelola-isolir.isolir');
  Route::post('/kelola-isolir/aktifkan', 'aktifkanUser')->name('kelola-isolir.aktifkan');
  Route::post('/kelola-isolir/bulk-isolir', 'bulkIsolir')->name('kelola-isolir.bulk-isolir');
  Route::post('/kelola-isolir/bulk-aktifkan', 'bulkAktifkan')->name('kelola-isolir.bulk-aktifkan');

  // === CRUD (static routes sebelum wildcard {id}) ===
  Route::get('/', 'index')->name('index');
  Route::get('/create', 'create')->name('create');
  Route::post('/', 'store')->name('store');
  Route::get('/data/json', 'json')->name('json');
  Route::get('/{id}', 'show')->name('show');
  Route::get('/{id}/edit', 'edit')->name('edit');
  Route::put('/{id}', 'update')->name('update');
  Route::delete('/{id}', 'destroy')->name('destroy');
});
