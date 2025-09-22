<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Lamtim_kategori;
use App\Services\BaseService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KategoriController extends Controller
{

  protected BaseService $service;

  public function __construct()
  {
    $this->service = new BaseService(new Lamtim_kategori(), null);
  }
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    return view('content.kategori_paket.kategori');
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
      'nama' => 'required|string|max:255',
      'description' => 'required|string',
      'isActive' => 'required|numeric',
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
      'nama' => 'required|string|max:255',
      'description' => 'required|string',
      'isActive' => 'required|numeric',
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
      'nama',
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
    ];
    $with = [
      // 'olt:id,nama',
      // 'odc:id,nama,portOlt'
    ];
    return $this->service->getDatatablesJson($columns, $callbacks, $with);
  }
}
