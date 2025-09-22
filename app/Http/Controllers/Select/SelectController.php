<?php

namespace App\Http\Controllers\Select;

use App\Http\Controllers\Controller;
use App\Services\MikrotikMulti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SelectController extends Controller
{
  /**
   * get data pelangga atau secret pada mikoritk
   * melalui data api nya
   */
  public function SelectSecretApiMikrotik($idMikrotik)
  {
    if (!$idMikrotik) {
      return response()->json(['status' => false, 'message' => 'ID Mikrotik tidak ditemukan.'], 422);
    }

    try {
      $data = MikrotikMulti::commandApi($idMikrotik, '/ppp/secret/print', ['service' => 'pppoe']);

      return response()->json([
        'status' => true,
        'data' => $data
      ]);
    } catch (\Throwable $e) {
      return response()->json([
        'status' => false,
        'message' => 'Gagal menghubungi Mikrotik: ' . $e->getMessage()
      ], 500);
    }
  }
}
