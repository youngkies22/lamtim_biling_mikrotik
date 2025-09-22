<?php

namespace App\Http\Controllers\Mikrotik;

use App\Http\Controllers\Controller;
use App\Models\Lamtim_odc;
use App\Services\BaseService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OdcController extends Controller
{
  protected BaseService $service;

  public function __construct()
  {
    $this->service = new BaseService(new Lamtim_odc());
  }
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    return view('content.server.odc');
  }


  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {

    $validated = $request->validate([
      'olt' => 'required|string',
      'nama' => 'required|string|max:255',
      'kode' => 'required|string|max:255|unique:lamtim_odcs,kode',
      'port' => 'required|numeric',
      'sisa' => 'required|numeric',
      'portolt' => 'required|numeric',
    ]);
    if ((int)$validated['sisa'] > (int)$validated['port']) {
      return response()->json(['message' => 'Port sisa tidak boleh lebih dari total port.'], 422);
    }
    $data = [
      'idOlt' => $validated['olt'],
      'nama' => $validated['nama'],
      'kode' => strtoupper($validated['kode']),
      'port' => (int) $validated['port'],
      'portSisa' => (int) $validated['sisa'], #untuk + - jika port sudah terpakai oleh odp
      'portOlt' => (int) $validated['portolt'],
    ];
    return $this->service->store($data);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    $validated = $request->validate([
      'olt' => 'required|string',
      'nama' => 'required|string|max:255',
      'kode' => 'required|string|max:255',
      'port' => 'required|numeric',
      'sisa' => 'required|numeric',
      'portolt' => 'required|numeric',
    ]);
    if ((int)$validated['sisa'] > (int)$validated['port']) {
      return response()->json(['message' => 'Port sisa tidak boleh lebih dari total port.'], 422);
    }
    $data = [
      'idOlt' => $validated['olt'],
      'nama' => $validated['nama'],
      'kode' => strtoupper($validated['kode']),
      'port' => (int) $validated['port'],
      'portSisa' => (int) $validated['port'],
      'portOlt' => (int) $validated['portolt'],

    ];
    return $this->service->update($data, $id);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    return $this->service->deleteByEncryptedId($id);
  }
  public function json(Request $request)
  {
    $columns = [
      'id',
      'idOlt',
      'kode',
      'nama',
      'port',
      'portSisa',
      'portOlt',
      'created_at'
    ];
    $callbacks = [
      'created_at' => function ($row) {
        return Carbon::parse($row->created_at)->format('d-m-Y H:i:s');
      },
      'olt' => function ($row) {
        return optional($row->olt)->nama;
      },
      'id' => function ($row) {
        return encrypt($row->id);
      },
    ];
    $with = [
      'olt:id,nama' // hanya ambil kolom id dan nama dari relasi olt
    ];
    return $this->service->getDatatablesJson($columns, $callbacks, $with);
  }
}
