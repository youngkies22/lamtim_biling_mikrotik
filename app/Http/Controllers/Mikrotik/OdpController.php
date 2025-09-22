<?php

namespace App\Http\Controllers\Mikrotik;

use App\Http\Controllers\Controller;
use App\Models\Lamtim_odc;
use App\Models\Lamtim_odp;
use App\Services\BaseService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OdpController extends Controller
{
  protected BaseService $service;

  public function __construct()
  {
    $this->service = new BaseService(new Lamtim_odp(), new Lamtim_odc());
  }
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    return view('content.server.odp');
  }
  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      //'olt' => 'required|numeric',
      'odc' => 'required|numeric',
      'nama' => 'required|string|max:255',
      'kode' => 'required|string|max:255|unique:lamtim_odcs,kode',
      'port' => 'required|numeric',
      'portodc' => 'required|numeric',
      'sisa' => 'required|numeric',
    ]);

    if ((int)$validated['sisa'] > (int)$validated['port']) {
      return response()->json(['message' => 'Port sisa tidak boleh lebih dari total port.'], 422);
    }

    $data = [
      //'idOlt' => $validated['olt'],
      'idOdc' => $validated['odc'],
      'nama' => $validated['nama'],
      'kode' => strtoupper($validated['kode']),
      'port' => (int) $validated['port'],
      'portOdc' => (int) $validated['portodc'],
      'portSisa' => (int) $validated['sisa'],
    ];

    // Panggil service dengan data sudah disiapkan
    return $this->service->storeWithStockCheck($data);
  }


  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    $validated = $request->validate([
      //'olt' => 'required|string',
      'odc' => 'required|string',
      'nama' => 'required|string|max:255',
      'kode' => 'required|string|max:255',
      'port' => 'required|numeric',
      'portodc' => 'required|numeric',
      'sisa' => 'required|numeric',

    ]);
    if ((int)$validated['sisa'] > (int)$validated['port']) {
      return response()->json(['message' => 'Port sisa tidak boleh lebih dari total port.'], 422);
    }
    $data = [
      //'idOlt' => $validated['olt'],
      'idOdc' => $validated['odc'],
      'nama' => $validated['nama'],
      'kode' => strtoupper($validated['kode']),
      'port' => (int) $validated['port'],
      'portOdc' => (int) $validated['portodc'],
      'portSisa' => (int) $validated['port'],

    ];
    return $this->service->updateWithStockCheck($data, $id);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    return $this->service->deleteWithStockAdjustment($id);
  }
  public function json(Request $request)
  {
    $columns = [
      'id',
      'idOlt',
      'idOdc',
      'kode',
      'nama',
      'port',
      'portSisa',
      'portOdc',
      'created_at'
    ];
    $callbacks = [
      'created_at' => function ($row) {
        return Carbon::parse($row->created_at)->format('d-m-Y H:i:s');
      },
      'olt' => function ($row) {
        return optional($row->olt)->nama;
      },
      'odc' => function ($row) {
        return optional($row->odc)->nama;
      },
      'sfp' => function ($row) {
        return optional($row->odc)->portOlt;
      },
      'id' => function ($row) {
        return encrypt($row->id);
      },
    ];
    $with = [
      'olt:id,nama',
      'odc:id,nama,portOlt'
    ];
    return $this->service->getDatatablesJson($columns, $callbacks, $with);
  }
}
