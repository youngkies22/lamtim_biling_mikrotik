<?php


namespace App\Traits;

trait ApiResponseTrait
{
  public function successResponse($data = null, $message = 'Success', $errors = [])
  {
    return [
      'success' => true,
      'data' => $data,
      'message' => $message,
      'errors' => $errors,
    ];
  }

  public function errorResponse($message = 'Error', $errors = [], $data = null)
  {
    return [
      'success' => false,
      'data' => $data,
      'message' => $message,
      'errors' => $errors,
    ];
  }
}
