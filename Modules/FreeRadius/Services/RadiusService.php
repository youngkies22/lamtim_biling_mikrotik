<?php

namespace Modules\FreeRadius\Services;

use App\Models\Lamtim_mikrotik;
use App\Models\Lamtim_paket;
use App\Services\MikrotikMulti;
use Modules\FreeRadius\Models\RadCheck;
use Modules\FreeRadius\Models\RadReply;
use Modules\FreeRadius\Models\RadUserGroup;
use Modules\FreeRadius\Models\RadGroupReply;
use Modules\FreeRadius\Models\RadGroupCheck;
use Modules\FreeRadius\Models\RadAcct;
use Modules\FreeRadius\Models\Nas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RadiusService
{
    /**
     * Sinkronisasi data dari MikroTik ke RADIUS
     */
    public function syncFromMikrotik($idMikrotik, $type = 'ppp')
    {
        $command = ($type === 'ppp') ? '/ppp/secret/print' : '/ip/hotspot/user/print';
        $users = MikrotikMulti::commandApi2($idMikrotik, $command);

        if (!$users) return ['status' => false, 'message' => 'Gagal mengambil data dari MikroTik.'];

        $count = 0;
        foreach ($users as $u) {
            $username = $u['name'] ?? null;
            $password = $u['password'] ?? '';
            $profile  = $u['profile'] ?? 'default';

            if (!$username) continue;

            // 1. RadCheck (Password)
            RadCheck::updateOrCreate(
                ['username' => $username, 'attribute' => 'Cleartext-Password'],
                ['op' => ':=', 'value' => $password]
            );

            // 2. RadUserGroup (Mapping ke Profile)
            RadUserGroup::updateOrCreate(
                ['username' => $username],
                ['groupname' => $profile, 'priority' => 1]
            );

            $count++;
        }

        return ['status' => true, 'count' => $count];
    }

    /**
     * Sinkronisasi data user tunggal ke RADIUS (Digunakan oleh Observer)
     */
    public function syncUser($userMikrotik)
    {
        $username = $userMikrotik->namaMikrotikUser;
        $password = $userMikrotik->password;
        $profile  = $userMikrotik->profileMikrotikUser;
        $isIsolir = (int) $userMikrotik->statusIsolir === 1;
        $isOff    = $userMikrotik->status === 'off' || $userMikrotik->status === 'berhenti';

        // Jika statusnya OFF, hapus dari RADIUS
        if ($isOff) {
            $this->deleteUser($username);
            return;
        }

        // 1. RadCheck (Password)
        RadCheck::updateOrCreate(
            ['username' => $username, 'attribute' => 'Cleartext-Password'],
            ['op' => ':=', 'value' => $password]
        );

        // 2. RadUserGroup (Mapping ke Profile)
        // Jika isolir, arahkan ke profile ISOLIR
        $targetProfile = $isIsolir ? 'ISOLIR' : $profile;

        RadUserGroup::updateOrCreate(
            ['username' => $username],
            ['groupname' => $targetProfile, 'priority' => 1]
        );

        // 3. Update timestamp sinkronisasi di billing (tanpa memicu event Observer)
        $userMikrotik->timestamps = false;
        if (method_exists($userMikrotik, 'updateQuietly')) {
            $userMikrotik->updateQuietly(['radius_synced_at' => now()]);
        } else {
            \App\Models\Lamtim_user_mikrotik_details::withoutEvents(function () use ($userMikrotik) {
                $userMikrotik->update(['radius_synced_at' => now()]);
            });
        }
    }

    /**
     * Hapus User dari RADIUS
     */
    public function deleteUser($username)
    {
        RadCheck::where('username', $username)->delete();
        RadUserGroup::where('username', $username)->delete();
        RadReply::where('username', $username)->delete();
    }

    /**
     * Sinkronisasi MASSAL dari Database Billing ke RADIUS
     */
    public function syncFromBilling()
    {
        $users = \App\Models\Lamtim_user_mikrotik_details::all();
        $count = 0;

        foreach ($users as $user) {
            $this->syncUser($user);
            $count++;
        }

        return ['status' => true, 'count' => $count];
    }

    /**
     * Sinkronisasi Profile/Paket dari MikroTik ke radgroupreply
     */
    public function syncProfilesFromMikrotik($idMikrotik, $type = 'ppp')
    {
        $command = ($type === 'ppp') ? '/ppp/profile/print' : '/ip/hotspot/user/profile/print';
        $profiles = MikrotikMulti::commandApi2($idMikrotik, $command);

        if (!$profiles) return ['status' => false, 'message' => 'Gagal mengambil data Profile dari MikroTik.'];

        $count = 0;
        foreach ($profiles as $p) {
            $name = $p['name'] ?? null;
            $rateLimit = $p['rate-limit'] ?? null;

            if (!$name || $name === 'default' || $name === 'default-encryption') continue;

            // Update radgroupreply (Rate Limit)
            if ($rateLimit) {
                RadGroupReply::updateOrCreate(
                    ['groupname' => $name, 'attribute' => 'Mikrotik-Rate-Limit'],
                    ['op' => ':=', 'value' => $rateLimit]
                );
                $count++;
            }
        }

        return ['status' => true, 'count' => $count];
    }

    /**
     * Migrasi User dari RADIUS ke MikroTik (RADIUS -> MikroTik)
     */
    public function migrateRadiusToMikrotik($idMikrotik)
    {
        // Ambil semua username dan password dari radcheck
        $radiusUsers = RadCheck::where('attribute', 'Cleartext-Password')->get();
        
        if ($radiusUsers->isEmpty()) return ['status' => false, 'message' => 'Tidak ada user di database RADIUS.'];

        $successCount = 0;
        foreach ($radiusUsers as $user) {
            // Ambil group/profile user
            $group = RadUserGroup::where('username', $user->username)->first();
            $profile = $group ? $group->groupname : 'default';

            // Tambahkan ke MikroTik (PPP Secret)
            $params = [
                'name' => $user->username,
                'password' => $user->value,
                'profile' => $profile,
                'service' => 'any'
            ];

            try {
                $result = MikrotikMulti::commandApi2($idMikrotik, '/ppp/secret/add', $params);
                $successCount++;
            } catch (\Exception $e) {
                Log::warning("Migrasi gagal untuk user {$user->username}: " . $e->getMessage());
            }
        }

        return ['status' => true, 'count' => $successCount];
    }

    // ========================================================================
    // FEATURE: NAS Management (Sync MikroTik -> nas table)
    // ========================================================================

    /**
     * Sinkronisasi MikroTik aktif ke tabel NAS
     */
    public function syncNas()
    {
        try {
            $mikrotiks = Lamtim_mikrotik::where('isActive', 1)->get();
            $count = 0;

            foreach ($mikrotiks as $mk) {
                $nas = Nas::firstOrNew(['nasname' => $mk->ip]);

                if (!$nas->exists || empty($nas->secret)) {
                    $nas->secret = Str::random(12);
                }

                $nas->shortname   = $mk->nama;
                $nas->type        = 'other';
                $nas->description = 'MikroTik: ' . $mk->nama;
                $nas->save();
                $count++;
            }

            return ['status' => true, 'count' => $count, 'message' => "$count NAS berhasil disinkronkan."];
        } catch (\Exception $e) {
            Log::error("RadiusService@syncNas Error: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Regenerasi secret NAS
     */
    public function regenerateNasSecret($id)
    {
        try {
            $nas = Nas::findOrFail($id);
            $newSecret = Str::random(12);
            $nas->secret = $newSecret;
            $nas->save();

            return [
                'status'     => true,
                'message'    => "Secret untuk {$nas->nasname} berhasil digenerate ulang.",
                'new_secret' => $newSecret,
            ];
        } catch (\Exception $e) {
            Log::error("RadiusService@regenerateNasSecret Error: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    // ========================================================================
    // FEATURE: Groups Sync from Billing Packages (dengan Burst)
    // ========================================================================

    /**
     * Sinkronisasi Paket Billing ke radgroupreply
     */
    public function syncGroupsFromBilling()
    {
        try {
            $packages = Lamtim_paket::where('isActive', 1)->get();
            $count = 0;

            foreach ($packages as $pkg) {
                $groupName = strtoupper($pkg->kode);

                // Bangun Rate-Limit value
                $speed = $pkg->speed_limit ?? '1M/1M';
                if ($pkg->is_burst && $pkg->burst_rate && $pkg->burst_threshold && $pkg->burst_time) {
                    $speed .= " {$pkg->burst_rate} {$pkg->burst_threshold} {$pkg->burst_time} {$pkg->priority}";
                }

                // Rate Limit
                RadGroupReply::updateOrCreate(
                    ['groupname' => $groupName, 'attribute' => 'Mikrotik-Rate-Limit'],
                    ['op' => ':=', 'value' => $speed]
                );

                // IP Pool (Framed-Pool)
                if ($pkg->ip_pool) {
                    RadGroupReply::updateOrCreate(
                        ['groupname' => $groupName, 'attribute' => 'Framed-Pool'],
                        ['op' => ':=', 'value' => $pkg->ip_pool]
                    );
                } else {
                    RadGroupReply::where('groupname', $groupName)->where('attribute', 'Framed-Pool')->delete();
                }

                // Address List
                if ($pkg->address_list) {
                    RadGroupReply::updateOrCreate(
                        ['groupname' => $groupName, 'attribute' => 'Mikrotik-Address-List'],
                        ['op' => ':=', 'value' => $pkg->address_list]
                    );
                }

                $count++;
            }

            // Pastikan group ISOLIR selalu ada
            RadGroupReply::updateOrCreate(
                ['groupname' => 'ISOLIR', 'attribute' => 'Mikrotik-Rate-Limit'],
                ['op' => ':=', 'value' => '64k/128k']
            );

            return ['status' => true, 'count' => $count, 'message' => "$count paket berhasil disinkronkan ke RADIUS."];
        } catch (\Exception $e) {
            Log::error("RadiusService@syncGroupsFromBilling Error: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Hapus group dari RADIUS
     */
    public function deleteGroup($groupname)
    {
        try {
            RadGroupReply::where('groupname', $groupname)->delete();
            RadUserGroup::where('groupname', $groupname)->delete();
            return ['status' => true, 'message' => "Group $groupname berhasil dihapus."];
        } catch (\Exception $e) {
            Log::error("RadiusService@deleteGroup Error: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    // ========================================================================
    // FEATURE: Sinkronisasi Semua User dari Billing
    // ========================================================================

    /**
     * Sinkronisasi semua user billing aktif ke RADIUS
     */
    public function syncAllUsers()
    {
        try {
            $users = \App\Models\Lamtim_user_mikrotik_details::all();
            $success = 0;

            foreach ($users as $user) {
                $this->syncUser($user);
                $success++;
            }

            return ['status' => true, 'count' => $success, 'message' => "Berhasil sinkronisasi $success user."];
        } catch (\Exception $e) {
            Log::error("RadiusService@syncAllUsers Error: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    // ========================================================================
    // FEATURE: Active Sessions Monitoring (radacct)
    // ========================================================================

    /**
     * Ambil sesi aktif (online)
     */
    public function getActiveSessions()
    {
        return RadAcct::whereNull('acctstoptime')
            ->orderBy('acctstarttime', 'desc')
            ->get();
    }

    /**
     * Ambil riwayat sesi
     */
    public function getHistory($limit = 100)
    {
        return RadAcct::whereNotNull('acctstoptime')
            ->orderBy('acctstoptime', 'desc')
            ->paginate($limit);
    }

    /**
     * Kirim Packet of Disconnect (PoD) untuk memutus sesi user
     */
    public function disconnectSession($username)
    {
        try {
            $session = RadAcct::whereNull('acctstoptime')
                ->where('username', $username)
                ->first();

            if (!$session) {
                return ['status' => false, 'message' => "User $username tidak sedang online."];
            }

            $nas = Nas::where('nasname', $session->nasipaddress)->first();
            if (!$nas) {
                return ['status' => false, 'message' => "NAS {$session->nasipaddress} tidak ditemukan di tabel nas."];
            }

            $nasIp  = $session->nasipaddress;
            $secret = $nas->secret;
            $cmd    = "echo \"User-Name={$username}\" | radclient -x {$nasIp}:1700 disconnect {$secret} 2>&1";

            $output     = [];
            $returnCode = 0;
            exec($cmd, $output, $returnCode);

            if ($returnCode === 0) {
                return ['status' => true, 'message' => "Perintah disconnect berhasil dikirim ke $nasIp untuk user $username."];
            }

            Log::error("PoD Failed for $username: " . implode(' ', $output));
            return ['status' => false, 'message' => "Gagal disconnect: " . implode(' ', $output)];
        } catch (\Exception $e) {
            Log::error("RadiusService@disconnectSession Error: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    // ========================================================================
    // FEATURE: User Configuration (Static IP, VLAN)
    // ========================================================================

    /**
     * Set static IP untuk user
     */
    public function setStaticIp($username, $ip)
    {
        try {
            if (!$ip) {
                RadReply::where('username', $username)->where('attribute', 'Framed-IP-Address')->delete();
                return ['status' => true, 'message' => 'Static IP berhasil dihapus.'];
            }

            RadReply::updateOrCreate(
                ['username' => $username, 'attribute' => 'Framed-IP-Address'],
                ['op' => ':=', 'value' => $ip]
            );

            return ['status' => true, 'message' => "Static IP $ip berhasil diset untuk $username."];
        } catch (\Exception $e) {
            Log::error("RadiusService@setStaticIp Error: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Set VLAN untuk user
     */
    public function setVlan($username, $vlanId)
    {
        try {
            if (!$vlanId) {
                RadReply::where('username', $username)->whereIn('attribute', [
                    'Tunnel-Type', 'Tunnel-Medium-Type', 'Tunnel-Private-Group-Id'
                ])->delete();
                return ['status' => true, 'message' => 'VLAN berhasil dihapus.'];
            }

            RadReply::updateOrCreate(
                ['username' => $username, 'attribute' => 'Tunnel-Type'],
                ['op' => ':=', 'value' => 'VLAN']
            );
            RadReply::updateOrCreate(
                ['username' => $username, 'attribute' => 'Tunnel-Medium-Type'],
                ['op' => ':=', 'value' => 'IEEE-802']
            );
            RadReply::updateOrCreate(
                ['username' => $username, 'attribute' => 'Tunnel-Private-Group-Id'],
                ['op' => ':=', 'value' => (string)$vlanId]
            );

            return ['status' => true, 'message' => "VLAN ID $vlanId berhasil diset untuk $username."];
        } catch (\Exception $e) {
            Log::error("RadiusService@setVlan Error: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Statistik penggunaan user (seumur hidup)
     */
    public function getUserStats($username)
    {
        $stats = RadAcct::where('username', $username)
            ->selectRaw('COALESCE(SUM(acctinputoctets), 0) as upload, COALESCE(SUM(acctoutputoctets), 0) as download, COALESCE(SUM(acctsessiontime), 0) as total_time')
            ->first();

        return [
            'upload'     => $this->formatBytes($stats->upload),
            'download'   => $this->formatBytes($stats->download),
            'total_time' => $this->formatSeconds($stats->total_time),
        ];
    }

    // ========================================================================
    // FEATURE: Import from MikroTik
    // ========================================================================

    /**
     * Import user dari MikroTik ke Billing + RADIUS
     */
    public function importFromMikrotik($idMikrotik)
    {
        try {
            $secrets = MikrotikMulti::commandApi2($idMikrotik, '/ppp/secret/print');
            if (empty($secrets)) {
                return ['status' => false, 'message' => 'Tidak ada PPP Secret ditemukan di MikroTik ini.'];
            }

            $success = 0;
            $failed  = 0;

            foreach ($secrets as $s) {
                try {
                    $username = $s['name'] ?? null;
                    if (!$username) continue;

                    // Cari atau buat user billing
                    $userMikrotik = \App\Models\Lamtim_user_mikrotik_details::where('namaMikrotikUser', $username)->first();
                    if (!$userMikrotik) {
                        $userMikrotik = \App\Models\Lamtim_user_mikrotik_details::create([
                            'namaMikrotikUser'    => $username,
                            'password'            => $s['password'] ?? '',
                            'profileMikrotikUser' => $s['profile'] ?? 'default',
                            'serviceMikrotikUser' => $s['service'] ?? 'pppoe',
                            'status'              => ($s['disabled'] == 'true') ? 'off' : 'active',
                            'idMikrotik'          => $idMikrotik,
                        ]);
                    }

                    // Sync ke RADIUS
                    $this->syncUser($userMikrotik);
                    $success++;
                } catch (\Exception $ex) {
                    Log::error("Import User {$s['name']} Failed: " . $ex->getMessage());
                    $failed++;
                }
            }

            return ['status' => true, 'count' => $success, 'message' => "Berhasil memproses $success user. ($failed gagal)"];
        } catch (\Exception $e) {
            Log::error("importFromMikrotik Error: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    // ========================================================================
    // HELPERS
    // ========================================================================

    private function formatBytes($bytes)
    {
        $bytes = (float)$bytes;
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) $bytes /= 1024;
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function formatSeconds($seconds)
    {
        $seconds = (int)$seconds;
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
}
