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
        return view('radius::config.index', compact('config'));
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
}
