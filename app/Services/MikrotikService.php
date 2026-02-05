<?php

/**
 * Tempat untuk menuliskan logika bisnis, pengolahan data, dan koordinasi antara repository dan controller.
 * Memanggil repository dan olah datanya
 * Menyusun aturan bisnis (misalnya: validasi manual, perhitungan)
 * Bisa juga trigger event, queue, dsb.
 */

namespace App\Services;

use App\Models\Lamtim_mikrotik;
use App\Repositories\MikrotikRepository;
use App\SendRespon\LamtimResponse;
use App\Services\MikrotikMulti;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use \RouterOS\Client;
use \RouterOS\Query;

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

  /**
   * Cek koneksi ke MikroTik server.
   */
  public function checkConnection(int $idMikrotik): array
  {
    try {
      $client = MikrotikMulti::connectionMikrotik($idMikrotik);
      $query = new Query('/system/identity/print');
      $response = $client->query($query)->read();

      $identity = $response[0]['name'] ?? 'Unknown';

      return [
        'connected' => true,
        'message'   => "Terhubung ke {$identity}",
        'identity'  => $identity,
      ];
    } catch (\Exception $e) {
      Log::error("Gagal koneksi Mikrotik ID {$idMikrotik}: " . $e->getMessage());
      return [
        'connected' => false,
        'message'   => 'Gagal terhubung: ' . $e->getMessage(),
        'identity'  => null,
      ];
    }
  }

  /**
   * Ambil semua PPPoE secrets dari MikroTik server.
   */
  public function getPppoeUsers(int $idMikrotik): array
  {
    try {
      $data = MikrotikMulti::commandApi($idMikrotik, '/ppp/secret/print', ['service' => 'pppoe']);
      return ['success' => true, 'data' => $data];
    } catch (\Exception $e) {
      Log::error("Gagal ambil PPPoE users dari Mikrotik ID {$idMikrotik}: " . $e->getMessage());
      return ['success' => false, 'data' => [], 'message' => $e->getMessage()];
    }
  }

  /**
   * Disable PPPoE secret di MikroTik dan disconnect sesi aktif.
   */
  public function disablePppoeUser(int $idMikrotik, string $pppoeUsername): array
  {
    try {
      $client = MikrotikMulti::connectionMikrotik($idMikrotik);

      // Cari .id secret
      $findQuery = new Query('/ppp/secret/print');
      $findQuery->where('name', $pppoeUsername);
      $secrets = $client->query($findQuery)->read();

      if (empty($secrets)) {
        return ['success' => false, 'message' => "PPPoE user '{$pppoeUsername}' tidak ditemukan."];
      }

      $secretId = $secrets[0]['.id'];

      // Disable secret
      $setQuery = new Query('/ppp/secret/set');
      $setQuery->equal('.id', $secretId);
      $setQuery->equal('disabled', 'yes');
      $client->query($setQuery)->read();

      // Disconnect sesi aktif
      $this->removeActivePppSession($client, $pppoeUsername);

      Log::info("PPPoE user '{$pppoeUsername}' berhasil di-disable pada Mikrotik ID {$idMikrotik}.");
      return ['success' => true, 'message' => "PPPoE user '{$pppoeUsername}' berhasil di-disable."];
    } catch (\Exception $e) {
      Log::error("Gagal disable PPPoE '{$pppoeUsername}': " . $e->getMessage());
      return ['success' => false, 'message' => 'Gagal disable: ' . $e->getMessage()];
    }
  }

  /**
   * Enable PPPoE secret di MikroTik.
   */
  public function enablePppoeUser(int $idMikrotik, string $pppoeUsername): array
  {
    try {
      $client = MikrotikMulti::connectionMikrotik($idMikrotik);

      // Cari .id secret
      $findQuery = new Query('/ppp/secret/print');
      $findQuery->where('name', $pppoeUsername);
      $secrets = $client->query($findQuery)->read();

      if (empty($secrets)) {
        return ['success' => false, 'message' => "PPPoE user '{$pppoeUsername}' tidak ditemukan."];
      }

      $secretId = $secrets[0]['.id'];

      // Enable secret
      $setQuery = new Query('/ppp/secret/set');
      $setQuery->equal('.id', $secretId);
      $setQuery->equal('disabled', 'no');
      $client->query($setQuery)->read();

      Log::info("PPPoE user '{$pppoeUsername}' berhasil di-enable pada Mikrotik ID {$idMikrotik}.");
      return ['success' => true, 'message' => "PPPoE user '{$pppoeUsername}' berhasil di-enable."];
    } catch (\Exception $e) {
      Log::error("Gagal enable PPPoE '{$pppoeUsername}': " . $e->getMessage());
      return ['success' => false, 'message' => 'Gagal enable: ' . $e->getMessage()];
    }
  }

  /**
   * Remove sesi PPP aktif (disconnect user).
   */
  private function removeActivePppSession(Client $client, string $pppoeUsername): void
  {
    try {
      $findActive = new Query('/ppp/active/print');
      $findActive->where('name', $pppoeUsername);
      $actives = $client->query($findActive)->read();

      if (!empty($actives)) {
        $removeQuery = new Query('/ppp/active/remove');
        $removeQuery->equal('.id', $actives[0]['.id']);
        $client->query($removeQuery)->read();
      }
    } catch (\Exception $e) {
      Log::warning("Gagal remove active session '{$pppoeUsername}': " . $e->getMessage());
    }
  }
}
