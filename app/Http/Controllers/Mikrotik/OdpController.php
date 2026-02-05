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
      'odc' => 'required_without:odp_parent|nullable|numeric',
      'nama' => 'required|string|max:255',
      'kode' => 'required|string|max:255|unique:lamtim_odcs,kode',
      'port' => 'required|numeric',
      'portodc' => 'required_with:odc|nullable|numeric',
      'sisa' => 'required|numeric',
      'odp_parent' => 'nullable|numeric',
    ]);

    if ((int)$validated['sisa'] > (int)$validated['port']) {
      return response()->json(['message' => 'Port sisa tidak boleh lebih dari total port.'], 422);
    }

    $data = [
      //'idOlt' => $validated['olt'],
      'idOdc' => !empty($validated['odc']) ? $validated['odc'] : null,
      'idOdp' => !empty($validated['odp_parent']) ? (int) $validated['odp_parent'] : null,
      'nama' => $validated['nama'],
      'kode' => strtoupper($validated['kode']),
      'port' => (int) $validated['port'],
      'portOdc' => !empty($validated['portodc']) ? (int) $validated['portodc'] : null,
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
      'odc' => 'required_without:odp_parent|nullable|string',
      'nama' => 'required|string|max:255',
      'kode' => 'required|string|max:255',
      'port' => 'required|numeric',
      'portodc' => 'required_with:odc|nullable|numeric',
      'sisa' => 'required|numeric',
      'odp_parent' => 'nullable|numeric',

    ]);
    if ((int)$validated['sisa'] > (int)$validated['port']) {
      return response()->json(['message' => 'Port sisa tidak boleh lebih dari total port.'], 422);
    }
    $data = [
      //'idOlt' => $validated['olt'],
      'idOdc' => !empty($validated['odc']) ? $validated['odc'] : null,
      'idOdp' => !empty($validated['odp_parent']) ? (int) $validated['odp_parent'] : null,
      'nama' => $validated['nama'],
      'kode' => strtoupper($validated['kode']),
      'port' => (int) $validated['port'],
      'portOdc' => !empty($validated['portodc']) ? (int) $validated['portodc'] : null,
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
      'idOdp',
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
        // Hanya tampilkan ODC jika ada, jika ada ODP Parent kosongkan
        if ($row->odc) {
          return $row->odc->nama;
        }
        return '<span class="text-muted">-</span>';
      },
      'portOdc' => function ($row) {
        // Hanya tampilkan portOdc jika ada, jika ada ODP Parent kosongkan
        if ($row->portOdc) {
          return $row->portOdc;
        }
        return '<span class="text-muted">-</span>';
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
      'odc:id,nama,portOlt',
      'parentOdp:id,nama'
    ];
    
    // Gunakan query builder (bukan get()) untuk server-side processing
    $query = Lamtim_odp::select($columns)->with($with);
    
    // Buat DataTable instance dengan query builder
    $datatable = \Yajra\DataTables\Facades\DataTables::of($query);
    
    // Tambahkan callback untuk kolom yang ada
    if (!empty($callbacks)) {
      foreach ($callbacks as $column => $callback) {
        $datatable->editColumn($column, $callback);
      }
    }
    
    // Tambahkan kolom virtual odpParent
    $datatable->addColumn('odpParent', function ($row) {
      if ($row->parentOdp) {
        return '<span class="badge bg-label-primary">' . e($row->parentOdp->nama) . '</span>';
      }
      return '<span class="text-muted">-</span>';
    });
    
    // Set raw columns agar HTML ter-render dengan benar
    $datatable->rawColumns(['odc', 'portOdc', 'odpParent']);
    
    return $datatable->make(true);
  }
}
