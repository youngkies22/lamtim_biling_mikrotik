<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
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
    return view('content.kategori_paket.paket');
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
      'isActive'    => 'required|boolean', // 'isActive' seharusnya boolean (true/false, 0/1)
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
      'isActive'    => 'required|boolean', // 'isActive' seharusnya boolean (true/false, 0/1)
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
