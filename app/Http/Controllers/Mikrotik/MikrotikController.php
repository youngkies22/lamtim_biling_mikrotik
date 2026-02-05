<?php

namespace App\Http\Controllers\Mikrotik;

use App\Http\Controllers\Controller;
use App\Models\Lamtim_mikrotik;
use App\Services\MikrotikService;
use Illuminate\Http\Request;


class MikrotikController extends Controller
{
  protected $mikrotikService;

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
}
