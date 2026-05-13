<?php

namespace Modules\FreeRadius\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lamtim_mikrotik;
use Modules\FreeRadius\Models\RadGroupReply;
use Modules\FreeRadius\Services\RadiusService;
use Yajra\DataTables\Facades\DataTables;

class RadiusProfileController extends Controller
{
    protected $radiusService;

    public function __construct(RadiusService $radiusService)
    {
        $this->radiusService = $radiusService;
    }

    public function index()
    {
        $mikrotiks = Lamtim_mikrotik::where('isActive', 1)->get();
        return view('radius::profile.index', compact('mikrotiks'));
    }

    public function json()
    {
        try {
            
            // Ambil semua group name yang unik
            $groupnames = RadGroupReply::distinct()->pluck('groupname');
            
            $data = [];
            foreach ($groupnames as $name) {
                // Ambil Rate Limit
                $limit = RadGroupReply::where('groupname', $name)
                    ->where('attribute', 'Mikrotik-Rate-Limit')
                    ->value('value') ?? '-';
                
                // Ambil IP Pool jika ada
                $pool = RadGroupReply::where('groupname', $name)
                    ->where('attribute', 'Framed-Pool')
                    ->value('value') ?? '-';

                $data[] = [
                    'groupname' => $name,
                    'limit' => $limit,
                    'pool' => $pool,
                    'action' => '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-profile" data-group="'.$name.'"><i class="mdi mdi-delete"></i></button>',
                ];
            }
                                 
            return DataTables::of(collect($data))->make(true);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function sync(Request $request)
    {
        $request->validate([
            'id_mikrotik' => 'required|exists:lamtim_mikrotiks,id'
        ]);

        try {
            $result = $this->radiusService->syncProfilesFromMikrotik($request->id_mikrotik);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'groupname' => 'required',
            'limit' => 'required'
        ]);

        try {
            RadGroupReply::updateOrCreate(
                ['groupname' => $request->groupname, 'attribute' => 'Mikrotik-Rate-Limit'],
                ['op' => ':=', 'value' => $request->limit]
            );
            return response()->json(['status' => true, 'message' => 'Paket berhasil disimpan.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function destroy($groupname)
    {
        $result = $this->radiusService->deleteGroup($groupname);
        return response()->json($result);
    }
}
