<?php

namespace Modules\FreeRadius\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FreeRadius\Models\RadCheck;
use Modules\FreeRadius\Models\RadUserGroup;
use Modules\FreeRadius\Models\RadReply;
use Modules\FreeRadius\Models\RadAcct;
use Modules\FreeRadius\Services\RadiusService;
use Yajra\DataTables\Facades\DataTables;

class RadiusUserController extends Controller
{
    protected $radiusService;

    public function __construct(RadiusService $radiusService)
    {
        $this->radiusService = $radiusService;
    }

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

            // 6. Ambil data pelanggan dari billing (nama, dll) berdasarkan username
            $billingUsers = \App\Models\Lamtim_user_mikrotik_details::with('user')
                ->get()
                ->keyBy('namaMikrotikUser');

            // 7. Gabungkan semua username unik
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

                // Ambil data billing jika ada
                $billingUser = $billingUsers->get($username);
                $nama = '-';
                $julukan = '';
                if ($billingUser && $billingUser->user) {
                    $nama = $billingUser->user->name;
                    $julukan = $billingUser->user->julukan ?? '';
                }

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
                    'nama'     => $nama . ($julukan ? " ($julukan)" : ''),
                    'password' => $passwords->get($username, '-'),
                    'group'    => $group,
                    'limit'    => $limits->get($username, '-'),
                    'ip'       => $ips->get($username, '-'),
                    'status'   => $status,
                    'action'   => '<div class="d-flex gap-1">
                        <a href="'.route('radius.user.config', $username).'" class="btn btn-sm btn-outline-primary" title="Konfigurasi Static IP/VLAN"><i class="mdi mdi-cog"></i></a>
                        <button type="button" class="btn btn-sm btn-outline-success btn-sync-single" data-username="'.$username.'" title="Sinkron dari Database Billing"><i class="mdi mdi-sync"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-user" data-username="'.$username.'" title="Hapus dari RADIUS"><i class="mdi mdi-delete"></i></button>
                    </div>',
                ];
            }

            return DataTables::of(collect($data))->make(true);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function config($username)
    {
        $replies = RadReply::where('username', $username)->get()->keyBy('attribute');
        $stats   = $this->radiusService->getUserStats($username);
        return view('radius::user.config', compact('username', 'replies', 'stats'));
    }

    public function configUpdate(Request $request, $username)
    {
        $ip   = $request->input('static_ip');
        $vlan = $request->input('vlan_id');

        $this->radiusService->setStaticIp($username, $ip);
        $this->radiusService->setVlan($username, $vlan);

        return response()->json(['status' => true, 'message' => 'Konfigurasi user berhasil diperbarui.']);
    }

    public function destroy($username)
    {
        $result = $this->radiusService->deleteUser($username);
        // Return in format expected by DataTables/API
        return response()->json([
            'status'  => $result['status'] ?? true,
            'success' => $result['status'] ?? true,
            'message' => $result['message'] ?? "User $username berhasil dihapus dari RADIUS.",
        ]);
    }

    /**
     * Sinkron semua pelanggan dari database billing ke RADIUS
     */
    public function syncFromDatabase()
    {
        try {
            $result = $this->radiusService->syncFromBilling();
            return response()->json([
                'status'  => true,
                'message' => "Berhasil sinkronisasi {$result['count']} pelanggan dari database billing ke RADIUS.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Sinkron satu pelanggan dari database billing ke RADIUS berdasarkan username
     */
    public function syncSingleUser($username)
    {
        try {
            $user = \App\Models\Lamtim_user_mikrotik_details::where('namaMikrotikUser', $username)->first();
            if (!$user) {
                return response()->json(['status' => false, 'message' => "User $username tidak ditemukan di database billing."]);
            }
            $this->radiusService->syncUser($user);
            return response()->json(['status' => true, 'message' => "User $username berhasil disinkronkan ke RADIUS."]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Tampilkan form tambah user RADIUS langsung
     */
    public function add()
    {
        $pakets = \App\Models\Lamtim_paket::where('isActive', 1)->get();
        return view('radius::user.add', compact('pakets'));
    }

    /**
     * Simpan user baru ke RADIUS langsung
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'profile'  => 'required|string',
            'limit'    => 'nullable|string',
        ]);

        try {
            $username = $request->username;
            $password = $request->password;
            $profile  = $request->profile;

            // Simpan ke radcheck
            \Modules\FreeRadius\Models\RadCheck::updateOrCreate(
                ['username' => $username, 'attribute' => 'Cleartext-Password'],
                ['op' => ':=', 'value' => $password]
            );

            // Simpan ke radusergroup
            \Modules\FreeRadius\Models\RadUserGroup::updateOrCreate(
                ['username' => $username],
                ['groupname' => $profile, 'priority' => 1]
            );

            // Jika ada custom rate limit, simpan ke radreply
            if ($request->filled('limit')) {
                \Modules\FreeRadius\Models\RadReply::updateOrCreate(
                    ['username' => $username, 'attribute' => 'Mikrotik-Rate-Limit'],
                    ['op' => ':=', 'value' => $request->limit]
                );
            }

            return response()->json(['status' => true, 'message' => "User $username berhasil ditambahkan ke RADIUS."]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Gagal: ' . $e->getMessage()]);
        }
    }
}
