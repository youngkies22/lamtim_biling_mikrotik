<?php

/**
 * Tempat untuk menuliskan logika bisnis, pengolahan data, dan koordinasi antara repository dan controller.
 * Memanggil repository dan olah datanya
 * Menyusun aturan bisnis (misalnya: validasi manual, perhitungan)
 * Bisa juga trigger event, queue, dsb.
 */

namespace App\Services;

use App\Models\Lamtim_mikrotik;
use App\Models\Lamtim_user_details;
use App\Models\Lamtim_user_mikrotik_details;
use App\Models\User;
use App\Repositories\MikrotikRepository;
use App\SendRespon\LamtimResponse;
use App\Services\MikrotikMulti;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

  /**
   * Ambil profile PPPoE user saat ini dari MikroTik.
   */
  public function getPppoeUserProfile(int $idMikrotik, string $pppoeUsername): array
  {
    try {
      $client = MikrotikMulti::connectionMikrotik($idMikrotik);

      $findQuery = new Query('/ppp/secret/print');
      $findQuery->where('name', $pppoeUsername);
      $secrets = $client->query($findQuery)->read();

      if (empty($secrets)) {
        return ['success' => false, 'message' => "PPPoE user '{$pppoeUsername}' tidak ditemukan."];
      }

      return [
        'success' => true,
        'profile' => $secrets[0]['profile'] ?? 'default',
        'data' => $secrets[0]
      ];
    } catch (\Exception $e) {
      Log::error("Gagal ambil profile PPPoE '{$pppoeUsername}': " . $e->getMessage());
      return ['success' => false, 'message' => 'Gagal ambil profile: ' . $e->getMessage()];
    }
  }

  /**
   * Ganti profile PPPoE user di MikroTik.
   */
  public function setPppoeProfile(int $idMikrotik, string $pppoeUsername, string $profileName): array
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
      $oldProfile = $secrets[0]['profile'] ?? 'default';

      // Set profile baru
      $setQuery = new Query('/ppp/secret/set');
      $setQuery->equal('.id', $secretId);
      $setQuery->equal('profile', $profileName);
      $client->query($setQuery)->read();

      Log::info("PPPoE user '{$pppoeUsername}' profile diubah dari '{$oldProfile}' ke '{$profileName}' pada Mikrotik ID {$idMikrotik}.");
      return [
        'success' => true,
        'message' => "Profile berhasil diubah ke '{$profileName}'.",
        'old_profile' => $oldProfile
      ];
    } catch (\Exception $e) {
      Log::error("Gagal ubah profile PPPoE '{$pppoeUsername}': " . $e->getMessage());
      return ['success' => false, 'message' => 'Gagal ubah profile: ' . $e->getMessage()];
    }
  }

  /**
   * Isolir user - ganti profile ke profile isolir dan disconnect.
   */
  public function isolirPppoeUser(int $idMikrotik, string $pppoeUsername, string $isolirProfile = 'ISOLIR'): array
  {
    try {
      $client = MikrotikMulti::connectionMikrotik($idMikrotik);

      // Cari .id secret dan profile saat ini
      $findQuery = new Query('/ppp/secret/print');
      $findQuery->where('name', $pppoeUsername);
      $secrets = $client->query($findQuery)->read();

      if (empty($secrets)) {
        return ['success' => false, 'message' => "PPPoE user '{$pppoeUsername}' tidak ditemukan."];
      }

      $secretId = $secrets[0]['.id'];
      $originalProfile = $secrets[0]['profile'] ?? 'default';

      // Jangan isolir jika sudah di profile isolir
      if (strtoupper($originalProfile) === strtoupper($isolirProfile)) {
        return ['success' => false, 'message' => "User sudah dalam status isolir."];
      }

      // Set profile ke isolir
      $setQuery = new Query('/ppp/secret/set');
      $setQuery->equal('.id', $secretId);
      $setQuery->equal('profile', $isolirProfile);
      $client->query($setQuery)->read();

      // Disconnect sesi aktif agar profile baru berlaku
      $this->removeActivePppSession($client, $pppoeUsername);

      Log::info("PPPoE user '{$pppoeUsername}' berhasil diisolir (profile: {$originalProfile} -> {$isolirProfile}) pada Mikrotik ID {$idMikrotik}.");
      return [
        'success' => true,
        'message' => "User '{$pppoeUsername}' berhasil diisolir.",
        'original_profile' => $originalProfile
      ];
    } catch (\Exception $e) {
      Log::error("Gagal isolir PPPoE '{$pppoeUsername}': " . $e->getMessage());
      return ['success' => false, 'message' => 'Gagal isolir: ' . $e->getMessage()];
    }
  }

  /**
   * Aktifkan user - kembalikan profile ke profile asli dan disconnect untuk refresh.
   */
  public function aktifkanPppoeUser(int $idMikrotik, string $pppoeUsername, string $originalProfile): array
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

      // Set profile kembali ke profile asli
      $setQuery = new Query('/ppp/secret/set');
      $setQuery->equal('.id', $secretId);
      $setQuery->equal('profile', $originalProfile);
      $client->query($setQuery)->read();

      // Disconnect sesi aktif agar profile baru berlaku
      $this->removeActivePppSession($client, $pppoeUsername);

      Log::info("PPPoE user '{$pppoeUsername}' berhasil diaktifkan (profile dikembalikan ke: {$originalProfile}) pada Mikrotik ID {$idMikrotik}.");
      return [
        'success' => true,
        'message' => "User '{$pppoeUsername}' berhasil diaktifkan dengan profile '{$originalProfile}'."
      ];
    } catch (\Exception $e) {
      Log::error("Gagal aktifkan PPPoE '{$pppoeUsername}': " . $e->getMessage());
      return ['success' => false, 'message' => 'Gagal aktifkan: ' . $e->getMessage()];
    }
  }

  /**
   * Fetch PPPoE secrets dari Mikrotik dan bandingkan dengan database.
   */
  public function fetchSecretsForSync(int $idMikrotik): array
  {
    try {
      $secrets = MikrotikMulti::commandApi($idMikrotik, '/ppp/secret/print', ['service' => 'pppoe']);

      // Ambil semua user mikrotik detail yang terhubung ke server ini (match by nama secret saja)
      $existingUsers = Lamtim_user_mikrotik_details::where('idMikrotik', $idMikrotik)
        ->get()
        ->keyBy('namaMikrotikUser');

      $result = [];
      foreach ($secrets as $secret) {
        $name = $secret['name'] ?? '';
        $apiId = $secret['.id'] ?? '';

        // Match by namaMikrotikUser saja (id API bisa tabrakan antar server)
        $existing = $existingUsers->get($name);

        $result[] = [
          'mikrotik_id'    => $apiId,
          'name'           => $name,
          'password'       => $secret['password'] ?? '',
          'service'        => $secret['service'] ?? 'pppoe',
          'profile'        => $secret['profile'] ?? 'default',
          'remote_address' => $secret['remote-address'] ?? '',
          'disabled'       => ($secret['disabled'] ?? 'false') === 'true',
          'comment'        => $secret['comment'] ?? '',
          'status'         => $existing ? 'existing' : 'new',
          'db_id'          => $existing ? $existing->id : null,
          'db_user_id'     => $existing ? $existing->idUser : null,
          'db_user_name'   => $existing ? ($existing->user->name ?? '-') : null,
        ];
      }

      return ['success' => true, 'data' => $result];
    } catch (\Exception $e) {
      Log::error("Gagal fetch secrets untuk sync dari Mikrotik ID {$idMikrotik}: " . $e->getMessage());
      return ['success' => false, 'data' => [], 'message' => $e->getMessage()];
    }
  }

  /**
   * Eksekusi sinkronisasi: update existing, create new.
   */
  public function executeSyncCustomers(int $idMikrotik, array $secrets): array
  {
    $created = 0;
    $updated = 0;
    $failed = 0;
    $errors = [];

    foreach ($secrets as $secret) {
      try {
        if ($secret['status'] === 'existing' && !empty($secret['db_id'])) {
          // Update existing user mikrotik details
          $mikrotikDetail = Lamtim_user_mikrotik_details::find($secret['db_id']);
          if ($mikrotikDetail) {
            $mikrotikDetail->update([
              'idMikrotikUser'       => $secret['mikrotik_id'],
              'namaMikrotikUser'     => $secret['name'],
              'serviceMikrotikUser'  => $secret['service'],
              'profileMikrotikUser'  => $secret['profile'],
              'password'             => $secret['password'],
            ]);
            $updated++;
          } else {
            $failed++;
            $errors[] = "{$secret['name']}: Data mikrotik detail tidak ditemukan (ID: {$secret['db_id']})";
          }
        } elseif ($secret['status'] === 'new') {
          // Buat user baru dalam transaksi
          DB::transaction(function () use ($secret, $idMikrotik, &$created) {
            // 1. Create User
            $user = User::create([
              'name'     => $secret['name'],
              'email'    => $secret['name'] . '@sync.local',
              'password' => Hash::make($secret['password'] ?: 'password123'),
              'idRole'   => 5,
              'isActive' => 1,
              'wa'       => '-',
            ]);

            // 2. Create user details
            Lamtim_user_details::create([
              'idUser'       => $user->id,
              'tglDafatar'   => now()->format('Y-m-d'),
              'statusPpn'    => 0,
              'statusTagihan'=> 0,
              'jenisBayar'   => 1,
            ]);

            // 3. Create user mikrotik details
            Lamtim_user_mikrotik_details::create([
              'idUser'              => $user->id,
              'idMikrotik'          => $idMikrotik,
              'idMikrotikUser'      => $secret['mikrotik_id'],
              'namaMikrotikUser'    => $secret['name'],
              'serviceMikrotikUser' => $secret['service'],
              'profileMikrotikUser' => $secret['profile'],
              'password'            => $secret['password'],
            ]);

            $created++;
          });
        }
      } catch (\Exception $e) {
        $failed++;
        $errors[] = "{$secret['name']}: " . $e->getMessage();
        Log::error("Sync error for secret '{$secret['name']}': " . $e->getMessage());
      }
    }

    return [
      'success' => true,
      'created' => $created,
      'updated' => $updated,
      'failed'  => $failed,
      'errors'  => $errors,
    ];
  }
}
