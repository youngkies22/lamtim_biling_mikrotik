<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Lamtim_ip_pool;
use App\Models\Lamtim_mikrotik;
use App\Services\BaseService;
use App\Services\MikrotikMulti;
use Illuminate\Http\Request;

class IpPoolController extends Controller
{
    protected BaseService $service;

    public function __construct()
    {
        $this->service = new BaseService(new Lamtim_ip_pool(), null);
    }

    public function index()
    {
        $mikrotiks = Lamtim_mikrotik::where('isActive', 1)->get();
        return view('content.ip_pool.index', compact('mikrotiks'));
    }

    public function json(Request $request)
    {
        $columns = ['id', 'name', 'ranges', 'next_pool', 'created_at'];
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

        $pools = MikrotikMulti::commandApi2($request->idMikrotik, '/ip/pool/print');

        if (!$pools) {
            return response()->json(['success' => false, 'message' => 'Gagal terhubung ke MikroTik atau data kosong.'], 400);
        }

        $count = 0;
        foreach ($pools as $pool) {
            Lamtim_ip_pool::updateOrCreate(
                ['name' => $pool['name']],
                [
                    'ranges' => $pool['ranges'] ?? null,
                    'next_pool' => $pool['next-pool'] ?? null,
                ]
            );
            $count++;
        }

        return response()->json(['success' => true, 'message' => "Berhasil menarik $count data IP Pool dari MikroTik."]);
    }

    public function destroy($id)
    {
        return $this->service->deleteByEncryptedId($id);
    }
}
