<?php

namespace Modules\FreeRadius\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lamtim_mikrotik;
use Modules\FreeRadius\Services\RadiusService;

class RadiusSyncController extends Controller
{
    protected $radiusService;

    public function __construct(RadiusService $radiusService)
    {
        $this->radiusService = $radiusService;
    }

    public function index()
    {
        $mikrotiks = Lamtim_mikrotik::where('isActive', 1)->get();
        return view('radius::sync.index', compact('mikrotiks'));
    }

    public function sync(Request $request)
    {
        $validated = $request->validate([
            'id_mikrotik' => 'required|exists:lamtim_mikrotiks,id',
            'type' => 'required|in:ppp,hotspot'
        ]);

        try {
            $result = $this->radiusService->syncFromMikrotik($validated['id_mikrotik'], $validated['type']);
            
            if ($result['status']) {
                return response()->json([
                    'status' => true, 
                    'message' => "Berhasil sinkronisasi {$result['count']} user dari MikroTik."
                ]);
            } else {
                return response()->json(['status' => false, 'message' => $result['message']]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function syncFromBilling()
    {
        try {
            $result = $this->radiusService->syncFromBilling();
            return response()->json([
                'status' => true, 
                'message' => "Berhasil sinkronisasi {$result['count']} user dari Database Billing ke RADIUS."
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
