<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Repositories\TagihanRepository;
use App\Services\TagihanService;
use Illuminate\Http\Request;


class TagihanController extends Controller
{
  protected TagihanService $service;

  public function __construct()
  {
    $this->service = new TagihanService(new TagihanRepository());
  }
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    return view('content.tagihan.tagihan');
  }

  public function json(Request $request)
  {
    return $this->service->getDatatablesJson($request);
  }

  public function generateTagihanBulanan()
  {
    return $this->service->generateTagihanBulanan();
  }
  public function lunas()
  {
    return view('content.tagihan.tagihan_lunas');
  }
  /**
   * Bayar tagihan
   * Menandai tagihan sebagai lunas.
   * Memperbarui status tagihan di database.
   * Mengembalikan respons sukses atau gagal.
   */
  public function bayar($id, Request $request)
  {
    return $this->service->bayarTagihan($id, $request);
  }
  /**
   * Batal pembayaran tagihan
   */
  public function batalPembayaran($encryptedId)
  {
    return $this->service->batalPembayaran($encryptedId);
  }
  public function destroy(string $id)
  {
    return $this->service->deleteByEncryptedId($id);
  }

  /**
   * Menampilkan detail tagihan tertentu.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    try {
      $tagihan = app(TagihanRepository::class)->findById($id);

      if (!$tagihan) {
        return response()->json([
          'success' => false,
          'message' => 'Tagihan tidak ditemukan',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Detail tagihan berhasil diambil',
        'data' => $tagihan
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal mengambil detail tagihan: ' . $e->getMessage(),
        'data' => null
      ], 500);
    }
  }
}
