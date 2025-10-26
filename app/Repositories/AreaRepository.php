<?php

namespace App\Repositories;

use App\Models\Lamtim_area;

class AreaRepository
{
  protected $model;

  /**
   * Injeksi model lewat konstruktor
   */
  public function __construct(Lamtim_area $model)
  {
    $this->model = $model;
  }

  /**
   * Ambil semua data area
   */
  public function getAll()
  {
    return $this->model->all();
  }

  /**
   * Cari data area berdasarkan ID
   */
  public function find($id)
  {
    return $this->model->find($id);
  }

  /**
   * Buat area baru
   */
  public function create(array $data)
  {
    return $this->model->create($data);
  }

  /**
   * Update area
   */
  public function update($id, array $data)
  {
    $area = $this->model->find($id);
    $area->update($data);
    return $area;
  }

  /**
   * Hapus area
   */
  public function delete($id)
  {
    $area = $this->model->find($id);
    return $area->delete();
  }

  /**
   * Query json untuk datatables
   */
  public function datatableQuery(array $columns, array $with = [], array $where = [])
  {
    $query = $this->model->select($columns);

    if (!empty($with)) {
      $query->with($with);
    }

    if (!empty($where)) {
      foreach ($where as $field => $value) {
        $query->where($field, $value);
      }
    }

    return $query;
  }
}
