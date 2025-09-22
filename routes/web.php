<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\authentications\LoginBasic;


// Main Page Route
// Bisa diakses tanpa login (public)
Route::get('/auth/login', [LoginBasic::class, 'index'])->name('login');

// Proses login
Route::post('/auth/proses-login', [LoginBasic::class, 'login'])->name('auth.login');
// Logout
Route::post('/auth/logout', [LoginBasic::class, 'logout'])->name('logout');

// Hanya bisa diakses kalau SUDAH login
Route::middleware(['auth'])->group(function () {
  // 🏠 Halaman utama
  Route::get('/', [HomePage::class, 'index'])->name('pages-home');
  // server
  require __DIR__ . '/modules/mikrotik.php';
  require __DIR__ . '/modules/olt.php';
  require __DIR__ . '/modules/odc.php';
  require __DIR__ . '/modules/odp.php';
  // paket kategori
  require __DIR__ . '/modules/kategori.php';
  require __DIR__ . '/modules/paket.php';
  require __DIR__ . '/modules/mapping.php'; //mapping
  require __DIR__ . '/modules/user.php'; // pelanggan
  require __DIR__ . '/modules/select.php';

  //tagihan transaksi
  require __DIR__ . '/modules/tagihan.php';
});
