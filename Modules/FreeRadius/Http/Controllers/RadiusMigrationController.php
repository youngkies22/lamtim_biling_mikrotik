<?php

namespace Modules\FreeRadius\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lamtim_mikrotik;
use Modules\FreeRadius\Services\RadiusService;

class RadiusMigrationController extends Controller
{
    protected $radiusService;

    public function __construct(RadiusService $radiusService)
    {
        $this->radiusService = $radiusService;
    }

    public function index()
    {
        $mikrotiks = Lamtim_mikrotik::where('isActive', 1)->get();
        return view('radius::migration.index', compact('mikrotiks'));
    }

    public function migrate(Request $request)
    {
        $request->validate([
            'id_mikrotik' => 'required|exists:lamtim_mikrotiks,id'
        ]);

        try {
            $result = $this->radiusService->migrateRadiusToMikrotik($request->id_mikrotik);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }
}
