<?php

namespace App\Http\Controllers\Mikrotik;

use App\Http\Controllers\Controller;
use App\Models\Lamtim_mikrotik;
use App\Models\User;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class MikrotikController extends Controller
{
  protected $mikrotikService;

  // Profile PPPoE untuk isolir (bisa disesuaikan)
  const ISOLIR_PROFILE = 'ISOLIR';

  public function __construct(MikrotikService $mikrotikService)
  {
    $this->mikrotikService = $mikrotikService;
  }
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    return view('content.server.mikrotik');
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'nama' => 'required|string|max:255',
      'kode' => 'required|string|max:255',
      'username' => 'required|string',
      'password' => 'required|string',
      'ip' => 'required|ip',
      'port' => 'required|numeric',
      'isActive' => 'required|boolean',
    ]);
    return $this->mikrotikService->store($validated);
  }
  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    $validated = $request->validate([
      'nama' => 'required|string|max:255',
      'kode' => 'required|string|max:255',
      'username' => 'required|string',
      'password' => 'required|string',
      'ip' => 'required|ip',
      'port' => 'required|numeric',
      'isActive' => 'required|boolean',
    ]);
    return $this->mikrotikService->update($validated, $id);
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id) {}



  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    return $this->mikrotikService->deleteByEncryptedId($id);
  }
  public function json(Request $request)
  {
    return $this->mikrotikService->getDatatablesJson($request);
  }

  // === Cek Koneksi ===

  public function connectionCheck()
  {
    return view('content.server.connection-check');
  }

  public function testConnection(int $id)
  {
    $result = $this->mikrotikService->checkConnection($id);
    return response()->json(['error' => !$result['connected'], 'data' => $result]);
  }

  public function testAllConnections()
  {
    $servers = Lamtim_mikrotik::where('isActive', 1)->get();
    $results = [];

    foreach ($servers as $server) {
      $check = $this->mikrotikService->checkConnection($server->id);
      $results[] = [
        'id'        => $server->id,
        'nama'      => $server->nama,
        'ip'        => $server->ip,
        'port'      => $server->port,
        'connected' => $check['connected'],
        'message'   => $check['message'],
        'identity'  => $check['identity'] ?? null,
      ];
    }

    return response()->json(['error' => false, 'data' => $results]);
  }

  // === Kelola PPPoE ===

  public function pppoeManage()
  {
    return view('content.server.pppoe-manage');
  }

  public function getPppoeUsers(int $idMikrotik)
  {
    $result = $this->mikrotikService->getPppoeUsers($idMikrotik);

    if (!$result['success']) {
      return response()->json([
        'error'   => true,
        'message' => 'Gagal mengambil data PPPoE: ' . ($result['message'] ?? ''),
      ], 500);
    }

    return response()->json(['error' => false, 'data' => $result['data']]);
  }

  public function disablePppoe(Request $request)
  {
    $validated = $request->validate([
      'idMikrotik' => 'required|integer',
      'username'   => 'required|string',
    ]);

    $result = $this->mikrotikService->disablePppoeUser($validated['idMikrotik'], $validated['username']);
    return response()->json(['error' => !$result['success'], 'message' => $result['message']], $result['success'] ? 200 : 422);
  }

  public function enablePppoe(Request $request)
  {
    $validated = $request->validate([
      'idMikrotik' => 'required|integer',
      'username'   => 'required|string',
    ]);

    $result = $this->mikrotikService->enablePppoeUser($validated['idMikrotik'], $validated['username']);
    return response()->json(['error' => !$result['success'], 'message' => $result['message']], $result['success'] ? 200 : 422);
  }

  // === Kelola Isolir ===

  public function kelolaIsolir()
  {
    return view('content.server.kelola-isolir');
  }

  public function getIsolirData(Request $request)
  {
    try {
      $query = User::with(['user_detail', 'user_mikrotik.mikrotik', 'user_mikrotik.paket', 'user_mikrotik.kategori'])
        ->whereHas('user_mikrotik')
        ->select('users.*');

      // Filter by status isolir (dari lamtim_user_mikrotik_details)
      if ($request->filled('status')) {
        $status = (int) $request->status;
        $query->whereHas('user_mikrotik', function ($q) use ($status) {
          $q->where('statusIsolir', $status);
        });
      }

      // Filter by mikrotik
      if ($request->filled('mikrotik')) {
        $mikrotikId = (int) $request->mikrotik;
        $query->whereHas('user_mikrotik', function ($q) use ($mikrotikId) {
          $q->where('idMikrotik', $mikrotikId);
        });
      }

      // Search
      if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
          $q->where('name', 'like', "%{$search}%")
            ->orWhere('wa', 'like', "%{$search}%")
            ->orWhereHas('user_mikrotik', function ($q2) use ($search) {
              $q2->where('namaMikrotikUser', 'like', "%{$search}%");
            });
        });
      }

      // Clone query untuk stats sebelum pagination
      $statsQuery = clone $query;

      // Pagination
      $perPage = $request->get('per_page', 20);
      $page = $request->get('page', 1);

      $users = $query->orderBy('name')->paginate($perPage, ['*'], 'page', $page);

      $data = $users->getCollection()->map(function ($user) {
        $detail = $user->user_detail;
        $mikrotik = $user->user_mikrotik;

        return [
          'id' => $user->id,
          'name' => $user->name,
          'wa' => $user->wa,
          'pppoe_username' => $mikrotik->namaMikrotikUser ?? '-',
          'mikrotik_id' => $mikrotik->idMikrotik ?? null,
          'mikrotik_nama' => $mikrotik->mikrotik->nama ?? '-',
          'paket' => $mikrotik->paket->nama ?? '-',
          'kategori' => $mikrotik->kategori->nama ?? '-',
          'status_isolir' => $mikrotik->statusIsolir ?? 0,
          'jatuh_tempo' => $detail->tglJatuhTempo ?? null,
          'tgl_isolir' => $mikrotik->tglDiIsolir ?? null,
          'tgl_buka' => $mikrotik->tglDiBuka ?? null,
          'profile_mikrotik' => $mikrotik->profileMikrotikUser ?? '-',
        ];
      })->values();

      // Statistics (dari semua data, bukan hanya halaman saat ini)
      $allUsers = $statsQuery->get();
      $totalUsers = $allUsers->count();
      $totalIsolir = $allUsers->filter(function ($user) {
        return ($user->user_mikrotik->statusIsolir ?? 0) == 1;
      })->count();
      $totalAktif = $totalUsers - $totalIsolir;

      return response()->json([
        'error' => false,
        'data' => $data,
        'stats' => [
          'total' => $totalUsers,
          'isolir' => $totalIsolir,
          'aktif' => $totalAktif,
        ],
        'pagination' => [
          'current_page' => $users->currentPage(),
          'last_page' => $users->lastPage(),
          'per_page' => $users->perPage(),
          'total' => $users->total(),
          'from' => $users->firstItem(),
          'to' => $users->lastItem(),
        ]
      ]);
    } catch (\Exception $e) {
      Log::error('Error getting isolir data: ' . $e->getMessage());
      return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
    }
  }

  public function isolirUser(Request $request)
  {
    $validated = $request->validate([
      'user_id' => 'required|integer',
    ]);

    DB::beginTransaction();
    try {
      $user = User::with(['user_detail', 'user_mikrotik.mikrotik'])->find($validated['user_id']);

      if (!$user) {
        return response()->json(['error' => true, 'message' => 'User tidak ditemukan'], 404);
      }

      // Ganti profile PPPoE ke profile ISOLIR di Mikrotik
      $mikrotik = $user->user_mikrotik;

      if ($mikrotik && $mikrotik->idMikrotik && $mikrotik->namaMikrotikUser) {
        $result = $this->mikrotikService->isolirPppoeUser(
          $mikrotik->idMikrotik,
          $mikrotik->namaMikrotikUser,
          self::ISOLIR_PROFILE
        );

        if (!$result['success']) {
          DB::rollBack();
          return response()->json(['error' => true, 'message' => 'Gagal isolir di Mikrotik: ' . $result['message']], 422);
        }
      }

      // Update status isolir in database (lamtim_user_mikrotik_details)
      if ($mikrotik) {
        $mikrotik->update([
          'statusIsolir' => 1,
          'tglDiIsolir' => now(),
        ]);
      }

      DB::commit();
      return response()->json(['error' => false, 'message' => "User {$user->name} berhasil diisolir (profile diubah ke " . self::ISOLIR_PROFILE . ")"]);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error isolir user: ' . $e->getMessage());
      return response()->json(['error' => true, 'message' => 'Gagal isolir: ' . $e->getMessage()], 500);
    }
  }

  public function aktifkanUser(Request $request)
  {
    $validated = $request->validate([
      'user_id' => 'required|integer',
    ]);

    DB::beginTransaction();
    try {
      $user = User::with(['user_detail', 'user_mikrotik.mikrotik'])->find($validated['user_id']);

      if (!$user) {
        return response()->json(['error' => true, 'message' => 'User tidak ditemukan'], 404);
      }

      $mikrotik = $user->user_mikrotik;

      // Ambil profile asli dari profileMikrotikUser di lamtim_user_mikrotik_details
      $originalProfile = $mikrotik->profileMikrotikUser ?? 'default';

      // Kembalikan profile PPPoE ke profile asli di Mikrotik
      if ($mikrotik && $mikrotik->idMikrotik && $mikrotik->namaMikrotikUser) {
        $result = $this->mikrotikService->aktifkanPppoeUser(
          $mikrotik->idMikrotik,
          $mikrotik->namaMikrotikUser,
          $originalProfile
        );

        if (!$result['success']) {
          DB::rollBack();
          return response()->json(['error' => true, 'message' => 'Gagal aktifkan di Mikrotik: ' . $result['message']], 422);
        }
      }

      // Update status isolir in database (lamtim_user_mikrotik_details)
      if ($mikrotik) {
        $mikrotik->update([
          'statusIsolir' => 0,
          'tglDiBuka' => now(),
        ]);
      }

      DB::commit();
      return response()->json(['error' => false, 'message' => "User {$user->name} berhasil diaktifkan (profile dikembalikan ke {$originalProfile})"]);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error aktifkan user: ' . $e->getMessage());
      return response()->json(['error' => true, 'message' => 'Gagal aktifkan: ' . $e->getMessage()], 500);
    }
  }

  public function bulkIsolir(Request $request)
  {
    $validated = $request->validate([
      'user_ids' => 'required|array',
      'user_ids.*' => 'integer',
    ]);

    $success = 0;
    $failed = 0;
    $errors = [];

    foreach ($validated['user_ids'] as $userId) {
      try {
        $user = User::with(['user_detail', 'user_mikrotik.mikrotik'])->find($userId);

        if (!$user) {
          $failed++;
          continue;
        }

        // Ganti profile PPPoE ke profile ISOLIR di Mikrotik
        $mikrotik = $user->user_mikrotik;

        if ($mikrotik && $mikrotik->idMikrotik && $mikrotik->namaMikrotikUser) {
          $result = $this->mikrotikService->isolirPppoeUser(
            $mikrotik->idMikrotik,
            $mikrotik->namaMikrotikUser,
            self::ISOLIR_PROFILE
          );

          if (!$result['success']) {
            $errors[] = "{$user->name}: {$result['message']}";
            $failed++;
            continue;
          }
        }

        // Update status isolir in database (lamtim_user_mikrotik_details)
        if ($mikrotik) {
          $mikrotik->update([
            'statusIsolir' => 1,
            'tglDiIsolir' => now(),
          ]);
        }

        $success++;
      } catch (\Exception $e) {
        $failed++;
        $errors[] = "User ID {$userId}: " . $e->getMessage();
      }
    }

    return response()->json([
      'error' => false,
      'message' => "Berhasil isolir {$success} user" . ($failed > 0 ? ", gagal {$failed} user" : ""),
      'success' => $success,
      'failed' => $failed,
      'errors' => $errors,
    ]);
  }

  public function bulkAktifkan(Request $request)
  {
    $validated = $request->validate([
      'user_ids' => 'required|array',
      'user_ids.*' => 'integer',
    ]);

    $success = 0;
    $failed = 0;
    $errors = [];

    foreach ($validated['user_ids'] as $userId) {
      try {
        $user = User::with(['user_detail', 'user_mikrotik.mikrotik'])->find($userId);

        if (!$user) {
          $failed++;
          continue;
        }

        $mikrotik = $user->user_mikrotik;

        // Ambil profile asli dari profileMikrotikUser di lamtim_user_mikrotik_details
        $originalProfile = $mikrotik->profileMikrotikUser ?? 'default';

        // Kembalikan profile PPPoE ke profile asli di Mikrotik
        if ($mikrotik && $mikrotik->idMikrotik && $mikrotik->namaMikrotikUser) {
          $result = $this->mikrotikService->aktifkanPppoeUser(
            $mikrotik->idMikrotik,
            $mikrotik->namaMikrotikUser,
            $originalProfile
          );

          if (!$result['success']) {
            $errors[] = "{$user->name}: {$result['message']}";
            $failed++;
            continue;
          }
        }

        // Update status isolir in database (lamtim_user_mikrotik_details)
        if ($mikrotik) {
          $mikrotik->update([
            'statusIsolir' => 0,
            'tglDiBuka' => now(),
          ]);
        }

        $success++;
      } catch (\Exception $e) {
        $failed++;
        $errors[] = "User ID {$userId}: " . $e->getMessage();
      }
    }

    return response()->json([
      'error' => false,
      'message' => "Berhasil aktifkan {$success} user" . ($failed > 0 ? ", gagal {$failed} user" : ""),
      'success' => $success,
      'failed' => $failed,
      'errors' => $errors,
    ]);
  }
}
