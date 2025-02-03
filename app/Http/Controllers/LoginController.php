<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->intended('/CPL/index');
        }

        return view('login.index', [
            "title" => "Login",
            "active" => "login"
        ]);
    }

    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password harus diisi',
        ]);

        // Cek apakah email terdaftar
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email tidak terdaftar']);
        }

        // Cek kredensial
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();

            // Jika login berhasil
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect' => '/CPL/index'
                ]);
            }

            return redirect()->intended('/CPL/index');
        }

        // Jika password salah
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Password yang Anda masukkan salah'
            ], 422);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['password' => 'Password yang Anda masukkan salah']);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Anda berhasil logout');
    }

    public function callName()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return redirect('/')->with('error', 'Silakan login terlebih dahulu');
            }
            return view('layouts_baru', compact('user'));
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }
}
