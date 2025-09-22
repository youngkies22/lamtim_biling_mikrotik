<?php

/**
 * Tempat untuk menuliskan logika bisnis, pengolahan data, dan koordinasi antara repository dan controller.
 * Memanggil repository dan olah datanya
 * Menyusun aturan bisnis (misalnya: validasi manual, perhitungan)
 * Bisa juga trigger event, queue, dsb.
 */

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\SendRespon\LamtimResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class UserService
{
  protected $modelRepo;

  public function __construct(UserRepository $modelRepo)
  {
    $this->modelRepo = $modelRepo;
  }

  public function getAll()
  {
    return $this->modelRepo->getAll();
  }
  public function find($id)
  {
    return $this->modelRepo->find($id);
  }
  public function findPaket($id)
  {
    return $this->modelRepo->findPaket($id);
  }
  public function findUserMikrotik($id)
  {
    return $this->modelRepo->findUserMikrotik($id);
  }
  public function findUserMikrotikByIdUser($id)
  {
    return $this->modelRepo->findUserMikrotikByIdUser($id);
  }
  public function exisUserMikrotikByIdUser($id)
  {
    return $this->modelRepo->exisUserMikrotikByIdUser(decrypt($id));
  }

  public function getOdpOdcOlt($id)
  {
    return $this->modelRepo->getOdpOdcOlt($id);
  }

  public function store(array $data)
  {
    DB::beginTransaction();
    try {
      $this->modelRepo->create($data);
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
  public function storeUserAndUserDetail(array $data_user, array $data_user_detail)
  {
    DB::beginTransaction(); // Mulai transaksi

    try {
      // Simpan data ke tabel User
      $user = $this->modelRepo->create($data_user);

      // Menambahkan user_id ke data_user_detail agar menjadi foreign key
      $data_user_detail['idUser'] = $user->id;

      // Simpan data ke tabel Lamtim_user_detail
      $this->modelRepo->createDetail($data_user_detail);

      // Commit transaksi jika tidak ada error
      DB::commit();

      return LamtimResponse::accept('Data berhasil ditambahkan.');
    } catch (Exception $e) {
      // Rollback transaksi jika terjadi error
      DB::rollBack();

      // Log error untuk debugging
      Log::error('Gagal menambahkan User dan Detail: ' . $e->getMessage(), [
        'data_user' => $data_user,
        'data_user_detail' => $data_user_detail,
        'trace' => $e->getTraceAsString()
      ]);

      return LamtimResponse::internalServerError('Gagal menambahkan data.');
    }
  }
  /**
   * insert data user mikrotik detail
   * data user yang berhubugan dengan mikrotik
   */
  public function storeUserMikrotik(array $data, ?string $messageApi = null)
  {
    DB::beginTransaction(); // Mulai transaksi

    try {
      $this->modelRepo->createMikrotikUser($data);
      // Commit transaksi jika tidak ada error
      DB::commit();
      $message = 'Data berhasil ditambahkan.';
      if ($messageApi) {
        $message .= ' | Status Mikrotik: ' . $messageApi;
      }
      return LamtimResponse::accept($message);
    } catch (Exception $e) {
      // Rollback transaksi jika terjadi error
      DB::rollBack();

      // Log error untuk debugging
      Log::error('Gagal menambahkan User Mikrotik : ' . $e->getMessage(), [
        'data_user' => $data,
        'trace' => $e->getTraceAsString()
      ]);

      return LamtimResponse::internalServerError('Gagal menambahkan data.');
    }
  }
  /**
   * update data user mikrotik detail
   * data user yang berhubugan dengan mikrotik
   */
  public function updateUserMikrotik(array $data, array $data_user_detail,  string $id)
  {
    DB::beginTransaction(); // Mulai transaksi

    try {
      // Simpan data ke tabel User
      $user = $this->modelRepo->update($data, $id);

      // Menambahkan user_id ke data_user_detail agar menjadi foreign key
      $data_user_detail['idUser'] = $user->id;

      // Simpan data ke tabel Lamtim_user_detail
      $this->modelRepo->updateDetailWhere($data_user_detail, ['idUser' => $id]);

      // Commit transaksi jika tidak ada error
      DB::commit();

      return LamtimResponse::accept('Data berhasil diupdate.');
    } catch (Exception $e) {
      // Rollback transaksi jika terjadi error
      DB::rollBack();

      // Log error untuk debugging
      Log::error('Gagal update User dan Detail: ' . $e->getMessage(), [
        'data_user' => $data,
        'data_user_detail' => $data_user_detail,
        'trace' => $e->getTraceAsString()
      ]);

      return LamtimResponse::internalServerError('Gagal update data User dan Detail.');
    }
  }

  public function update(array $data, string $id)
  {
    DB::beginTransaction();
    try {
      $this->modelRepo->update($data, decrypt($id));
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
  /**
   * update data user mikrotik detail
   * data user yang berhubugan dengan mikrotik
   */
  public function updateMikrotikUserWhere(array $data, string $id, ?string $messageApi = null)
  {
    DB::beginTransaction(); // Mulai transaksi

    try {
      $this->modelRepo->updateMikrotikUserWhere($data, decrypt($id));
      DB::commit();
      $message = 'Data berhasil update.';
      if ($messageApi) {
        $message .= ' | Status Mikrotik: ' . $messageApi;
      }
      return LamtimResponse::accept($message);
    } catch (Exception $e) {
      // Rollback transaksi jika terjadi error
      DB::rollBack();

      // Log error untuk debugging
      Log::error('Gagal update User Mikrotik : ' . $e->getMessage(), [
        'data_user' => $data,
        'trace' => $e->getTraceAsString()
      ]);

      return LamtimResponse::internalServerError('Gagal update data.');
    }
  }

  public function deleteByEncryptedId(string $encryptedId)
  {
    try {
      $id = decrypt($encryptedId);

      return DB::transaction(function () use ($id) {
        $deleted = $this->modelRepo->deleteById($id);

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
  public function getDatatablesJson(array $columns, array $callbacks = [], array $with = [], array $where = [])
  {
    $query = $this->modelRepo->datatableQuery($columns, $with, $where)->get();
    $datatable = DataTables::of($query);
    if (!empty($callbacks)) {
      foreach ($callbacks as $column => $callback) {
        $datatable->editColumn($column, $callback);
      }
    }
    return $datatable->make(true);
  }
}
