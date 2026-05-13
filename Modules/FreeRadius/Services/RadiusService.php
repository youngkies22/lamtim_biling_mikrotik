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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
}
