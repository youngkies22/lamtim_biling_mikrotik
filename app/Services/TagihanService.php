<?php

namespace App\Services;

use App\Models\Lamtim_transaksi;
use App\Repositories\TagihanRepository;
use App\Traits\ApiResponseTrait;
use App\Traits\DatabaseErrorHandlerTrait;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\DataTables;
use App\Utils\Logger;

class TagihanService
{
  use ApiResponseTrait, DatabaseErrorHandlerTrait;

  protected TagihanRepository $repository;

  public function __construct(TagihanRepository $repository)
  {
    $this->repository = $repository;
  }

  public function getAll()
  {
    try {
      $data = $this->repository->getAll();
      return $this->successResponse($data, 'Data tagihan berhasil diambil');
    } catch (\Exception $e) {
      return $this->errorResponse('Gagal mengambil data tagihan', [$e->getMessage()]);
    }
  }

  public function deleteByEncryptedId($encryptedId)
  {
    try {
      $id = decrypt($encryptedId);
      $deleted = $this->repository->deleteById($id);

      if ($deleted) {
        return $this->successResponse(null, 'Tagihan berhasil dihapus');
      } else {
        return $this->errorResponse('Tagihan tidak ditemukan atau sudah dihapus', [], 404);
      }
    } catch (\Exception $e) {
      return $this->errorResponse('Gagal menghapus tagihan', [$e->getMessage()]);
    }
  }

