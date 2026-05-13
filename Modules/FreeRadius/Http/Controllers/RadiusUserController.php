<?php

namespace Modules\FreeRadius\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FreeRadius\Models\RadCheck;
use Modules\FreeRadius\Models\RadUserGroup;
use Modules\FreeRadius\Models\RadReply;
use Modules\FreeRadius\Models\RadAcct;
use Yajra\DataTables\Facades\DataTables;

class RadiusUserController extends Controller
{

    public function index()
    {
        $profiles = RadUserGroup::select('groupname')->distinct()->orderBy('groupname')->get();
        return view('radius::user.index', compact('profiles'));
    }

    public function json(Request $request)
    {
        try {

            // === BATCH QUERY: Ambil semua data sekaligus, bukan per-user ===

            // 1. Ambil semua password (1 query)
            $passwords = RadCheck::whereIn('attribute', ['Cleartext-Password', 'User-Password'])
                ->pluck('value', 'username');

            // 2. Ambil semua group (1 query)
            $groups = RadUserGroup::pluck('groupname', 'username');

            // 3. Ambil semua rate limit (1 query)
            $limits = RadReply::where('attribute', 'Mikrotik-Rate-Limit')
                ->pluck('value', 'username');

            // 4. Ambil semua IP (1 query)
            $ips = RadReply::where('attribute', 'Framed-IP-Address')
                ->pluck('value', 'username');

            // 5. Ambil semua user yang sedang online (1 query)
            $onlineUsers = RadAcct::whereNull('acctstoptime')
                ->distinct()
                ->pluck('username')
                ->flip(); // flip agar bisa cek dengan isset (O(1))

            // 6. Gabungkan semua username unik
            $allUsernames = $passwords->keys()
                ->merge($groups->keys())
                ->unique();

            // Filter berdasarkan Profile SEBELUM build data (lebih efisien)
            if ($request->filled('profile')) {
                $filteredUsernames = $groups->filter(function ($groupname) use ($request) {
                    return $groupname == $request->profile;
                })->keys();
                $allUsernames = $allUsernames->intersect($filteredUsernames);
            }

            // Build data array
            $data = [];
            foreach ($allUsernames as $username) {
                $group = $groups->get($username, '-');
                $isOnline = isset($onlineUsers[$username]);

                // Tentukan status: Online / Isolir / Offline
                if ($group === 'ISOLIR') {
                    $status = 'Isolir';
                } elseif ($isOnline) {
                    $status = 'Online';
                } else {
                    $status = 'Offline';
                }

                // Filter berdasarkan status jika diminta
                if ($request->filled('status') && $request->status !== 'Semua' && $status !== $request->status) {
                    continue;
                }

                $data[] = [
                    'username' => $username,
                    'password' => $passwords->get($username, '-'),
                    'group'    => $group,
                    'limit'    => $limits->get($username, '-'),
                    'ip'       => $ips->get($username, '-'),
                    'status'   => $status,
                ];
            }

            return DataTables::of(collect($data))->make(true);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
