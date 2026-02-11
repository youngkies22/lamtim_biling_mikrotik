<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Repositories\TagihanRepository;
use App\Services\TagihanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


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
    // Ambil data filter dari database dengan cache 1 bulan
    $filterData = Cache::remember('tagihan_filter_data', now()->addMonth(), function () {
      $tahun = DB::table('lamtim_tagihans')
        ->select('tahun')
        ->distinct()
        ->orderBy('tahun', 'desc')
        ->pluck('tahun')
        ->toArray();

      $bulan = DB::table('lamtim_tagihans')
        ->select('bulan')
        ->distinct()
        ->orderBy('bulan', 'asc')
        ->pluck('bulan')
        ->toArray();

      return [
        'tahun' => $tahun,
        'bulan' => $bulan
      ];
    });

    return view('content.tagihan.tagihan', compact('filterData'));
  }

  public function json(Request $request)
  {
    return $this->service->getDatatablesJson($request);
  }

  public function generate()
  {
    $currentMonth = (int) date('n');
    $currentYear  = (int) date('Y');

    $pelangganAktif = DB::table('users')
      ->where('isActive', 1)
      ->whereIn('idRole', [5])
      ->count();

    $sudahGenerate = DB::table('lamtim_tagihans')
      ->where('bulan', $currentMonth)
      ->where('tahun', $currentYear)
      ->count();

    $belumBayar = DB::table('lamtim_tagihans')
      ->where('bulan', $currentMonth)
      ->where('tahun', $currentYear)
      ->where('statusBayar', 0)
      ->count();

    $sudahBayar = DB::table('lamtim_tagihans')
      ->where('bulan', $currentMonth)
      ->where('tahun', $currentYear)
      ->where('statusBayar', 1)
      ->count();

    $stats = compact('pelangganAktif', 'sudahGenerate', 'belumBayar', 'sudahBayar');

    return view('content.tagihan.generate', compact('stats'));
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
