<?php

/**
 * Tempat untuk menuliskan logika bisnis, pengolahan data, dan koordinasi antara repository dan controller.
 * Memanggil repository dan olah datanya
 * Menyusun aturan bisnis (misalnya: validasi manual, perhitungan)
 * Bisa juga trigger event, queue, dsb.
 */


namespace App\Services;

use App\Repositories\BaseRepository;
use App\SendRespon\LamtimResponse;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class BaseService
{
  protected $repo;
  protected $secondaryRepo;

  public function __construct($modelInstance, $secondaryRepo = null)
  {
    $this->repo = new BaseRepository($modelInstance);

    if ($secondaryRepo !== null) {
      $this->secondaryRepo = new BaseRepository($secondaryRepo);
    }
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
      Log::error('Gagal menambahkan data: ' . $e->getMessage(), [
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
      Log::error("Gagal mengupdate ID {$id}: " . $e->getMessage(), [
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
          throw new Exception('Data tidak ditemukan.');
        }

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

  public function getDatatablesJson(array $columns, array $callbacks = [], array $with = [])
  {
    $query = $this->repo->datatableQuery($columns, $with)->get();
    $datatable = DataTables::of($query);
    if (!empty($callbacks)) {
      foreach ($callbacks as $column => $callback) {
        $datatable->editColumn($column, $callback);
      }
    }
    return $datatable->make(true);
  }

  /**
   * Simpan data utama dengan melakukan pengecekan dan pengurangan stok pada repository sekunder.
   *
   * Proses ini dijalankan dalam sebuah transaksi database agar operasi penyimpanan data utama dan
   * pengurangan stok dilakukan secara atomik (berhasil bersama atau gagal bersama).
   *
   * @param int   $stockId      ID data stok yang akan dicek dan dikurangi pada repository sekunder.
   * @param string $stockColumn Nama kolom pada repository stok yang menyimpan jumlah stok tersedia.
   * @param int   $stockNeeded  Jumlah stok yang dibutuhkan/dikurangi.
   * @param array $data         Data utama yang akan disimpan pada repository utama.
   *
   * @return mixed              Response sukses dari LamtimResponse jika penyimpanan berhasil.
   *
   * @throws ModelNotFoundException Jika data stok dengan ID yang diberikan tidak ditemukan.
   * @throws ValidationException    Jika stok tersedia kurang dari kebutuhan ($stockNeeded).
   * @throws Exception              Jika terjadi kesalahan lain selama proses penyimpanan atau update stok.
   */
  public function storeWithStockCheck(array $data)
  {
    DB::beginTransaction();
    try {
      if (!$this->secondaryRepo) {
        throw new \Exception("Secondary repository belum di-set");
      }

      // Jika idOdc null (ODP Parent dipilih), skip pengecekan stok
      if (empty($data['idOdc'])) {
        // Jika ada idOdp, ambil idOlt dari ODP Parent
        if (!empty($data['idOdp'])) {
          $parentOdp = $this->repo->findById($data['idOdp']);
          if ($parentOdp && $parentOdp->idOlt) {
            $data['idOlt'] = $parentOdp->idOlt;
          }
        }
        // Langsung simpan tanpa pengecekan stok
        $this->repo->create($data);
        DB::commit();
        return LamtimResponse::accept('Data berhasil ditambahkan.');
      }

      // Misal langsung ambil dari $data
      $stockId = $data['idOdc'];
      $stockColumn = 'portSisa';
      //$stockNeeded = $data['portOdc'];
      $stockNeeded = 1;  #ada masalah di sini, dmn stok di kurang dari lokasi port odc


      $stockData = $this->secondaryRepo->findById($stockId);
      if (!$stockData) {
        throw new ModelNotFoundException("Data stok dengan ID {$stockId} tidak ditemukan.");
      }

      if ($stockData->$stockColumn < $stockNeeded) {
        throw ValidationException::withMessages([
          $stockColumn => ["Port tidak cukup tersedia. Dibutuhkan: {$stockNeeded}, tersedia: {$stockData->$stockColumn}."]
        ]);
      }

      $newStock = $stockData->$stockColumn - $stockNeeded;
      $this->secondaryRepo->updateStock($stockId, $stockColumn, $newStock);

      // Tambahkan "idOlt" ke data update
      $data = array_merge($data, ['idOlt' => $stockData->idOlt]);


      $this->repo->create($data);

      DB::commit();

      return LamtimResponse::accept('Data berhasil ditambahkan.');
    } catch (ModelNotFoundException | ValidationException $e) {
      DB::rollBack();
      throw $e; // biarkan controller handle error
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Gagal menambahkan data dengan pengecekan stok: ' . $e->getMessage(), [
        'data' => $data,
        'trace' => $e->getTraceAsString()
      ]);
      return LamtimResponse::internalServerError('Gagal menambahkan data.');
    }
  }


  /**
   * Update data utama dengan melakukan penyesuaian stok pada repository sekunder.
   *
   * Proses ini dilakukan dalam sebuah transaksi database agar perubahan data utama dan
   * penyesuaian stok terjadi secara atomik (berhasil bersama atau gagal bersama).
   *
   * Mekanisme:
   * - Ambil data lama dari repository utama berdasarkan ID yang diberikan.
   * - Kembalikan stok lama ke repository stok (misal tambahkan kembali stok lama yang sudah dipakai).
   * - Cek apakah stok saat ini cukup untuk kebutuhan update dengan nilai baru.
   * - Kurangi stok sesuai kebutuhan update.
   * - Update data utama dengan data baru.
   *
   * @param array $data           Data baru yang akan digunakan untuk update.
   *                              Harus memuat field yang diperlukan seperti 'idOdc' dan 'portOdc'.
   * @param string $encryptedId   ID terenkripsi dari data utama yang akan diupdate.
   *
   * @return mixed                Response sukses dari LamtimResponse jika update berhasil.
   *
   * @throws ModelNotFoundException Jika data utama atau data stok tidak ditemukan.
   * @throws ValidationException    Jika stok tidak cukup untuk kebutuhan update.
   * @throws Exception              Jika terjadi kesalahan lain selama proses update atau penyesuaian stok.
   */

  public function updateWithStockCheck(array $data, string $encryptedId)
  {
    DB::beginTransaction();
    try {
      if (!$this->secondaryRepo) {
        throw new \Exception("Secondary repository belum di-set");
      }

      // Dekripsi ID
      $id = decrypt($encryptedId);

      // Ambil data lama dari repo utama
      $oldData = $this->repo->findById($id);
      if (!$oldData) {
        throw new ModelNotFoundException("Data dengan ID {$id} tidak ditemukan.");
      }

      // Jika data lama punya idOdc tapi data baru tidak (berubah dari ODC ke ODP Parent)
      // Kembalikan stok yang sudah dipakai
      if (!empty($oldData->idOdc) && empty($data['idOdc'])) {
        $oldStockId = $oldData->idOdc;
        $oldStockData = $this->secondaryRepo->findById($oldStockId);
        if ($oldStockData) {
          $stockColumn = 'portSisa';
          $stockUsed = 1;
          $newStock = $oldStockData->$stockColumn + $stockUsed;
          $this->secondaryRepo->updateStock($oldStockId, $stockColumn, $newStock);
        }
        // Jika ada idOdp, ambil idOlt dari ODP Parent
        if (!empty($data['idOdp'])) {
          $parentOdp = $this->repo->findById($data['idOdp']);
          if ($parentOdp && $parentOdp->idOlt) {
            $data['idOlt'] = $parentOdp->idOlt;
          }
        }
        // Langsung update tanpa pengecekan stok
        $this->repo->update($data, $id);
        DB::commit();
        return LamtimResponse::accept('Data berhasil diperbarui.');
      }

      // Jika idOdc null (ODP Parent dipilih), skip pengecekan stok
      if (empty($data['idOdc'])) {
        // Jika ada idOdp, ambil idOlt dari ODP Parent
        if (!empty($data['idOdp'])) {
          $parentOdp = $this->repo->findById($data['idOdp']);
          if ($parentOdp && $parentOdp->idOlt) {
            $data['idOlt'] = $parentOdp->idOlt;
          }
        }
        // Langsung update tanpa pengecekan stok
        $this->repo->update($data, $id);
        DB::commit();
        return LamtimResponse::accept('Data berhasil diperbarui.');
      }

      $stockId = $data['idOdc'];
      $portOdc = $data['portOdc'];

      // Jika data lama tidak punya idOdc tapi data baru punya (berubah dari ODP Parent ke ODC)
      // Kurangi stok
      if (empty($oldData->idOdc) && !empty($data['idOdc'])) {
        // Cek port sudah digunakan atau belum
        $cekPort = $this->repo->existsBy(['idOdc' => $stockId, 'portOdc' => $portOdc]);
        if ($cekPort) {
          throw new ModelNotFoundException("Port {$portOdc} sudah di gunakan.");
        }
        
        // Ambil data stok sekarang
        $stockData = $this->secondaryRepo->findById($stockId);
        if (!$stockData) {
          throw new ModelNotFoundException("Data stok dengan ID {$stockId} tidak ditemukan.");
        }
        
        // Cek stok cukup
        $stockColumn = 'portSisa';
        $stockNeeded = 1;
        if ($stockData->$stockColumn < $stockNeeded) {
          throw ValidationException::withMessages([
            $stockColumn => ["Port tidak cukup tersedia. Dibutuhkan: {$stockNeeded}, tersedia: {$stockData->$stockColumn}."]
          ]);
        }
        
        // Kurangi stok
        $newStock = $stockData->$stockColumn - $stockNeeded;
        $this->secondaryRepo->updateStock($stockId, $stockColumn, $newStock);
        
        // Tambahkan "idOlt" ke data update
        $data = array_merge($data, ['idOlt' => $stockData->idOlt]);
        
        // Update data utama
        $this->repo->update($data, $id);
        DB::commit();
        return LamtimResponse::accept('Data berhasil diperbarui.');
      }

      // Ambil data stok sekarang
      $stockData = $this->secondaryRepo->findById($stockId);
      if (!$stockData) {
        throw new ModelNotFoundException("Data stok dengan ID {$stockId} tidak ditemukan.");
      }

      // Jika berpindah dari ODC ke ODC lain, kembalikan stok ODC lama dan kurangi stok ODC baru
      if ($oldData->idOdc != $stockId) {
        $cekPort = $this->repo->existsBy(['idOdc' => $stockId, 'portOdc' => $portOdc]);
        if ($cekPort) {
          throw new ModelNotFoundException("Port {$portOdc} sudah di gunakan.");
        }
        
        // Kembalikan stok ODC lama
        if (!empty($oldData->idOdc)) {
          $oldStockData = $this->secondaryRepo->findById($oldData->idOdc);
          if ($oldStockData) {
            $stockColumn = 'portSisa';
            $stockUsed = 1;
            $newStock = $oldStockData->$stockColumn + $stockUsed;
            $this->secondaryRepo->updateStock($oldData->idOdc, $stockColumn, $newStock);
          }
        }
        
        // Kurangi stok ODC baru
        $stockColumn = 'portSisa';
        $stockNeeded = 1;
        if ($stockData->$stockColumn < $stockNeeded) {
          throw ValidationException::withMessages([
            $stockColumn => ["Port tidak cukup tersedia. Dibutuhkan: {$stockNeeded}, tersedia: {$stockData->$stockColumn}."]
          ]);
        }
        
        $newStock = $stockData->$stockColumn - $stockNeeded;
        $this->secondaryRepo->updateStock($stockId, $stockColumn, $newStock);
      }

      // Tambahkan "idOlt" ke data update
      $data = array_merge($data, ['idOlt' => $stockData->idOlt]);

      // Update data utama
      $this->repo->update($data, $id);

      DB::commit();

      return LamtimResponse::accept('Data berhasil diperbarui.');
    } catch (ModelNotFoundException | ValidationException $e) {
      DB::rollBack();
      throw $e;
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Gagal update data dengan pengecekan stok: ' . $e->getMessage(), [
        'data' => $data,
        'trace' => $e->getTraceAsString()
      ]);
      return LamtimResponse::internalServerError('Gagal memperbarui data.');
    }
  }

  /**
   * Hapus data utama berdasarkan ID terenkripsi dan sesuaikan stok pada repository sekunder.
   *
   * Proses ini dilakukan dalam sebuah transaksi database agar penghapusan data utama dan
   * penyesuaian stok (penambahan kembali stok yang terpakai) terjadi secara atomik.
   *
   * Mekanisme:
   * - Dekripsi ID terenkripsi untuk mendapatkan ID asli.
   * - Ambil data utama berdasarkan ID tersebut.
   * - Jika data utama ditemukan, lakukan penghapusan.
   * - Tambahkan kembali stok pada repository stok sesuai dengan jumlah yang digunakan di data utama.
   *
   * @param string $encryptedId   ID terenkripsi dari data utama yang akan dihapus.
   *
   * @return mixed                Response sukses dari LamtimResponse jika penghapusan berhasil.
   *
   * @throws DecryptException         Jika ID terenkripsi tidak valid atau gagal didekripsi.
   * @throws ModelNotFoundException   Jika data utama atau data stok tidak ditemukan.
   * @throws Exception                Jika terjadi kesalahan lain selama proses penghapusan atau penyesuaian stok.
   */

  public function deleteWithStockAdjustment(string $encryptedId)
  {
    try {
      $id = decrypt($encryptedId);

      DB::beginTransaction();

      if (!$this->secondaryRepo) {
        throw new \Exception("Secondary repository belum di-set");
      }

      // Cari data utama yang akan dihapus
      $data = $this->repo->findById($id);
      if (!$data) {
        throw new ModelNotFoundException("Data dengan ID {$id} tidak ditemukan.");
      }

      // Jika idOdc tidak null, kembalikan stok
      if (!empty($data->idOdc)) {
      // Ambil stok yang dipakai pada data utama (misal portOdc)
      $stockUsed = 1;
      $stockId = $data->idOdc;
      $stockColumn = 'portSisa';

      // Ambil data stok saat ini
      $stockData = $this->secondaryRepo->findById($stockId);
      if (!$stockData) {
        throw new ModelNotFoundException("Data stok dengan ID {$stockId} tidak ditemukan.");
      }

      // Tambahkan kembali stok yang dipakai pada data utama
      $newStock = $stockData->$stockColumn + $stockUsed;
      $this->secondaryRepo->updateStock($stockId, $stockColumn, $newStock);
      }

      // Hapus data utama
      $deleted = $this->repo->deleteById($id);
      if (!$deleted) {
        throw new \Exception("Gagal menghapus data dengan ID {$id}.");
      }

      DB::commit();

      return LamtimResponse::accept('Data berhasil dihapus dan stok disesuaikan.');
    } catch (DecryptException $e) {
      return LamtimResponse::badRequest('ID tidak valid.');
    } catch (ModelNotFoundException $e) {
      return LamtimResponse::notFound($e->getMessage());
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Gagal menghapus data dengan penyesuaian stok: ' . $e->getMessage(), [
        'id' => $encryptedId,
        'trace' => $e->getTraceAsString(),
      ]);
      return LamtimResponse::internalServerError('Gagal menghapus data.');
    }
  }
}
