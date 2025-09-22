<?php

/**
 * Tempat khusus untuk menyimpan dan mengelola query database.
 * Mengakses database (Model, Query Builder, dll)
 * Menyediakan data mentah ke Service
 * Menyembunyikan kompleksitas query dari controller
 */

namespace App\Repositories;

use App\Models\Lamtim_mikrotik;

class MikrotikRepository
{
  public function getAll()
  {
    return Lamtim_mikrotik::all(); // atau with() kalau butuh relasi
  }

  public function create(array $data)
  {
    return Lamtim_mikrotik::create($data);
  }
  public function update(array $data, string $id)
  {
    $mikrotik = Lamtim_mikrotik::findOrFail($id);
    $mikrotik->update($data);
    return $mikrotik;
  }
  public function deleteById(int $id): bool
  {
    $mikrotik = Lamtim_mikrotik::find($id);

    if (!$mikrotik) {
      return false;
    }

    return $mikrotik->delete();
  }

  public function datatableQuery()
  {
    return Lamtim_mikrotik::select(['id', 'kode', 'nama', 'username', 'password', 'ip', 'port', 'isActive', 'created_at'])->get();
  }
}
