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
            'exec_available'   => false,
        ];

        // Cek apakah fungsi shell_exec/exec tersedia (sering di-disable di hosting)
        $canExec = function_exists('shell_exec') && !in_array('shell_exec', explode(',', ini_get('disable_functions') ?? ''));
        $result['exec_available'] = $canExec;

        if (!$canExec) {
            return $result;
        }

        // Deteksi OS
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        // Lokasi umum binary FreeRADIUS. Dicek langsung via is_executable()
        // karena proses PHP-FPM sering punya $PATH terbatas (tidak termasuk
        // /usr/sbin), sehingga `which` bisa gagal walau binary-nya ada.
        $radiusdPaths = $isWindows
            ? []
            : ['/usr/sbin/radiusd', '/usr/local/sbin/radiusd', '/usr/sbin/freeradius', '/usr/local/sbin/freeradius'];
        $radclientPaths = $isWindows
            ? []
            : ['/usr/bin/radclient', '/usr/local/bin/radclient'];

        try {
            $foundRadiusd = null;
            foreach ($radiusdPaths as $path) {
                if (is_executable($path)) {
                    $foundRadiusd = $path;
                    break;
                }
            }

            if (!$foundRadiusd) {
                $whichCmd = $isWindows
                    ? 'where radiusd.exe 2>NUL || where freeradius.exe 2>NUL'
                    : 'which radiusd 2>/dev/null || which freeradius 2>/dev/null';
                $foundRadiusd = trim((string) \shell_exec($whichCmd)) ?: null;
            }

            if ($foundRadiusd) {
                $result['binary_radiusd'] = true;

                if (!$isWindows) {
                    $version = \shell_exec(escapeshellarg($foundRadiusd) . ' -v 2>/dev/null');
                    if ($version) {
                        preg_match('/FreeRADIUS Version (\S+)/', $version, $m);
                        $result['version'] = $m[1] ?? trim(explode("\n", $version)[0]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Abaikan error
        }

        try {
            $foundRadclient = null;
            foreach ($radclientPaths as $path) {
                if (is_executable($path)) {
                    $foundRadclient = $path;
                    break;
                }
            }

            if (!$foundRadclient) {
                $whichCmd = $isWindows
                    ? 'where radclient.exe 2>NUL'
                    : 'which radclient 2>/dev/null';
                $foundRadclient = trim((string) \shell_exec($whichCmd)) ?: null;
            }

            if ($foundRadclient) {
                $result['binary_radclient'] = true;
            }
        } catch (\Throwable $e) {
            // Abaikan error
        }

        if (!$isWindows) {
            try {
                $statusOutput = \shell_exec('systemctl is-active freeradius 2>/dev/null || systemctl is-active radiusd 2>/dev/null');
                $result['service_active'] = trim((string) $statusOutput) === 'active';
            } catch (\Throwable $e) {
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
