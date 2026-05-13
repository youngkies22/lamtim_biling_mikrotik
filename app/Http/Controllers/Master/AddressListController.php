<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Lamtim_address_list;
use App\Models\Lamtim_mikrotik;
use App\Services\BaseService;
use App\Services\MikrotikMulti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AddressListController extends Controller
{
    protected BaseService $service;

    public function __construct()
    {
        $this->service = new BaseService(new Lamtim_address_list(), null);
    }

    public function index()
    {
        $mikrotiks = Lamtim_mikrotik::where('isActive', 1)->get();
        return view('content.address_list.index', compact('mikrotiks'));
    }

    public function json(Request $request)
    {
        $columns = ['id', 'name', 'description', 'created_at'];
        $callbacks = [
            'id' => function($row) {
                return encrypt($row->id);
            }
        ];
        return $this->service->getDatatablesJson($columns, $callbacks);
    }

    public function pull(Request $request)
    {
        $request->validate([
            'idMikrotik' => 'required|numeric'
        ]);

        try {
            // Mengambil semua address list yang ada di router
            // Kita ambil nama unik saja agar tidak ribet
            $lists = MikrotikMulti::commandApi2($request->idMikrotik, '/ip/firewall/address-list/print');

            if (!$lists) {
                return response()->json(['success' => false, 'message' => 'Gagal terhubung ke MikroTik atau data kosong.'], 400);
            }

            // Ekstrak nama unik menggunakan koleksi Laravel (Sangat efisien)
            $uniqueNames = collect($lists)->pluck('list')->unique()->filter();

            $count = 0;
            foreach ($uniqueNames as $name) {
                Lamtim_address_list::updateOrCreate(
                    ['name' => $name],
                    ['description' => 'Ditarik dari MikroTik']
                );
                $count++;
            }

            return response()->json([
                'success' => true, 
                'message' => "Berhasil menyinkronkan $count kategori Address List dari MikroTik."
            ]);

        } catch (\Exception $e) {
            Log::error('AddressList Pull Error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        return $this->service->deleteByEncryptedId($id);
    }
}
