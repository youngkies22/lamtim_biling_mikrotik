<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Lamtim_foto;
use App\SendRespon\LamtimResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FotoController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'idUser' => 'required|string',
                'fotos' => 'required|array|min:1',
                'fotos.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // max 5MB per file
            ]);

            $idUser = decrypt($request->idUser);
            $uploadedFiles = [];

            DB::beginTransaction();
            try {
                foreach ($request->file('fotos') as $file) {
                    // Generate unique filename
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    
                    // Store file in public storage
                    $path = $file->storeAs('fotos/user/' . $idUser, $filename, 'public');
                    
                    // Get file size
                    $fileSize = $file->getSize();
                    $fileSizeFormatted = $this->formatFileSize($fileSize);
                    
                    // Save to database
                    $foto = Lamtim_foto::create([
                        'idPelanggan' => $idUser,
                        'idJenis' => 1, // Default jenis foto
                        'foto' => $path,
                        'extensi' => $file->getClientOriginalExtension(),
                        'ukuran' => $fileSizeFormatted,
                        'ukuran2' => $fileSize
                    ]);
                    
                    $uploadedFiles[] = $foto;
                }

                DB::commit();
                
                return LamtimResponse::accept('Foto berhasil diupload.', [
                    'fotos' => $uploadedFiles
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return LamtimResponse::badRequest('Validasi gagal: ' . implode(', ', $e->errors()));
        } catch (\Exception $e) {
            Log::error('Gagal upload foto: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return LamtimResponse::internalServerError('Gagal upload foto: ' . $e->getMessage());
        }
    }

    /**
     * Get all photos by user ID
     */
    public function getByUser(Request $request, $id)
    {
        try {
            $idUser = decrypt($id);
            $fotos = Lamtim_foto::where('idPelanggan', $idUser)
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'status' => true,
                'data' => $fotos->map(function($foto) {
                    return [
                        'id' => encrypt($foto->id),
                        'path' => Storage::url($foto->foto),
                        'nama' => $foto->foto ? basename($foto->foto) : '-',
                        'created_at' => $foto->created_at ? $foto->created_at->format('d-m-Y H:i:s') : '-'
                    ];
                })
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil foto: ' . $e->getMessage());
            return LamtimResponse::internalServerError('Gagal mengambil foto.');
        }
    }
    
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $fotoId = decrypt($id);
            $foto = Lamtim_foto::findOrFail($fotoId);
            
            // Delete file from storage
            if ($foto->foto && Storage::disk('public')->exists($foto->foto)) {
                Storage::disk('public')->delete($foto->foto);
            }
            
            // Delete from database
            $foto->delete();
            
            return LamtimResponse::accept('Foto berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus foto: ' . $e->getMessage());
            return LamtimResponse::internalServerError('Gagal menghapus foto.');
        }
    }
}
