<?php

namespace Modules\FreeRadius\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\FreeRadius\Services\RadiusService;

class RadiusMonitorController extends Controller
{
    protected $radiusService;

    public function __construct(RadiusService $radiusService)
    {
        $this->radiusService = $radiusService;
    }

    public function index()
    {
        $sessions = $this->radiusService->getActiveSessions();
        return view('radius::monitor.index', compact('sessions'));
    }

    public function history()
    {
        $history = $this->radiusService->getHistory();
        return view('radius::monitor.history', compact('history'));
    }

    public function disconnect($username)
    {
        $result = $this->radiusService->disconnectSession($username);
        return response()->json($result);
    }
}
