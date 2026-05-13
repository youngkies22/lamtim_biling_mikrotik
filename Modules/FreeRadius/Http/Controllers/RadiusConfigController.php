<?php

namespace Modules\FreeRadius\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FreeRadius\Models\RadiusConfig;
use Illuminate\Support\Facades\DB;

class RadiusConfigController extends Controller
{
    public function index()
    {
        $config = RadiusConfig::first();
        $serverCheck = $this->checkFreeRadiusServer();
        return view('radius::config.index', compact('config', 'serverCheck'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'host' => 'required',
            'port' => 'required|integer',
            'database' => 'required',
            'username' => 'required',
            'password' => 'nullable',
            'secret' => 'nullable',
        ]);

        RadiusConfig::updateOrCreate(['id' => 1], $validated);

        return back()->with('success', 'Konfigurasi berhasil disimpan.');
    }

    public function testConnection()
    {
        $config = RadiusConfig::first();
        
        if (!$config) {
            return response()->json(['status' => false, 'message' => 'Konfigurasi tidak ditemukan.']);
        }

        try {
            // Set dynamic connection
            config(['database.connections.radius_test' => [
                'driver' => 'mysql',
                'host' => $config->host,
                'port' => $config->port,
                'database' => $config->database,
                'username' => $config->username,
                'password' => $config->password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]]);

            DB::connection('radius_test')->getPdo();
            
            return response()->json(['status' => true, 'message' => 'Koneksi Berhasil!']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Koneksi Gagal: ' . $e->getMessage()]);
        }
    }

    /**
     * Cek apakah FreeRADIUS terinstall dan berjalan di server
     */
    private function checkFreeRadiusServer()
    {
        $result = [
            'binary_radiusd'   => false,
            'binary_radclient' => false,
            'service_active'   => false,
            'version'          => null,
        ];

        // Deteksi OS
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        // Cek binary radiusd/freeradius
        try {
            if ($isWindows) {
                // Di Windows, cek via where
                $whichRadiusd = trim((string) \shell_exec('where radiusd.exe 2>NUL || where freeradius.exe 2>NUL'));
            } else {
                $whichRadiusd = trim((string) \shell_exec('which radiusd 2>/dev/null || which freeradius 2>/dev/null'));
            }
            if ($whichRadiusd) {
                $result['binary_radiusd'] = true;
                if (!$isWindows) {
                    $version = @\shell_exec('radiusd -v 2>/dev/null || freeradius -v 2>/dev/null');
                    if ($version) {
                        preg_match('/FreeRADIUS Version (\S+)/', $version, $m);
                        $result['version'] = $m[1] ?? trim(explode("\n", $version)[0]);
                    }
                }
            }
        } catch (\Exception $e) {
            // Abaikan error, anggap tidak terinstall
        }

        // Cek binary radclient
        try {
            if ($isWindows) {
                $whichRadclient = trim((string) \shell_exec('where radclient.exe 2>NUL'));
            } else {
                $whichRadclient = trim((string) \shell_exec('which radclient 2>/dev/null'));
            }
            if ($whichRadclient) {
                $result['binary_radclient'] = true;
            }
        } catch (\Exception $e) {
            // Abaikan error
        }

        // Cek status service (hanya di Linux)
        if (!$isWindows) {
            try {
                $statusOutput = @\shell_exec('systemctl is-active freeradius 2>/dev/null || systemctl is-active radiusd 2>/dev/null');
                $result['service_active'] = trim((string) $statusOutput) === 'active';
            } catch (\Exception $e) {
                // Service check tidak tersedia
            }
        }

        return $result;
    }

    /**
     * API endpoint untuk cek server (bisa dipanggil via AJAX)
     */
    public function checkServer()
    {
        $check = $this->checkFreeRadiusServer();
        return response()->json($check);
    }
}
