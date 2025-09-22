<?php

namespace App\Http\Controllers\Mikrotik;

use App\Http\Controllers\Controller;
use App\Services\MikrotikService;
use Illuminate\Http\Request;


class MikrotikController extends Controller
{
  protected $mikrotikService;

  public function __construct(MikrotikService $mikrotikService)
  {
    $this->mikrotikService = $mikrotikService;
  }
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    return view('content.server.mikrotik');
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'nama' => 'required|string|max:255',
      'kode' => 'required|string|max:255',
      'username' => 'required|string',
      'password' => 'required|string',
      'ip' => 'required|ip',
      'port' => 'required|numeric',
      'isActive' => 'required|boolean',
    ]);
    return $this->mikrotikService->store($validated);
  }
  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    $validated = $request->validate([
      'nama' => 'required|string|max:255',
      'kode' => 'required|string|max:255',
      'username' => 'required|string',
      'password' => 'required|string',
      'ip' => 'required|ip',
      'port' => 'required|numeric',
      'isActive' => 'required|boolean',
    ]);
    return $this->mikrotikService->update($validated, $id);
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id) {}



  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    return $this->mikrotikService->deleteByEncryptedId($id);
  }
  public function json(Request $request)
  {
    return $this->mikrotikService->getDatatablesJson($request);
  }
}
