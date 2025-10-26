<?php

namespace App\Services;

use App\Repositories\AreaRepository;
use Yajra\DataTables\DataTables;

class AreaService
{
  protected $repository;

  public function __construct(AreaRepository $repository)
  {
    $this->repository = $repository;
  }

  /**
   * Get all areas
   */
  public function getAll()
  {
    return $this->repository->getAll();
  }

  /**
   * Find area by ID
   */
  public function find($id)
  {
    return $this->repository->find($id);
  }

  /**
   * Create new area
   */
  public function create(array $data)
  {
    return $this->repository->create([
      'name' => $data['name'],
      'address' => $data['address'],
      'code_area' => $data['code_area'],
      'created_at' => now(),
      'updated_at' => now()
    ]);
  }

  /**
   * Update area
   */
  public function update($id, array $data)
  {
    $area = $this->repository->find($id);

    if (!$area) {
      throw new \Exception('Area tidak ditemukan');
    }

    return $this->repository->update($id, [
      'name' => $data['name'],
      'address' => $data['address'],
      'code_area' => $data['code_area'],
      'updated_at' => now()
    ]);
  }

  /**
   * Delete area
   */
  public function delete($id)
  {
    $area = $this->repository->find($id);

    if (!$area) {
      throw new \Exception('Area tidak ditemukan');
    }

    return $this->repository->delete($id);
  }

  /**
   * Mendapatkan data dalam format datatables JSON
   */
  public function getDatatablesJson($request)
  {
    $columns = ['id', 'name', 'address', 'code_area'];
    $with = [];
    $callbacks = [];

    // Jika ada idd, tambahkan kondisi where
    $where = [];
    if (!empty($request->idd)) {
      $where = ['id' => decrypt($request->idd)];
    }

    $query = $this->repository->datatableQuery($columns, $with, $where)->get();

    $datatable = DataTables::of($query);

    if (!empty($callbacks)) {
      foreach ($callbacks as $column => $callback) {
        $datatable->editColumn($column, $callback);
      }
    }

    return $datatable->make(true);
  }
}
