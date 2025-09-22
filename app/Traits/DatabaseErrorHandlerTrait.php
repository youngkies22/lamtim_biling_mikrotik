<?php


namespace App\Traits;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

trait DatabaseErrorHandlerTrait
{
  /**
   * Handle database query exceptions dengan response yang sesuai
   *
   * @param QueryException $e
   * @param string $defaultMessage
   * @return \Illuminate\Http\JsonResponse
   */
  public function handleDatabaseException(QueryException $e, $defaultMessage = 'Database error occurred')
  {
    // Log error untuk debugging
    Log::error('Database Error: ' . $e->getMessage(), [
      'code' => $e->getCode(),
      'file' => $e->getFile(),
      'line' => $e->getLine(),
      'sql' => $e->getSql() ?? 'N/A'
    ]);

    $errorCode = $e->getCode();
    $errorMessage = $this->getDatabaseErrorMessage($errorCode);
    $httpStatus = $this->getDatabaseErrorHttpStatus($errorCode);

    if (method_exists($this, 'errorResponse')) {
      return $this->errorResponse($errorMessage, [$e->getMessage()], $httpStatus);
    }

    // Fallback jika tidak ada ApiResponseTrait
    return response()->json([
      'success' => false,
      'message' => $errorMessage,
      'errors' => [$e->getMessage()],
    ], $httpStatus);
  }

  /**
   * Get user-friendly error message berdasarkan database error code
   *
   * @param string $errorCode
   * @return string
   */
  private function getDatabaseErrorMessage($errorCode)
  {
    $errorMessages = [
      // Integrity constraint violations
      '23000' => 'Data duplikat atau melanggar aturan database',
      '23502' => 'Field yang wajib diisi tidak boleh kosong',
      '23503' => 'Data tidak dapat dihapus karena masih digunakan oleh data lain',
      '23505' => 'Data dengan nilai yang sama sudah ada',
      '23514' => 'Data tidak memenuhi kriteria yang ditetapkan',

      // Transaction errors
      '40001' => 'Deadlock terdeteksi, silakan coba lagi',
      '40002' => 'Serialization failure, silakan coba lagi',
      '40P01' => 'Deadlock terdeteksi saat menunggu resource',

      // Syntax and schema errors
      '42000' => 'Sintaks SQL tidak valid',
      '42S02' => 'Tabel database tidak ditemukan',
      '42S22' => 'Kolom database tidak ditemukan',
      '42S21' => 'Kolom sudah ada',
      '42S01' => 'Tabel sudah ada',

      // Connection and general errors
      'HY000' => 'Error umum database',
      'HY001' => 'Alokasi memori database gagal',
      'HY003' => 'Parameter tidak valid',
      'HY004' => 'Tipe data SQL tidak valid',
      'HY007' => 'Statement tidak siap untuk eksekusi',
      'HY010' => 'Sequence error dalam fungsi',
      'HY013' => 'Manajemen memori database error',

      // MySQL specific errors
      '08S01' => 'Koneksi database terputus',
      '22001' => 'Data terlalu panjang untuk kolom',
      '22003' => 'Nilai numerik di luar rentang',
      '22007' => 'Format datetime tidak valid',
      '22012' => 'Pembagian dengan nol',

      // PostgreSQL specific errors
      '08001' => 'Tidak dapat terhubung ke database',
      '08006' => 'Koneksi database gagal',
      '53300' => 'Terlalu banyak koneksi database',

      // Access and permission errors
      '28000' => 'Akses ditolak untuk user database',
      '42501' => 'Tidak memiliki hak akses yang cukup',
    ];

    return $errorMessages[$errorCode] ?? 'Terjadi kesalahan pada database';
  }

  /**
   * Get HTTP status code berdasarkan database error code
   *
   * @param string $errorCode
   * @return int
   */
  private function getDatabaseErrorHttpStatus($errorCode)
  {
    $statusCodes = [
      // Client errors (4xx)
      '23000' => 400, // Bad Request - Duplicate/Constraint
      '23502' => 400, // Bad Request - Not null violation
      '23503' => 409, // Conflict - Foreign key violation
      '23505' => 409, // Conflict - Unique violation
      '23514' => 400, // Bad Request - Check violation
      '42000' => 400, // Bad Request - Syntax error
      '42S02' => 404, // Not Found - Table doesn't exist
      '42S22' => 400, // Bad Request - Column doesn't exist
      '42S21' => 409, // Conflict - Column already exists
      '42S01' => 409, // Conflict - Table already exists
      '28000' => 403, // Forbidden - Access denied
      '42501' => 403, // Forbidden - Insufficient privilege
      '22001' => 400, // Bad Request - Data too long
      '22003' => 400, // Bad Request - Numeric value out of range
      '22007' => 400, // Bad Request - Invalid datetime
      '22012' => 400, // Bad Request - Division by zero

      // Server errors (5xx) - mostly for retryable errors
      '40001' => 409, // Conflict - Deadlock (retryable)
      '40002' => 409, // Conflict - Serialization failure (retryable)
      '40P01' => 409, // Conflict - Deadlock (retryable)
      '08S01' => 503, // Service Unavailable - Connection lost
      '08001' => 503, // Service Unavailable - Can't connect
      '08006' => 503, // Service Unavailable - Connection failure
      '53300' => 503, // Service Unavailable - Too many connections
    ];

    return $statusCodes[$errorCode] ?? 500; // Default to Internal Server Error
  }

  /**
   * Check if database error is retryable
   *
   * @param string $errorCode
   * @return bool
   */
  public function isDatabaseErrorRetryable($errorCode)
  {
    $retryableErrors = [
      '40001', // Deadlock
      '40002', // Serialization failure
      '40P01', // Deadlock detected
      '08S01', // Connection lost
      '08001', // Can't connect
      '08006', // Connection failure
      '53300', // Too many connections
    ];

    return in_array($errorCode, $retryableErrors);
  }

  /**
   * Get suggestion untuk user berdasarkan error code
   *
   * @param string $errorCode
   * @return string|null
   */
  public function getDatabaseErrorSuggestion($errorCode)
  {
    $suggestions = [
      '23000' => 'Periksa data yang akan disimpan, mungkin ada duplikasi',
      '23503' => 'Hapus data terkait terlebih dahulu sebelum menghapus data ini',
      '40001' => 'Silakan tunggu sebentar dan coba lagi',
      '40002' => 'Silakan tunggu sebentar dan coba lagi',
      '42S02' => 'Hubungi administrator sistem untuk memperbaiki struktur database',
      '42S22' => 'Hubungi administrator sistem untuk memperbaiki struktur database',
      '08S01' => 'Periksa koneksi internet dan coba lagi',
      '22001' => 'Kurangi panjang karakter yang diinput',
      '22007' => 'Periksa format tanggal dan waktu yang diinput',
    ];

    return $suggestions[$errorCode] ?? null;
  }
}
