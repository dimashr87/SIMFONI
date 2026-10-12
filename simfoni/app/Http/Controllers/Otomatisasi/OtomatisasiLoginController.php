<?php

namespace App\Http\Controllers\Otomatisasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Login khusus menu Otomatisasi 
 */

class OtomatisasiLoginController extends Controller
{

    public function show()
    {
        if (Auth::check()) {
            return redirect()->route('otomatisasi.dashboard');
        }

        return view('otomatisasi.login');
    }

    public function login(Request $request)
    {
        // jika user sudah login, langsung arahkan ke dashboard
        if (Auth::check()) {
            return redirect()->route('otomatisasi.dashboard');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('otomatisasi.dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak sesuai.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('otomatisasi.login');
    }
}
