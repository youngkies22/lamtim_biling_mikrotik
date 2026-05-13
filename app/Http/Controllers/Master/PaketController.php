<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Lamtim_address_list;
use App\Models\Lamtim_ip_pool;
use App\Models\Lamtim_paket;
use App\Services\BaseService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaketController extends Controller
{

  protected BaseService $service;

  public function __construct()
  {
    $this->service = new BaseService(new Lamtim_paket(), null);
  }
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $ipPools = Lamtim_ip_pool::all();
    $addressLists = Lamtim_address_list::all();
    return view('content.kategori_paket.paket', compact('ipPools', 'addressLists'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create() {}

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'idKategori'  => 'required|numeric',
      'kode'        => 'required|string|max:255',
      'nama'        => 'required|string|max:255',
      'price'       => 'required|numeric', // 'price' seharusnya numeric (integer atau float)
      //'picture'     => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 'picture' lebih baik divalidasi sebagai file gambar
      'description' => 'required|string',
      'isActive'    => 'required|boolean',
      'speed_limit' => 'nullable|string|max:50',
      'ip_pool'     => 'nullable|string|max:50',
      'address_list'=> 'nullable|string|max:100',
      'is_burst'    => 'nullable|boolean',
      'burst_rate'  => 'nullable|string|max:50',
      'burst_threshold' => 'nullable|string|max:50',
      'burst_time'  => 'nullable|string|max:50',
      'priority'    => 'nullable|integer|min:1|max:8',
    ]);
    return $this->service->store($validated);
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id) {}

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id) {}

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    $validated = $request->validate([
      'idKategori'  => 'required|numeric',
      'kode'        => 'required|string|max:255',
      'nama'        => 'required|string|max:255',
      'price'       => 'required|numeric', // 'price' seharusnya numeric (integer atau float)
      //'picture'     => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 'picture' lebih baik divalidasi sebagai file gambar
      'description' => 'required|string',
      'speed_limit' => 'nullable|string|max:50',
      'ip_pool'     => 'nullable|string|max:50',
      'address_list'=> 'nullable|string|max:100',
      'is_burst'    => 'nullable|boolean',
      'burst_rate'  => 'nullable|string|max:50',
      'burst_threshold' => 'nullable|string|max:50',
      'burst_time'  => 'nullable|string|max:50',
      'priority'    => 'nullable|integer|min:1|max:8',
    ]);
    return $this->service->update($validated, $id);
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
      'idKategori',
      'kode',
      'nama',
      'price',
      'description',
      'isActive',
      'speed_limit',
      'ip_pool',
      'address_list',
      'is_burst',
      'burst_rate',
      'burst_threshold',
      'burst_time',
      'priority',
      'created_at',
      'updated_at',
    ];
    $callbacks = [
      'created_at' => function ($row) {
        return Carbon::parse($row->created_at)->format('d-m-Y H:i:s');
      },
      'id' => function ($row) {
        return encrypt($row->id);
      },
      'kategori' => function ($row) {
        return $row->kategori->nama;
      },


    ];
    $with = [
      'kategori:id,nama',
      // 'odc:id,nama,portOlt'
    ];
    return $this->service->getDatatablesJson($columns, $callbacks, $with);
  }
}
