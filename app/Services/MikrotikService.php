<?php

/**
 * Tempat untuk menuliskan logika bisnis, pengolahan data, dan koordinasi antara repository dan controller.
 * Memanggil repository dan olah datanya
 * Menyusun aturan bisnis (misalnya: validasi manual, perhitungan)
 * Bisa juga trigger event, queue, dsb.
 */

namespace App\Services;

use App\Repositories\MikrotikRepository;
use App\SendRespon\LamtimResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class MikrotikService
{
  protected $mikrotikRepo;

  public function __construct(MikrotikRepository $mikrotikRepo)
  {
    $this->mikrotikRepo = $mikrotikRepo;
  }

  public function getAll()
  {
    return $this->mikrotikRepo->getAll();
  }

  public function store(array $data)
  {
    DB::beginTransaction();
    try {
      $this->mikrotikRepo->create($data);
      DB::commit();
      return LamtimResponse::accept('Data berhasil ditambahkan.');
    } catch (Exception $e) {
      DB::rollBack();

      Log::error('Gagal menambahkan Mikrotik: ' . $e->getMessage(), [
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
      $this->mikrotikRepo->update($data, decrypt($id));
      DB::commit();
      return LamtimResponse::accept('Data berhasil diperbarui.');
    } catch (Exception $e) {
      DB::rollBack();
      Log::error("Gagal mengupdate Mikrotik ID {$id}: " . $e->getMessage(), [
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
        $deleted = $this->mikrotikRepo->deleteById($id);

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
      return LamtimResponse::internalServerError('Gagal menghapus data.');
    }
  }
  public function getDatatablesJson($request)
  {
    $query = $this->mikrotikRepo->datatableQuery($request);
    return DataTables::of($query)
      ->editColumn('created_at', function ($row) {
        return Carbon::parse($row->created_at)->format('d-m-Y H:i:s');
      })
      ->editColumn('id', function ($row) {
        return encrypt($row->id); // langsung encrypt
      })
      ->make(true);
  }
}
