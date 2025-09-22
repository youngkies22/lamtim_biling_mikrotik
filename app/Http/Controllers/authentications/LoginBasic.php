<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginBasic extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.authentications.auth-login-basic', ['pageConfigs' => $pageConfigs]);
  }
  public function login(Request $request)
  {

    try {
      // Validasi input form
      $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
      ]);

      // Apakah checkbox 'remember me' dicentang?
      $remember = $request->has('remember');

      // Coba autentikasi
      if (Auth::attempt($credentials, $remember)) {
        $request->session()->regenerate(); // Hindari session fixation
        return redirect()->intended('/');  // Ke halaman yang diminta sebelum login
      }

      // Jika gagal login
      return back()->withErrors([
        'email' => 'Email atau password salah.',
      ])->withInput();
    } catch (\Throwable $e) {
      dd($e->getMessage());
      // Tangani error tak terduga
      report($e);
      return back()->withErrors([
        'email' => 'Terjadi kesalahan saat login. Silakan coba lagi nanti.' . $e->getMessage(),
      ]);
    }
  }

  public function logout(Request $request)
  {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
  }
}
