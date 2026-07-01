<?php

/**
 * UserController
 * Dev       : CODETEAM @mryes
 * Aplikasi  : Aplikasi Billing RTRW NET
 * Location  : Way Jepara Lampung Timur
 * @author mryes way jepara <mryes2210@gmail.com>
 */

namespace App\Services;

use App\Models\Lamtim_mikrotik;
use App\Models\Lamtim_user_mikrotik_details;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use \RouterOS\Client;
use \RouterOS\Query;

class MikrotikMulti
{
  /**
   * 18-11-2023
   * function koneksi api mikrotik berdasrakan yang di pilih
   * karena multi mikrotik
   */
  public static function connectionMikrotik($idMikrotik)
  {
    // $key = "mikrotik_config:{$idMikrotik}";

    // // Ambil config dari cache Redis atau database
    // $config = Cache::remember($key, now()->addMinutes(10), function () use ($idMikrotik) {
    //   $mikrotik = Lamtim_mikrotik::findOrFail($idMikrotik);
    //   return [
    //     'host'    => $mikrotik->ip,
    //     'user'    => $mikrotik->username,
    //     'pass'    => $mikrotik->password,
    //     'port'    => $mikrotik->port ?? 8728,
    //     'timeout' => 3,
    //   ];
    // });

    $db = Lamtim_mikrotik::find($idMikrotik);
    $client = new Client([
      'host'     => $db->ip,
      'user'     => $db->username,
      'pass'     => $db->password,
      'port'     => $db->port,
      // Gagal cepat kalau router tidak reachable, supaya tidak melebihi
      // max_execution_time PHP (30s) dan bisa ditangkap try/catch dengan rapi.
      // Default library: timeout 10s x 10 attempts = bisa sampai ~110s.
      'timeout'        => 5,
      'attempts'       => 2,
      // Default socket_timeout 30s nyaris sama dengan max_execution_time (30s)
      // server, jadi kalau router lambat merespon, PHP fatal-timeout duluan
      // sebelum StreamException sempat ditangkap try/catch. Turunkan ke 10s.
      'socket_timeout' => 10,
    ]);
    return $client;

    // Buat koneksi baru berdasarkan config (tidak disimpan di Redis)
    //return new Client($config);
  }

  /**
   * Menjalankan perintah API Mikrotik secara dinamis berdasarkan ID Mikrotik dan command yang diberikan.
   *
   * @param int $idMikrotik  ID Mikrotik yang terdaftar dalam database (misalnya `lamtim_mikrotik.id`)
   * @param string $command  Perintah API RouterOS (misal: '/ppp/secret/print', '/ip/address/print', dll.)
   * @param array $filters   (Opsional) Filter tambahan berupa key-value, seperti ['service' => 'pppoe']
   *
   * @return array           Hasil respon dari Mikrotik dalam bentuk array
   *
   * @example
   * // Ambil semua PPP user
   * MikrotikMulti::commandApi(1, '/ppp/secret/print');
   *
   * // Ambil hanya PPPoE user
   * MikrotikMulti::commandApi(1, '/ppp/secret/print', ['service' => 'pppoe']);
   *
   * // Ambil IP address untuk ether1
   * MikrotikMulti::commandApi(1, '/ip/address/print', ['interface' => 'ether1']);
   */
  public static function commandApi($idMikrotik, string $command, array $filters = [])
  {
    $client = self::connectionMikrotik($idMikrotik);
    $query  = new Query($command);

    foreach ($filters as $key => $value) {
      $query->where($key, $value);
    }

    return $client->query($query)->read();
  }

  public static function commandApi2($idMikrotik, string $command, array $filters = [])
  {
    try {
      $client = self::connectionMikrotik($idMikrotik);
      $query  = new Query($command);

      foreach ($filters as $key => $value) {
        if (isset($value) && trim($value) !== '') {
          $query->equal($key, $value); // GANTI from where() to equal()
        }
      }

      Log::debug('Perintah ke Mikrotik:', [
        'command' => $command,
        'filters' => $filters
      ]);

      return $client->query($query)->read();
    } catch (\Exception $e) {
      Log::error('Gagal menjalankan command API Mikrotik.', [
        'command' => $command,
        'filters' => $filters,
        'message' => $e->getMessage(),
      ]);

      return null;
    }
  }
}
