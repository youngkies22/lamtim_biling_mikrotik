<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MikrotikMulti;

use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{
  protected $service;

  public function __construct(UserService $service)
  {
    $this->service = $service;
  }
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    return view('content.user.index');
  }
  public function mapping()
  {
    return view('content.user.mapping');
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    return view('content.user.add');
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {

    $validated = $request->validate([
      'nama' => ['required', 'string', 'max:100'],
      'wa' => ['required', 'string', 'max:100'],
      'password' => ['nullable', 'string', 'min:8'], // jika dikosongkan, default di-handle di controller
      'area' => ['required', 'string'],
      'status_isolir' => ['required', 'in:0,1'],
      'tgl_daftar' => ['required', 'date'],
      //'jatuh_tempo' => ['required', 'date', 'after_or_equal:tgl_daftar'],
      'jatuh_tempo' => ['required', 'date'],
      'status_ppn' => ['required', 'in:0,1'],
      'status_tagihan' => ['required', 'in:0,1'],
      'jenis_bayar' => ['required', 'in:1,2'],
      'google_map_url' => ['nullable', 'url'],
      'jenis_kelamin' => ['required', 'in:L,P'],
      //'telegram' => ['nullable', 'string', 'max:100'],
      'jenis_identitas' => ['required', 'in:KTP,KK,SIM,PASPOR,KARTU_PELAJAR,KARTU_MAHASISWA,NPWP,BPJS,LAINNYA'],
      'no_identitas' => ['required', 'string', 'max:100'],
      'alamat' => ['nullable', 'string'],
      'keterangan' => ['nullable', 'string'],
    ]);

    if (empty($validated['password'])) {
      $validated['password'] = '123456789abc';
    }
    $validated['password'] = bcrypt($validated['password']);

    $arataArray = [
      'name'          => $validated['nama'],
      'wa'            => $validated['wa'],
      'password'      => $validated['password'],
      'idRole'        => 5,
    ];
    $arataArrayDetail = [
      'idArea'        => decrypt($validated['area']),
      'tglDafatar'    => $validated['tgl_daftar'],
      'tglJatuhTempo' => $validated['jatuh_tempo'],
      'statusPpn'     => $validated['status_ppn'],
      'statusTagihan' => $validated['status_tagihan'],
      'jenisBayar'    => $validated['jenis_bayar'],
      'googleMap'     => $validated['google_map_url'],
      'js'            => $validated['jenis_kelamin'],
      //'telegram'      => $validated['telegram'],
      // 'fotoProfile'  => $validated['area'],
      // 'email'  => $validated['area'],
      // 'qrcode'  => $validated['area'],
      'identitas'     => $validated['jenis_identitas'],
      'noIdentitas'   => $validated['no_identitas'],
      'alamat'        => $validated['alamat'],
      'keterangan'    => $validated['keterangan'],
    ];
    return $this->service->storeUserAndUserDetail($arataArray, $arataArrayDetail);
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    //
  }

  /**
   * Show foto page for user
   */
  public function showFoto(string $id)
  {
    $query = User::with('fotos')->find(decrypt($id));
    return view('content.user.foto', compact('id', 'query'));
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    $query = User::with(['user_detail', 'user_mikrotik'])->find(decrypt($id));
    return view('content.user.edit', compact('id', 'query'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {

    $validated = $request->validate([
      'nama' => ['required', 'string', 'max:100'],
      'julukan' => ['nullable', 'string', 'max:100'],
      'status_user' => ['required', 'in:0,1'],
      'wa' => ['required', 'string', 'max:100'],
      'password' => ['nullable', 'string', 'min:8'], // jika dikosongkan, default di-handle di controller
      'area' => ['required', 'string'],
      'status_isolir' => ['required', 'in:0,1'],
      'tgl_daftar' => ['required', 'date'],
      //'jatuh_tempo' => ['required', 'date', 'after_or_equal:tgl_daftar'],
      'jatuh_tempo' => ['required', 'date'],
      'status_ppn' => ['required', 'in:0,1'],
      'status_tagihan' => ['required', 'in:0,1'],
      'jenis_bayar' => ['required', 'in:1,2'],
      'google_map_url' => ['nullable', 'url'],
      'jenis_kelamin' => ['required', 'in:L,P'],
      //'telegram' => ['nullable', 'string', 'max:100'],
      'jenis_identitas' => ['required', 'in:KTP,KK,SIM,PASPOR,KARTU_PELAJAR,KARTU_MAHASISWA,NPWP,BPJS,LAINNYA'],
      'no_identitas' => ['required', 'string', 'max:100'],
      'alamat' => ['nullable', 'string'],
      'keterangan' => ['nullable', 'string'],
    ]);
    if (!empty($validated['password'])) {
      $validated['password'] = bcrypt($validated['password']);
    } else {
      unset($validated['password']); // jangan kirim ke update
    }

    $arataArray = [
      'name'          => $validated['nama'],
      'julukan'       => $validated['julukan'],
      'isActive'      => $validated['status_user'],
      'wa'            => $validated['wa'],
      'idRole'        => 5,
    ];
    // Kalau password ADA isinya, hash dan tambahkan ke array update
    if (!empty($validated['password'])) {
      $arataArray['password'] = bcrypt($validated['password']);
    }
    $arataArrayDetail = [
      'idArea'        => decrypt($validated['area']),
      'tglDafatar'    => $validated['tgl_daftar'],
      'tglJatuhTempo' => $validated['jatuh_tempo'],
      'statusPpn'     => $validated['status_ppn'],
      'statusTagihan' => $validated['status_tagihan'],
      'jenisBayar'    => $validated['jenis_bayar'],
      'googleMap'     => $validated['google_map_url'],
      'js'            => $validated['jenis_kelamin'],
      //'telegram'      => $validated['telegram'],
      // 'fotoProfile'  => $validated['area'],
      // 'email'  => $validated['area'],
      // 'qrcode'  => $validated['area'],
      'identitas'     => $validated['jenis_identitas'],
      'noIdentitas'   => $validated['no_identitas'],
      'alamat'        => $validated['alamat'],
      'keterangan'    => $validated['keterangan'],
    ];
    $decryptedId = decrypt($id);
    $response = $this->service->updateUserMikrotik($arataArray, $arataArrayDetail, $decryptedId);

    // Update statusIsolir di tabel lamtim_user_mikrotik_details
    \App\Models\Lamtim_user_mikrotik_details::where('idUser', $decryptedId)
      ->update(['statusIsolir' => $validated['status_isolir']]);

    return $response;
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
    $columns = ['id', 'idRole', 'name', 'email', 'wa', 'created_at'];

    $callbacks = [
      'created_at' => fn($row) => Carbon::parse($row->created_at)->format('d-m-Y H:i:s'),
      'id' => fn($row) => encrypt($row->id),
    ];
    $with = [
      'user_detail:id,idUser,idArea,tglDafatar,tglJatuhTempo,statusPpn,statusTagihan,jenisBayar,googleMap,js,identitas,noIdentitas,alamat,keterangan',
      'user_mikrotik.mikrotik:id,nama',
      'user_mikrotik.paket:id,nama',
      'fotos:id,idPelanggan,foto,extensi,ukuran,created_at'
    ];
    $where = ['idRole' => 5];

    $query = \App\Models\User::select($columns)->with($with)->where($where)->get();
    $datatable = \Yajra\DataTables\Facades\DataTables::of($query);

    // Tambahkan callback untuk kolom yang ada
    if (!empty($callbacks)) {
      foreach ($callbacks as $column => $callback) {
        $datatable->editColumn($column, $callback);
      }
    }

    // Tambahkan kolom virtual
    $datatable->addColumn('tglDafatar', function ($row) {
      return $row->user_detail ? Carbon::parse($row->user_detail->tglDafatar)->format('d-m-Y') : '-';
    });

    $datatable->addColumn('mikrotik', function ($row) {
      if ($row->user_mikrotik && $row->user_mikrotik->mikrotik) {
        return $row->user_mikrotik->mikrotik->nama ?? '-';
      }
      return '<span class="text-muted">-</span>';
    });

    $datatable->addColumn('paket', function ($row) {
      if ($row->user_mikrotik && $row->user_mikrotik->paket) {
        return $row->user_mikrotik->paket->nama ?? '-';
      }
      return '<span class="text-muted">-</span>';
    });

    $datatable->addColumn('googleMap', function ($row) {
      if ($row->user_detail && $row->user_detail->googleMap) {
        return '<a href="' . e($row->user_detail->googleMap) . '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-map-marker"></i> Lihat</a>';
      }
      return '<span class="text-muted">-</span>';
    });

    $datatable->addColumn('foto', function ($row) {
      $hasFoto = $row->fotos && $row->fotos->count() > 0;
      $encryptedId = encrypt($row->id);
      if ($hasFoto) {
        return '<a href="/user/' . e($encryptedId) . '/foto" class="btn btn-sm btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
          <i class="mdi mdi-camera"></i>
        </a>';
      }
      return '<span class="text-muted">-</span>';
    });

    return $datatable->rawColumns(['mikrotik', 'paket', 'googleMap', 'foto'])->make(true);
  }

  #mapping -------------------------------------------------------------------------------------------
  public function jsonMapping()
  {
    $columns = ['id', 'idRole', 'name', 'email', 'wa', 'created_at'];

    $callbacks = [
      'created_at' => fn($row) => Carbon::parse($row->created_at)->format('d-m-Y H:i:s'),
      'id' => fn($row) => encrypt($row->id),
    ];
    $with = [
      'user_detail:id,idUser,tglDafatar,tglJatuhTempo',
    ];
    $where = ['idRole' => 5];
    return $this->service->getDatatablesJson($columns, $callbacks, $with, $where);
  }
  /**
   * Tampilkan halaman edit mapping untuk user tertentu berdasarkan ID.
   *
   * @param  int  $id  ID user yang ingin diedit mapping-nya
   * @return \Illuminate\View\View
   */
  public function editMapping($id)
  {
    $query = User::with(['user_mikrotik', 'user_detail'])->find(decrypt($id));
    
    // Prepare data untuk JavaScript (hindari PHP di blade)
    $dariDb = $query->user_mikrotik->namaMikrotikUser ?? '';
    $hasUserMikrotik = $query->user_mikrotik?->id !== null;
    $hasNamaMikrotikUser = !empty($query->user_mikrotik?->namaMikrotikUser);
    
    return view('content.mapping.mp-user', compact('id', 'query', 'dariDb', 'hasUserMikrotik', 'hasNamaMikrotikUser'));
  }

  /**
   * Menyimpan data mapping user berdasarkan input form AJAX.
   *
   * Method ini menerima data dari request berbasis AJAX, melakukan validasi input,
   * menyimpan data ke dalam database (jika valid), dan mengembalikan respons JSON.
   * Jika terjadi kesalahan validasi atau error server, akan mengembalikan respon
   * dengan kode status dan pesan yang sesuai.
   *
   * @param  \Illuminate\Http\Request  $request  Objek request yang berisi data form
   * @return \Illuminate\Http\JsonResponse       Respon JSON berisi status dan pesan hasil penyimpanan
   */
  public function storeMapping(Request $request): JsonResponse
  {
    //dd($request);
    try {
      $aksi = $request->input('aksi');

      $rules = [
        'aksi'       => 'required|in:1,2',
        'idd'        => 'required|string',
        'kategori'   => 'required|not_in:00',
        'paket'      => 'required|not_in:00',
        'idmikrotik' => 'required|not_in:00',
        'odp'        => 'required|not_in:00',
        'latitude'   => 'nullable|string',
        'longitude'  => 'nullable|string',
        'id_olt'     => 'nullable|integer',
        'id_odc'     => 'nullable|integer',
        'port'       => 'nullable|integer',
      ];

      #jika 1 berati dia mode manual, dan harus insert juga ke api mikrotik
      if ($aksi == '1') {
        // Manual input
        $rules['secretapi'] = 'required|string';
        $rules['password']  = 'required|string';
      } elseif ($aksi == '2') {
        // Ambil dari API
        $rules['secretapi']     = 'nullable|string';
        $rules['id_api']        = 'required|string';
        $rules['service_api']   = 'required|string';
        $rules['profile_api']   = 'required|string';
        $rules['password']      = 'required|string';
      }

      $validated = $request->validate($rules);
      $odp = $this->service->getOdpOdcOlt($validated['odp']);
      $idd = $validated['idd'];
      $dataArray = [
        'idUser'              => decrypt($idd),
        'idKategori'          => $validated['kategori'],
        'idPaket'             => $validated['paket'],
        'idMikrotik'          => $validated['idmikrotik'],
        'idOlt'               => $odp->idOlt ?? null,
        'idOdc'               => $odp->idOdc ?? null,
        'idOdp'               => $validated['odp'],
        'portOdp'             => $validated['port'],
        'latitude'            => $validated['latitude'] ?? null,
        'longitude'           => $validated['longitude'] ?? null,
      ];
      $paket = $this->service->findPaket($validated['paket']);
      // Tambahkan isian mikrotik berdasarkan aksi
      if ($aksi == '1') {
        // Manual input
        //$dataArray['idMikrotikUser']      = $validated['idd'];
        //$dataArray['namaMikrotikUser']    = $validated['secretapi'];

        if (!empty($validated['secretapi'])) {
          $dataArray['namaMikrotikUser'] = $validated['secretapi'];
        }
        $dataArray['serviceMikrotikUser'] = 'pppoe';
        $dataArray['profileMikrotikUser'] = $paket->kode;
        $dataArray['password']            = $validated['password'] ?? null;
      } elseif ($aksi == '2') {
        // Dari API
        $dataArray['idMikrotikUser']      = $validated['id_api'] ?? null;
        if (!empty($validated['secretapi'])) {
          $dataArray['namaMikrotikUser']  = $validated['secretapi'];
        }
        //$dataArray['namaMikrotikUser']    = $validated['secretapi'];
        $dataArray['serviceMikrotikUser'] = $validated['service_api'];
        $dataArray['profileMikrotikUser'] = $validated['profile_api'];
        $dataArray['password']            = $validated['password'] ?? null;
      }
      $find = $this->service->exisUserMikrotikByIdUser($idd);
      $messageApi  = '';
      if ($aksi == '1') {
        /**
         * proses isert data ke api mikrotik
         */
        $arraySecret = [
          'name'     => $validated['secretapi'],
          'password' => $validated['password'],
          'service'  => 'pppoe',
          'profile'  => $paket->kode,
          'disabled' => 'false', #no Aktif → pengguna bisa login PPPoE, yes Nonaktif → pengguna tidak bisa login meskipun username dan password
          'comment'  => 'oleh sistem ' . now()->format('Y-m-d H:i:s'),
        ];

        $responseApi = MikrotikMulti::commandApi2($validated['idmikrotik'], '/ppp/secret/add', $arraySecret);

        if (is_array($responseApi) && isset($responseApi['after']['ret']) && !empty($responseApi['after']['ret'])) {
          $messageApi = 'PPP secret berhasil ditambahkan.';
          Log::info($messageApi, [
            'ret_id'  => $responseApi['after']['ret'],
            'command' => '/ppp/secret/add',
            'data'    => $validated
          ]);

          $dataArray['idMikrotikUser'] = $responseApi['after']['ret'];  // Tambahkan ke dataArray
        } else {
          $messageApi = '❌ Gagal menambahkan PPP secret ke Mikrotik.';
          Log::warning($messageApi, [
            'response' => $responseApi,
            'command'  => '/ppp/secret/add',
            'data'     => $validated
          ]);
        }
      }
      if ($find) {
        return $this->service->updateMikrotikUserWhere($dataArray, $idd, $messageApi);
      } else {
        return $this->service->storeUserMikrotik($dataArray, $messageApi);
      }
    } catch (\Throwable $e) {
      // Log error untuk debugging
      Log::error('Gagal menyimpan mapping: ' . $e->getMessage());

      return response()->json([
        'status' => false,
        'message' => 'Terjadi kesalahan pada server',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function showMapping()
  {
    return view('content.mapping.mp-show-user');
  }
}
