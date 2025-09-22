<?php

/**
 * UserController
 * Dev       : CODETEAM @mryes
 * Aplikasi  : Sarpras SMK BUDI UTOMO WAY JEPARA
 * Location  : Way Jepara Lampung Timur
 * @author mryes way jepara <mryes2210@gmail.com>
 */

namespace App\SendRespon;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class LamtimResponse
{
  /**
   * Status 403 Forbidden
   */
  public static function forbidden(string $message = ''): JsonResponse
  {
    return response()->json([
      'error' => true,
      'message' => $message ?: 'You do not have access to this resource.',
    ], HttpResponse::HTTP_FORBIDDEN);
  }

  /**
   * Status 404 Not Found
   */
  public static function notFound(string $message = ''): JsonResponse
  {
    return response()->json([
      'error' => true,
      'message' => $message ?: 'Data not found.',
    ], HttpResponse::HTTP_NOT_FOUND);
  }

  /**
   * Status 400 Bad Request
   */
  public static function badRequest(string $message = ''): JsonResponse
  {
    return response()->json([
      'error' => true,
      'message' => $message ?: 'Bad request.',
    ], HttpResponse::HTTP_BAD_REQUEST);
  }

  /**
   * Status 200 OK with message only
   */
  public static function accept(string $message = ''): JsonResponse
  {
    return response()->json([
      'error' => false,
      'message' => $message ?: 'Success.',
    ], HttpResponse::HTTP_OK);
  }

  /**
   * Status 200 OK with data
   */
  public static function acceptData($data): JsonResponse
  {
    return response()->json([
      'error' => false,
      'data' => $data,
    ], HttpResponse::HTTP_OK);
  }

  /**
   * Status 200 OK with custom structure
   */
  public static function acceptCustom(array $data): JsonResponse
  {
    return response()->json($data, HttpResponse::HTTP_OK);
  }

  /**
   * Status 500 Internal Server Error
   */
  public static function internalServerError(string $message = ''): JsonResponse
  {
    return response()->json([
      'error' => true,
      'message' => $message ?: 'Internal server error.',
    ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
  }
}
