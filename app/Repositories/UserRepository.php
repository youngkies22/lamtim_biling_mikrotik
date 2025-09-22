<?php

/**
 * Tempat khusus untuk menyimpan dan mengelola query database.
 * Mengakses database (Model, Query Builder, dll)
 * Menyediakan data mentah ke Service
 * Menyembunyikan kompleksitas query dari controller
 */

namespace App\Repositories;

use App\Models\Lamtim_odp;
use App\Models\Lamtim_paket;
use App\Models\Lamtim_user_details;
use App\Models\Lamtim_user_mikrotik_details;
use App\Models\User;

class UserRepository
{
  public function getAll()
  {
    return User::all(); // atau with() kalau butuh relasi
  }
  public function find($id)
  {
    return User::find($id);
  }
  public function findPaket($id)
  {
    return Lamtim_paket::select('id', 'nama', 'kode')->find($id);
  }

  public function findUserMikrotik($id)
  {
    return Lamtim_user_mikrotik_details::find($id);
  }
  public function findUserMikrotikByIdUser($id)
  {
    return Lamtim_user_mikrotik_details::where('idUser', $id)->first();
  }
  public function exisUserMikrotikByIdUser($id)
  {
    return Lamtim_user_mikrotik_details::where('idUser', $id)->exists();
  }

  public function create(array $data)
  {
    return User::create($data);
  }
  public function createDetail(array $data)
  {
    return Lamtim_user_details::create($data);
  }
  public function createMikrotikUser(array $data)
  {
    return Lamtim_user_mikrotik_details::create($data);
  }
  public function update(array $data, string $id)
  {
    $mikrotik = User::findOrFail($id);
    $mikrotik->update($data);
    return $mikrotik;
  }
  public function updateDetailWhere(array $data, array $where)
  {
    $query = Lamtim_user_details::where($where)->first();
    $query->update($data);
    return $query;
  }

  /**
   * update data user mikrotik detail
   * data user yang berhubungan dengan mikrotik
   */
  public function updateMikrotikUserWhere(array $data, string $id)
  {
    $mikrotik = Lamtim_user_mikrotik_details::where('idUser', $id)->first();
    $mikrotik->update($data);
    return $mikrotik;
  }
  public function deleteById(int $id): bool
  {
    $mikrotik = User::find($id);

    if (!$mikrotik) {
      return false;
    }

    return $mikrotik->delete();
  }

  public function datatableQuery(array $columns = ['*'], array $with = [], array $where = [])
  {
    $query = User::select($columns);

    if (!empty($where)) {
      $query->where($where);
    }

    if (!empty($with)) {
      $query->with($with);
    }

    return $query;
  }

  /**
   * get data odp untuk ambil id data odc dan olt
   */
  public function getOdpOdcOlt($id)
  {
    return Lamtim_odp::select('id', 'idOdc', 'idOlt')->find($id);
  }
}
