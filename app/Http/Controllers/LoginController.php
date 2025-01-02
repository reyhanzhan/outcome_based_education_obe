<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    // Menampilkan form login
    public function showLoginForm()
    {

        if (Auth::check()) {
            return redirect()->intended('/CPL/index'); // Ganti dengan rute yang diinginkan
        }

        return view('login.index', [
            "title" => "Login",
            "active" => "login"
        ]);
    }

    // Proses login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/CPL/index');
        }

        return back()->with('loginError', 'Email atau password salah');
    }


    // Proses logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    // fungsi untuk panggil semua data yang ada di user start
    public function callName()
    {
        $user = Auth::user(); // Mengambil data pengguna yang sedang login
        return view('layouts_baru', compact('user'));
    }
    // fungsi untuk panggil semua data yang ada di user end
}
