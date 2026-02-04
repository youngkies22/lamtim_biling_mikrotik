<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lamtim_foto extends Model
{
    protected $table = 'lamtim_fotos';
    
    public $timestamps = true; // Aktifkan timestamps untuk created_at
    const UPDATED_AT = null; // Tabel tidak memiliki updated_at
    
    protected $fillable = [
        'idPelanggan',
        'idJenis',
        'foto',
        'extensi',
        'ukuran',
        'ukuran2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'idPelanggan', 'id');
    }
}
