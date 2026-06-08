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

    $currentMonth = (int) date('n');
    $currentYear  = (int) date('Y');

    return view('content.tagihan.tagihan', compact('filterData', 'currentMonth', 'currentYear'));
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
      ->whereExists(function ($q) {
        $q->select(DB::raw(1))
          ->from('lamtim_user_mikrotik_details')
          ->whereColumn('lamtim_user_mikrotik_details.idUser', 'users.id')
          ->where('statusIsolir', 0);
      })
      ->whereExists(function ($q) {
        $q->select(DB::raw(1))
          ->from('lamtim_user_details')
          ->whereColumn('lamtim_user_details.idUser', 'users.id')
          ->where('statusTagihan', 1);
      })
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

  public function rekap()
  {
    $tahunDipilih = (int) request('tahun', date('Y'));

    $listTahun = DB::table('lamtim_tagihans')
      ->select('tahun')
      ->distinct()
      ->orderBy('tahun', 'desc')
      ->pluck('tahun')
      ->toArray();

    if (!in_array($tahunDipilih, $listTahun)) {
      $listTahun = array_unique(array_merge([$tahunDipilih], $listTahun));
      rsort($listTahun);
    }

    $rows = DB::table('lamtim_tagihans')
      ->where('tahun', $tahunDipilih)
      ->selectRaw('
        bulan,
        COUNT(*) as total_tagihan,
        SUM(total) as grand_total,
        SUM(CASE WHEN statusBayar = 0 THEN 1 ELSE 0 END) as belum_bayar_count,
        SUM(CASE WHEN statusBayar = 0 THEN total ELSE 0 END) as belum_bayar_total,
        SUM(CASE WHEN statusBayar = 1 THEN 1 ELSE 0 END) as sudah_bayar_count,
        SUM(CASE WHEN statusBayar = 1 THEN total ELSE 0 END) as sudah_bayar_total
      ')
      ->groupBy('bulan')
      ->get()
      ->keyBy('bulan');

    $namaBulan = [
      1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
      5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
      9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $rekapBulan = [];
    foreach ($namaBulan as $no => $nama) {
      $row = $rows->get($no);
      $rekapBulan[] = [
        'no'               => $no,
        'nama'             => $nama,
        'total_tagihan'    => $row->total_tagihan ?? 0,
        'grand_total'      => $row->grand_total ?? 0,
        'belum_bayar_count'=> $row->belum_bayar_count ?? 0,
        'belum_bayar_total'=> $row->belum_bayar_total ?? 0,
        'sudah_bayar_count'=> $row->sudah_bayar_count ?? 0,
        'sudah_bayar_total'=> $row->sudah_bayar_total ?? 0,
      ];
    }

    return view('content.tagihan.rekap', compact('rekapBulan', 'listTahun', 'tahunDipilih'));
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