  /**
   * Bayar tagihan
   * Menandai tagihan sebagai lunas.
   * Memperbarui status tagihan di database.
   * Mengembalikan respons sukses atau gagal.
   * @param  string  $id
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function bayarTagihan($id, $request)
  {
    $id = decrypt($id);
    // Validasi input
    if (empty($request->metode_bayar)) {
      return $this->errorResponse('Metode bayar harus diisi', [], 400);
    }

    // Cek apakah ada proses pembayaran yang sedang berlangsung untuk tagihan ini
    $lockKey = "payment_process_{$id}";
    // if (Cache::has($lockKey)) {
    //   return $this->errorResponse('Pembayaran sedang diproses, silakan tunggu', [], 423);
    // }

    try {
      // Set lock untuk mencegah double process (2 menit timeout)
      Cache::put($lockKey, true, 60);

      // Mulai database transaction
      DB::beginTransaction();

      // Ambil tagihan dengan locking untuk mencegah race condition
      $tagihan = $this->repository->findByIdWithLock($id);

      if (!$tagihan) {
        DB::rollBack();
        Cache::forget($lockKey);
        return $this->errorResponse('Tagihan tidak ditemukan', [], 404);
      }

      // Cek apakah tagihan sudah dibayar
      if ($tagihan->statusBayar == 1) {
        DB::rollBack();
        Cache::forget($lockKey);
        return $this->errorResponse('Tagihan sudah dibayar sebelumnya', [], 400);
      }

      // Cek apakah sudah ada transaksi untuk tagihan ini
      $existingTransaction = Lamtim_transaksi::where('idTagihan', $tagihan->id)->first();
      if ($existingTransaction) {
        DB::rollBack();
        Cache::forget($lockKey);
        return $this->errorResponse('Transaksi untuk tagihan ini sudah ada', [], 400);
      }

      // Validasi user yang login
      if (!Auth::check()) {
        DB::rollBack();
        Cache::forget($lockKey);
        return $this->errorResponse('User tidak terautentikasi', [], 401);
      }

      // Prepare data transaksi
      $arrayTransaksi = [
        'idUser' => $tagihan->idUser,
        'idTagihan' => $id,
        'metodeBayar' => $request->metode_bayar,
        'namaPaket' => optional($tagihan->paket)->nama ?? 'Paket tidak diketahui',
        'tglBayar' => Carbon::now(),
        'prosesBy' => Auth::user()->id,
        'prosesNama' => Auth::user()->name,
        'harga' => $tagihan->harga,
        'ppn' => $tagihan->ppn ?? 0,
        'diskon' => $tagihan->diskon ?? 0,
        'total' => $tagihan->total,
        'keterangan' => $request->keterangan ?? null,
      ];


      // Validasi data transaksi
      if ($arrayTransaksi['total'] <= 0) {
        DB::rollBack();
        Cache::forget($lockKey);
        return $this->errorResponse('Total tagihan tidak valid', [], 400);
      }

      // Buat transaksi baru
      $transaksi = Lamtim_transaksi::create($arrayTransaksi);

      if (!$transaksi) {
        DB::rollBack();
        Cache::forget($lockKey);
        return $this->errorResponse('Gagal membuat transaksi', [], 500);
      }

      // Update status tagihan
      $updateResult = $tagihan->update([
        'tglBayar' => Carbon::now(),
        'statusBayar' => 1,
        'prosesBy' => Auth::user()->id,
        'prosesNama' => Auth::user()->name,
        'metode' => $request->metode_bayar,
        'aksi' => 'Pembayaran',
        'updated_at' => Carbon::now(),
      ]);

      if (!$updateResult) {
        DB::rollBack();
        Cache::forget($lockKey);
        return $this->errorResponse('Gagal mengupdate status tagihan', [], 500);
      }
      $namaPelanggan = optional($tagihan->user)->name ?? 'Nama tidak diketahui';
      // LOG dengan Utility
      Logger::payment(
        'Pembayaran Berhasil',
        "{$namaPelanggan} - No: {$tagihan->noTagihan}, Metode: {$request->metode_bayar}, Total: Rp " . number_format($tagihan->total)
      );

      // Commit transaction jika semua berhasil
      DB::commit();
      Cache::forget($lockKey);

      // Refresh data tagihan setelah update
      $tagihan->refresh();


      return $this->successResponse([
        'tagihan' => $tagihan,
        'transaksi' => $transaksi
      ], 'Tagihan berhasil dibayar');
    } catch (QueryException $e) {
      DB::rollBack();
      Cache::forget($lockKey);

      return $this->handleDatabaseException($e, 'Gagal memproses pembayaran');
    } catch (\Exception $e) {
      DB::rollBack();
      Cache::forget($lockKey);

      return $this->errorResponse('Gagal membayar tagihan', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
      ], 500);
    }
  }


  /**
   * Batal pembayaran tagihan
   * Mengembalikan status tagihan menjadi belum lunas dan menghapus data transaksi
   *
   * @param string $encryptedId
   * @return \Illuminate\Http\JsonResponse
   */
  public function batalPembayaran($encryptedId)
  {
    $id = decrypt($encryptedId);
    // Cek apakah ada proses pembatalan yang sedang berlangsung
    $lockKey = "cancel_payment_process_{$id}";
    if (Cache::has($lockKey)) {
      return $this->errorResponse('Pembatalan pembayaran sedang diproses, silakan tunggu', [], 423);
    }

    try {
      // Set lock untuk mencegah double process (2 menit timeout)
      Cache::put($lockKey, true, 120);

      // Mulai database transaction
      DB::beginTransaction();

      // Ambil tagihan dengan locking
      $tagihan = $this->repository->findByIdWithLock($id);

      if (!$tagihan) {
        DB::rollBack();
        Cache::forget($lockKey);
        return $this->errorResponse('Tagihan tidak ditemukan', [], 404);
      }

      // Cek apakah tagihan sudah dibayar
      if ($tagihan->statusBayar != 1) {
        DB::rollBack();
        Cache::forget($lockKey);
        return $this->errorResponse('Tagihan ini belum dibayar', [], 400);
      }

      // Validasi user yang login
      if (!Auth::check()) {
        DB::rollBack();
        Cache::forget($lockKey);
        return $this->errorResponse('User tidak terautentikasi', [], 401);
      }

      // Cari transaksi yang terkait dengan tagihan ini
      $transaksi = Lamtim_transaksi::where('idTagihan', $id)->first();

      if (!$transaksi) {
        DB::rollBack();
        Cache::forget($lockKey);
        return $this->errorResponse('Data transaksi tidak ditemukan', [], 404);
      }

      // Hapus data transaksi
      $deleteTransaksi = $transaksi->delete();

      if (!$deleteTransaksi) {
        DB::rollBack();
        Cache::forget($lockKey);
        return $this->errorResponse('Gagal menghapus data transaksi', [], 500);
      }

      // Update status tagihan kembali ke belum lunas
      $updateResult = $tagihan->update([
        'tglBayar' => null,
        'statusBayar' => 0,
        'prosesBy' => Auth::user()->id,
        'prosesNama' => Auth::user()->name,
        'metode' => null,
        'aksi' => 'Pembatalan',
        'updated_at' => Carbon::now()
      ]);

      if (!$updateResult) {
        DB::rollBack();
        Cache::forget($lockKey);
        return $this->errorResponse('Gagal mengupdate status tagihan', [], 500);
      }
      $namaPelanggan = optional($tagihan->user)->name ?? 'Nama tidak diketahui';

      // LOG Payment dengan ID PELANGGAN
      Logger::payment(
        'Batal Pembayaran',
        "{$namaPelanggan} - No: {$tagihan->noTagihan}, Total: Rp " . number_format($tagihan->total),
      );

      // Commit transaction jika semua berhasil
      DB::commit();
      Cache::forget($lockKey);

      // Refresh data tagihan setelah update
      $tagihan->refresh();


      return $this->successResponse([
        'tagihan' => $tagihan,
        'deleted_transaksi_id' => $transaksi->id
      ], 'Pembayaran berhasil dibatalkan. Status tagihan dikembalikan ke belum lunas.');
    } catch (\Illuminate\Database\QueryException $e) {
      DB::rollBack();
      Cache::forget($lockKey);

      return $this->handleDatabaseException($e, 'Gagal membatalkan pembayaran');
    } catch (\Exception $e) {
      DB::rollBack();
      Cache::forget($lockKey);

      return $this->errorResponse('Gagal membatalkan pembayaran', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
      ], 500);
    }
  }

  /**
   * Generate Tagihan Bulanan
   * Menghasilkan tagihan bulanan untuk semua pengguna berdasarkan paket yang mereka gunakan.
   * Memastikan bahwa tagihan tidak digandakan untuk bulan yang sama.
   * Menghitung total tagihan termasuk pajak dan diskon.
   * Menyimpan tagihan ke database.
   */
  public function generateTagihanBulanan()
  {
    try {
      $result = $this->repository->generateTagihanBulananRepo();
      return $this->successResponse(
        $result,
        'Generate tagihan bulanan selesai'
      );
    } catch (\Exception $e) {
      return $this->errorResponse('Terjadi error saat generate tagihan bulanan', [$e->getMessage()]);
    }
  }

  /**
   * Query untuk datatables
   * Memproses request dari datatables dan mengembalikan data dalam format yang sesuai.
   * Mendukung filter, sorting, dan pagination.
   * Menggunakan Yajra DataTables untuk mempermudah proses ini.
   * Menyediakan data yang sudah diformat untuk ditampilkan di frontend.
   *
   */
  public function getDatatablesJson($request)
  {
    try {
      $where = [];
      // filter bulan + status bayar
      if (!empty($request->bulan)) {
        $where['bulan'] = $request->bulan;
      }
      $where['statusBayar'] = $request->status_bayar ?? 0;

      $query = $this->repository->getDatatablesJson($where);

      // ✅ Hitung summary data SEBELUM DataTables processing
      $summaryQuery = clone $query;
      $summaryData = [
        'total_count' => $summaryQuery->count(),
        'total_unpaid' => $summaryQuery->sum('total'),
        'total_harga' => $summaryQuery->sum('harga'),
        'total_ppn' => $summaryQuery->sum('ppn'),
        'total_diskon' => $summaryQuery->sum('diskon'),
      ];
      $summaryData['potential_income'] = $summaryData['total_unpaid'];
      $summaryData['average_amount'] = $summaryData['total_count'] > 0 ?
        $summaryData['total_unpaid'] / $summaryData['total_count'] : 0;

      // ✅ TAMBAHAN: Untuk halaman tagihan lunas, ambil juga data tagihan belum bayar
      $unpaidSummary = [];
      if ($request->status_bayar == 1) { // Jika request untuk tagihan lunas
        $unpaidWhere = [];

        // Filter bulan yang sama jika ada
        if (!empty($request->bulan)) {
          $unpaidWhere['bulan'] = $request->bulan;
        }

        // Set status belum bayar
        $unpaidWhere['statusBayar'] = 0; // Ambil yang belum bayar

        $unpaidQuery = $this->repository->getDatatablesJson($unpaidWhere);
        $unpaidSummary = [
          'unpaid_count' => $unpaidQuery->count(),
          'unpaid_total' => $unpaidQuery->sum('total'),
          'unpaid_harga' => $unpaidQuery->sum('harga'),
          'unpaid_ppn' => $unpaidQuery->sum('ppn'),
          'unpaid_diskon' => $unpaidQuery->sum('diskon'),
        ];
      }

      $dataTable = DataTables::of($query)
        ->addColumn('paket_nama', function ($row) {
          return optional($row->paket)->nama;
        })
        ->addColumn('paket_harga', function ($row) {
          return optional($row->paket)->price;
        })
        ->editColumn('created_at', function ($row) {
          return $row->created_at
            ? Carbon::parse($row->created_at)->format('d-m-Y H:i:s')
            : '-';
        })
        ->editColumn('status_bayar', function ($row) {
          return $row->statusBayar == 1 ? 'paid' : 'unpaid';
        })
        ->rawColumns(['paket_nama', 'paket_harga'])
        ->only([
          'id',
          'noTagihan',
          'bulan',
          'tahun',
          'status_bayar',
          'harga',
          'ppn',
          'diskon',
          'total',
          'tglJatuhTempo',
          'tglBayar',
          'prosesNama',
          'metode',
          'create_by',
          'updated_at',
          'created_at',
          'paket_nama',
          'paket_harga'
        ])
        // ✅ Tambahkan summary data + unpaid summary ke response DataTables
        ->with([
          'summary' => $summaryData,
          'unpaid_summary' => $unpaidSummary, // Data tagihan belum bayar
          'filter_info' => [
            'bulan' => $request->bulan,
            'tahun' => date('Y'),
            'status_bayar' => $request->status_bayar ?? 0,
            'has_unpaid_data' => !empty($unpaidSummary) // Flag untuk frontend
          ]
        ])
        ->make(true);

      return $dataTable;
    } catch (\Exception $e) {
      return response()->json([
        'draw' => intval($request->input('draw', 0)),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        // ✅ Summary kosong untuk error case
        'summary' => [
          'total_count' => 0,
          'total_unpaid' => 0,
          'potential_income' => 0,
          'total_harga' => 0,
          'total_ppn' => 0,
          'total_diskon' => 0,
          'average_amount' => 0
        ],
        'unpaid_summary' => [
          'unpaid_count' => 0,
          'unpaid_total' => 0,
          'unpaid_harga' => 0,
          'unpaid_ppn' => 0,
          'unpaid_diskon' => 0,
        ],
        'filter_info' => [
          'bulan' => $request->bulan ?? null,
          'tahun' => date('Y'),
          'status_bayar' => $request->status_bayar ?? 0,
          'has_unpaid_data' => false
        ],
        'success' => false,
        'message' => 'Gagal mengambil data datatables',
        'error'   => $e->getMessage(),
      ], 500);
    }
  }
}
