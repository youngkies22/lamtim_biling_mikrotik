<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Lamtim_odc;
use App\Models\Lamtim_odp;
use App\Models\Lamtim_user_mikrotik_details;
use App\Services\BaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MappingController extends Controller
{

  protected $service;
  /**
   * show page mapping server mikrotik
   * odc odp ont
   */
  public function server()
  {
    return view('content.mapping.mp-server');
  }

  public function storeServer(Request $request)
  {

    $data = $request->all();

    // Cek validitas awal
    if ($data['odp'] === '00' && $data['odc'] === '00') {
      return response()->json([
        'status' => false,
        'message' => 'Harap pilih salah satu antara ODC atau ODP',
      ], 400);
    }

    // Validasi hanya latitude dan longitude (yang pasti dikirim)
    $validated = $request->validate([
      'latitude' => 'required',
      'longitude' => 'required',
    ]);


    if ($data['odp'] !== '00') {
      // Ganti service ke Lamtim_odp
      $this->service = new BaseService(new Lamtim_odp(), null);
      return $this->service->update($validated, $data['odp']);
    } else {
      // Ganti service ke Lamtim_odc
      $this->service = new BaseService(new Lamtim_odc(), null);
      return $this->service->update($validated, $data['odc']);
    }
  }

  public function jsonServer()
  {
    $odcs = Lamtim_odc::select('id', 'nama', 'latitude', 'longitude', 'port', 'portSisa', 'portOlt')->get()->map(function ($item) {
      $item->type = 'ODC';
      return $item;
    });

    $odps = Lamtim_odp::select('id', 'nama', 'latitude', 'longitude', 'idOdc', 'idOdp', 'port', 'portSisa', 'portOdc')->get()->map(function ($item) {
      $item->type = 'ODP';
      return $item;
    });

    return response()->json($odcs->merge($odps));
  }

  /**
   * ambil data user odp odc olt
   * untuk di tampilkan di mapping map
   * berdasarkan id user
   */
  public function jsonGetMapDataByUser($idUser)
  {
    // Ambil detail user beserta relasi OLT, ODC, ODP
    $details = Lamtim_user_mikrotik_details::with([
      'odc:id,latitude,longitude,nama,port,portSisa,portOlt',
      'odp:id,idOdc,idOdp,latitude,longitude,nama,port,portSisa,portOdc',
    ])
      ->where('idUser', decrypt($idUser))
      ->select('id', 'idUser', 'idOlt', 'idOdc', 'idOdp', 'latitude', 'longitude')
      ->get();
    // USER nodes
    $users = $details->map(function ($item) {
      return [
        'id' => $item->id,
        'latitude' => $item->latitude,
        'longitude' => $item->longitude,
        'type' => 'USER',
        'idUser' => $item->idUser,
        'idOdp' => $item->idOdp,

      ];
    });

    // ODP nodes
    $odps = $details->pluck('odp')->filter()->unique('id')->map(function ($odp) use ($details) {
      return [
        'id' => $odp->id,
        'latitude' => $odp->latitude,
        'longitude' => $odp->longitude,
        'nama' => $odp->nama,
        'type' => 'ODP',
        'idOdc' => $odp->idOdc,
        'idOdp' => $odp->idOdp,
        'port' => $odp->port,
        'portSisa' => $odp->portSisa,
        'portOdc' => $odp->portOdc,
      ];
    });

    // ODC nodes
    $odcs = $details->pluck('odc')->filter()->unique('id')->map(function ($odc) {
      return [
        'id' => $odc->id,
        'latitude' => $odc->latitude,
        'longitude' => $odc->longitude,
        'nama' => $odc->nama,
        'type' => 'ODC',
        'port' => $odc->port,
        'portSisa' => $odc->portSisa,
        'portOlt' => $odc->portOlt,
      ];
    });

    // Gabungkan semua node
    $mapData = collect()
      ->merge($odcs)
      ->merge($odps)
      ->merge($users)
      ->values(); // reset index

    return response()->json([
      'status' => true,
      'data' => $mapData
    ]);
  }
  /**
   * ambil data user odp odc olt
   * untuk di tampilkan di mapping map
   * get semua user
   */
  public function jsonGetShowMapUser()
  {
    // Ambil detail user beserta relasi OLT, ODC, ODP
    $details = Lamtim_user_mikrotik_details::with([
      'odc:id,latitude,longitude,nama,port,portSisa,portOlt',
      'odp:id,idOdc,idOdp,latitude,longitude,nama,port,portSisa,portOdc',
      'paket:id,nama',
      'kategori:id,nama',
      'user:id,name,julukan',
    ])->select('id', 'idUser', 'idPaket', 'idKategori', 'idOlt', 'idOdc', 'idOdp', 'latitude', 'longitude')
      ->get();
    
    // USER nodes
    $users = $details->map(function ($item) {
      return [
        'id' => $item->id,
        'latitude' => $item->latitude,
        'longitude' => $item->longitude,
        'type' => 'USER',
        'idUser' => $item->idUser,
        'idOdp' => $item->idOdp,
        'paket' => $item->paket?->nama,
        'kategori' => $item->kategori?->nama,
        'nama' => ($item->user?->julukan ?? '') . ($item->user?->julukan ? '-' : '') . $item->user?->name,
      ];
    });

    // Ambil SEMUA ODP nodes (tidak hanya yang punya user)
    $allOdps = Lamtim_odp::whereNotNull('latitude')
      ->whereNotNull('longitude')
      ->select('id', 'idOdc', 'idOdp', 'latitude', 'longitude', 'nama', 'port', 'portSisa', 'portOdc')
      ->get();
    
    $odps = $allOdps->map(function ($odp) use ($details) {
      $countUser = $details->where('idOdp', $odp->id)->count();
      return [
        'id' => $odp->id,
        'latitude' => $odp->latitude,
        'longitude' => $odp->longitude,
        'nama' => $odp->nama,
        'type' => 'ODP',
        'idOdc' => $odp->idOdc,
        'idOdp' => $odp->idOdp,
        'port' => $odp->port,
        'portSisa' => $odp->portSisa,
        'portOdc' => $odp->portOdc,
        'countUser' => $countUser,
      ];
    });

    // Ambil SEMUA ODC nodes (tidak hanya yang punya user)
    $allOdcs = Lamtim_odc::whereNotNull('latitude')
      ->whereNotNull('longitude')
      ->select('id', 'latitude', 'longitude', 'nama', 'port', 'portSisa', 'portOlt')
      ->get();
    
    $odcs = $allOdcs->map(function ($odc) use ($details) {
      $countUser = $details->where('idOdc', $odc->id)->count();
      // Hitung total ODP yang terhubung ke ODC ini
      $totalOdp = Lamtim_odp::where('idOdc', $odc->id)->count();
      return [
        'id' => $odc->id,
        'latitude' => $odc->latitude,
        'longitude' => $odc->longitude,
        'nama' => $odc->nama,
        'type' => 'ODC',
        'port' => $odc->port,
        'portSisa' => $odc->portSisa,
        'portOlt' => $odc->portOlt,
        'countUser' => $countUser,
        'totalOdp' => $totalOdp,
      ];
    });

    // Gabungkan semua node
    $mapData = collect()
      ->merge($odcs)
      ->merge($odps)
      ->merge($users)
      ->values(); // reset index

    return response()->json([
      'status' => true,
      'data' => $mapData
    ]);
  }
}
