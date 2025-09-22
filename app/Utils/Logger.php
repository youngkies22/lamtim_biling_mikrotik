<?php

namespace App\Utils;

use App\Models\Lamtim_log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Logger
{
  /**
   * Write log ke database
   */
  public static function write($jenis, $nama, $keterangan = null, $userId = null)
  {
    try {
      Lamtim_log::create([
        'idUser' => $userId ?? Auth::id() ?? 0,
        'jenis' => strtoupper($jenis),
        'nama' => $nama,
        'date' => Carbon::now(),
        'keterangan' => $keterangan
      ]);

      return true;
    } catch (\Exception $e) {
      // Log error tapi jangan sampai crash aplikasi
      Log::error('Logger Utility Error: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Shortcut untuk CRUD operations
   */
  public static function crud($action, $nama, $keterangan = null)
  {
    return self::write('CRUD', "{$action} {$nama}", $keterangan);
  }

  /**
   * Shortcut untuk Payment
   */
  public static function payment($action, $keterangan = null)
  {
    return self::write('PAYMENT', $action, $keterangan);
  }

  /**
   * Shortcut untuk Login
   */
  public static function login($email, $success = true)
  {
    $status = $success ? 'BERHASIL' : 'GAGAL';
    $keterangan = "Email: {$email}, IP: " . request()->ip();

    return self::write('LOGIN', "Login {$status}", $keterangan);
  }

  /**
   * Shortcut untuk Logout
   */
  public static function logout($email = null)
  {
    $email = $email ?? (Auth::user()->email ?? 'Unknown');
    $keterangan = "Email: {$email}, IP: " . request()->ip();

    return self::write('LOGOUT', 'User Logout', $keterangan);
  }

  /**
   * Shortcut untuk System activities
   */
  public static function system($nama, $keterangan = null)
  {
    return self::write('SYSTEM', $nama, $keterangan);
  }

  /**
   * Shortcut untuk Error logging
   */
  public static function error($nama, $keterangan = null)
  {
    return self::write('ERROR', $nama, $keterangan);
  }

  /**
   * Shortcut untuk Access logging
   */
  public static function access($nama, $keterangan = null)
  {
    return self::write('ACCESS', $nama, $keterangan);
  }
}
