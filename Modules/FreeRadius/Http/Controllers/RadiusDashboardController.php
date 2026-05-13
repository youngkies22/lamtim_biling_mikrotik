<?php

namespace Modules\FreeRadius\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\FreeRadius\Models\RadCheck;
use Modules\FreeRadius\Models\RadGroupReply;
use Modules\FreeRadius\Models\Nas;
use Modules\FreeRadius\Models\RadAcct;

class RadiusDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'nas_count'    => Nas::count(),
            'group_count'  => RadGroupReply::distinct('groupname')->count('groupname'),
            'user_count'   => RadCheck::where('attribute', 'Cleartext-Password')->count(),
            'online_count' => RadAcct::whereNull('acctstoptime')->count(),
        ];

        return view('radius::dashboard.index', compact('stats'));
    }
}
