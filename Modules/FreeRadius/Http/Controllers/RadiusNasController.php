<?php

namespace Modules\FreeRadius\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\FreeRadius\Services\RadiusService;
use Modules\FreeRadius\Models\Nas;

class RadiusNasController extends Controller
{
    protected $radiusService;

    public function __construct(RadiusService $radiusService)
    {
        $this->radiusService = $radiusService;
    }

    public function index()
    {
        $nas = Nas::all();
        return view('radius::nas.index', compact('nas'));
    }

    public function regenerate($id)
    {
        $result = $this->radiusService->regenerateNasSecret($id);
        return response()->json($result);
    }
}
