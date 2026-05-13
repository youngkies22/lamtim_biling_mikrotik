<?php

namespace Modules\FreeRadius\Http\Controllers;

use App\Http\Controllers\Controller;

class RadiusGuideController extends Controller
{
    public function index()
    {
        return view('radius::guide.index');
    }
}
