<?php

/**
 * Tempat khusus untuk menyimpan dan mengelola query database.
 * Mengakses database (Model, Query Builder, dll)
 * Menyediakan data mentah ke Service
 * Menyembunyikan kompleksitas query dari controller
 */

namespace App\Repositories;

use App\Models\Lamtim_diskon;
use App\Models\Lamtim_tagihans;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class TagihanRepository
{
  protected $model;

  public function __construct()
  {
    $this->model = new Lamtim_tagihans();
  }

  public function getAll()
  {
    return $this->model::all(); // atau with() kalau butuh relasi
  }

  public function create(array $data)
  {
    return $this->model::create($data);
  }
  public function update(array $data, string $id)
  {
    $mikrotik = $this->model::findOrFail($id);
    $mikrotik->update($data);
    return $mikrotik;
  }
  public function deleteById(int $id): bool
  {
    $tagihan = $this->model::find($id);

    if (!$tagihan) {
      return false;
    }

    return $tagihan->delete();
  }

  public function find($id)
  {
    return $this->model::find($id);
  }

  public function findByIdWithLock($id)
  {
    return $this->model->with('paket:id,nama', 'user:id,name')->where('id', $id)->lockForUpdate()->first();
  }


  /**
   * Query untuk datatables
   * Memproses request dari datatables dan mengembalikan data dalam format yang sesuai.
   * Mendukung filter, sorting, dan pagination.
   * Menggunakan Yajra DataTables untuk mempermudah proses ini.
   * Menyediakan data yang sudah diformat untuk ditampilkan di frontend
   * tagihan yang belum lunas
   *
   */
  public function getDatatablesJson(array $where = [])
  {

    $columns = [
      'id',
      'idUser',
      'idPaket',
      'noTagihan',
      'bulan',
      'tahun',
      'statusBayar',
      'harga',
      'ppn',
      'diskon',
      'total',
      'tglJatuhTempo',
      'tglBayar',
      'prosesNama',
      'metode',
      'created_at',
      'updated_at',
      'create_by',
    ];

    $query = $this->model::query()
      ->with([
        'paket:id,nama,price'
      ]);

    if (!empty($where)) {
      $query->where($where);
    }

    return $query->select($columns)->get();
  }

  public function generateTagihanBulananRepo()
  {
    $result = [
      'inserted' => 0,
      'skipped' => 0,
      'errors' => [],
      //'processed_users' => [],
    ];

    $users = User::where('idRole', 5)
      ->where('isActive', 1)
      ->with(['user_mikrotik:id,idUser,idPaket'])
      ->with(['user_mikrotik.paket:id,nama,price'])
      ->get(['id', 'name', 'isActive']);

    foreach ($users as $user) {

      try {
        $existingTagihan = $this->model::where('idUser', $user->id)
          ->where('bulan', Carbon::now()->month)
          ->where('tahun', Carbon::now()->year)
          ->first();
        $getDiskonBulanIni = Lamtim_diskon::where('idUser', $user->id)
          ->where('bulan', Carbon::now()->month)
          ->first();

        // Tentukan nilai diskon yang akan digunakan
        $nilaiDiskon = 0;
        if ($getDiskonBulanIni) {
          // Jika ada diskon bulan ini, gunakan nominal dari tabel diskon
          $nilaiDiskon = $getDiskonBulanIni->nominal;
        } else if (isset($user->user_mikrotik->diskon)) {
          // Jika tidak ada diskon bulan ini tapi ada diskon di user_mikrotik
          $nilaiDiskon = $user->user_mikrotik->diskon;
        } else {
          $nilaiDiskon = 0;
        }

        if ($existingTagihan) {
          $result['skipped']++;
          continue;
        }

        $now = Carbon::now();

        $hargaPaket = $user->user_mikrotik->paket->price;
        $ppnPercentage = config('billing.ppn') / 100; // Mengambil nilai PPN dari konfigurasi dan mengubahnya ke desimal
        // $ppn = $hargaPaket * $ppnPercentage;
        $ppn = round($hargaPaket * $ppnPercentage);
        $total = $hargaPaket + $ppn - $nilaiDiskon;

        $this->model::create([
          'idUser' => $user->id,
          'idPaket' => $user->user_mikrotik->idPaket,
          'noTagihan' => strtoupper($user->id . '-' . $now->month . $now->year . '-' . uniqid()),
          'bulan' => $now->month,
          'tahun' => $now->year,
          'tglJatuhTempo' => $now->addDays(30),
          'create_by' => Auth::id(),
          'statusBayar' => 0,
          'harga' => $hargaPaket,
          'ppn' => $ppn, // PPN 11%
          'diskon' => $nilaiDiskon,
          'total' => $total,
        ]);
        $result['inserted']++;
        //$result['processed_users'][] = $user->id;
      } catch (\Exception $e) {
        $result['errors'][] = [
          'user_id' => $user->id,
          'message' => $e->getMessage(),
        ];
      }
    }

    return $result;
  }
}
