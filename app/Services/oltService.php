<?php

/**
 * Tempat untuk menuliskan logika bisnis, pengolahan data, dan koordinasi antara repository dan controller.
 * Memanggil repository dan olah datanya
 * Menyusun aturan bisnis (misalnya: validasi manual, perhitungan)
 * Bisa juga trigger event, queue, dsb.
 */

namespace App\Services;

use App\Models\Lamtim_olt;
use App\Repositories\BaseRepository;
use App\SendRespon\LamtimResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class oltService
{
  protected $repo;

  public function __construct()
  {
    $this->repo = new BaseRepository(new Lamtim_olt());
  }

  public function getAll()
  {
    return $this->repo->getAll();
  }

  public function store(array $data)
  {
    DB::beginTransaction();
    try {
      $this->repo->create($data);
      DB::commit();
      return LamtimResponse::accept('Data berhasil ditambahkan.');
    } catch (Exception $e) {
      DB::rollBack();

      Log::error('Gagal menambahkan Olt: ' . $e->getMessage(), [
        'data' => $data,
        'trace' => $e->getTraceAsString()
      ]);
      return LamtimResponse::internalServerError('Gagal Tambahkan data.');
    }
  }

  public function update(array $data, string $id)
  {
    DB::beginTransaction();
    try {
      $this->repo->update($data, decrypt($id));
      DB::commit();
      return LamtimResponse::accept('Data berhasil diperbarui.');
    } catch (Exception $e) {
      DB::rollBack();
      Log::error("Gagal mengupdate Olt ID {$id}: " . $e->getMessage(), [
        'data' => $data,
        'trace' => $e->getTraceAsString()
      ]);
      return LamtimResponse::internalServerError('Gagal Update data.');
    }
  }

  public function deleteByEncryptedId(string $encryptedId)
  {
    try {
      $id = decrypt($encryptedId);

      return DB::transaction(function () use ($id) {
        $deleted = $this->repo->deleteById($id);

        if (!$deleted) {
          throw new \Exception('Data tidak ditemukan.');
        }

        // Contoh: log aktivitas jika perlu
        // ActivityLog::create([...]);

        return LamtimResponse::accept('Data berhasil dihapus.');
      });
    } catch (DecryptException $e) {
      return LamtimResponse::badRequest('ID tidak valid.');
    } catch (Exception $e) {
      Log::error("Gagal Hapus Data ID {$id}: " . $e->getMessage(), [
        'trace' => $e->getTraceAsString()
      ]);
      return LamtimResponse::internalServerError('Gagal menghapus Olt data.');
    }
  }
  public function getDatatablesJson($request)
  {
    $columns = [
      'id',
      'kode',
      'nama',
      'ip',
      'port',
      'sfp',
      'isActive',
      'created_at'
    ];
    $query = $this->repo->datatableQuery($columns)->get();

    return DataTables::of($query)
      ->editColumn('created_at', function ($row) {
        return Carbon::parse($row->created_at)->format('d-m-Y H:i:s');
      })
      ->editColumn('id', function ($row) {
        return encrypt($row->id);
      })
      ->make(true);
  }
}
