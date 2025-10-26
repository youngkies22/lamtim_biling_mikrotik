<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\AreaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AreaController extends Controller
{
  protected $service;

  public function __construct(AreaService $service)
  {
    $this->service = $service;
  }

  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    return view('content.area.index');
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    try {
      // Validasi input
      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'address' => 'required|string|max:500',
        'code_area' => 'required|string|max:10|unique:lamtim_areas,code_area',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Validation failed',
          'errors' => $validator->errors()
        ], 422);
      }

      // Simpan data melalui service
      $area = $this->service->create($request->all());

      return response()->json([
        'success' => true,
        'message' => 'Area berhasil ditambahkan',
        'data' => $area
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal menambahkan area: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    try {
      // Decrypt ID
      $decryptedId = decrypt($id);

      // Validasi input
      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'address' => 'required|string|max:500',
        'code_area' => 'required|string|max:10|unique:lamtim_areas,code_area,' . $decryptedId,
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Validation failed',
          'errors' => $validator->errors()
        ], 422);
      }

      // Update data melalui service
      $area = $this->service->update($decryptedId, $request->all());

      return response()->json([
        'success' => true,
        'message' => 'Area berhasil diupdate',
        'data' => $area
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal mengupdate area: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    try {
      // Decrypt ID
      $decryptedId = decrypt($id);

      // Hapus data melalui service
      $this->service->delete($decryptedId);

      return response()->json([
        'success' => true,
        'message' => 'Area berhasil dihapus'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal menghapus area: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Mengembalikan data area dalam format JSON untuk DataTables.
   */
  public function json(Request $request)
  {
    return $this->service->getDatatablesJson($request);
  }
}
