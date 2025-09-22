<?php

namespace App\Http\Controllers\Mikrotik;

use App\Http\Controllers\Controller;
use App\Services\oltService;
use Illuminate\Http\Request;

class OltController extends Controller
{
  public function __construct(protected oltService $service) {}

  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    return view('content.server.olt');
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
      'ip' => 'required|ip',
      'port' => 'required|numeric',
      'sfp' => 'required|numeric',
      'isActive' => 'required|boolean',
    ]);
    return $this->service->store($validated);
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id) {}

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {

    $validated = $request->validate([
      'nama' => 'required|string|max:255',
      'kode' => 'required|string|max:255',
      'ip' => 'required|ip',
      'port' => 'required|numeric',
      'sfp' => 'required|numeric',
      'isActive' => 'required|boolean',
    ]);
    return $this->service->update($validated, $id);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    return $this->service->deleteByEncryptedId($id);
  }
  public function json(Request $request)
  {
    return $this->service->getDatatablesJson($request);
  }
}
