<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

class BaseRepository
{
  protected $model;

  public function __construct(Model $model)
  {
    $this->model = $model;
  }

  public function getAll()
  {
    return $this->model->all();
  }

  public function create(array $data)
  {
    return $this->model->create($data);
  }

  public function update(array $data, string $id)
  {
    $record = $this->model->findOrFail($id);
    $record->update($data);
    return $record;
  }

  public function deleteById(int $id): bool
  {
    $record = $this->model->find($id);
    return $record ? $record->delete() : false;
  }

  public function datatableQuery(array $columns = ['*'], array $with = [])
  {
    $query = $this->model->select($columns);
    if (!empty($with)) {
      $query->with($with);
    }
    return $query;
  }
  public function findById($id)
  {
    return $this->model->find($id);
  }
  /**
   * cek apakah data ada atau tidak,
   * dengan kondisi lebih dari 1
   */
  public function existsBy(array $conditions): bool
  {
    $query = $this->model->newQuery();

    foreach ($conditions as $column => $value) {
      $query->where($column, $value);
    }

    return $query->exists();
  }
  public function updateStock($id, $column, $value)
  {
    return $this->model->where('id', $id)->update([$column => $value]);
  }
}
