<?php

namespace App\Observers;

use App\Models\Lamtim_user_mikrotik_details;
use Modules\FreeRadius\Services\RadiusService;
use Illuminate\Support\Facades\Log;

class UserMikrotikObserver
{
    protected $radiusService;

    public function __construct(RadiusService $radiusService)
    {
        $this->radiusService = $radiusService;
    }

    /**
     * Handle the Lamtim_user_mikrotik_details "saved" event.
     * Covers both created and updated.
     */
    public function saved(Lamtim_user_mikrotik_details $userMikrotik)
    {
        try {
            $this->radiusService->syncUser($userMikrotik);
        } catch (\Exception $e) {
            Log::error("Gagal sinkronisasi ke RADIUS saat menyimpan user: " . $e->getMessage());
        }
    }

    /**
     * Handle the Lamtim_user_mikrotik_details "deleted" event.
     */
    public function deleted(Lamtim_user_mikrotik_details $userMikrotik)
    {
        try {
            $this->radiusService->deleteUser($userMikrotik->namaMikrotikUser);
        } catch (\Exception $e) {
            Log::error("Gagal menghapus dari RADIUS saat menghapus user: " . $e->getMessage());
        }
    }
}
