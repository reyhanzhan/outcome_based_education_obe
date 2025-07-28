<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['error' => 'Silakan login terlebih dahulu.']);
        }

        $user = Auth::user();
        if (!$user || !($user instanceof User)) {
            Log::error('User not found or invalid instance', ['user' => $user, 'class' => get_class($user) ?? 'null']);
            abort(403, 'User tidak ditemukan atau tidak valid.');
        }

        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['error' => 'Silakan login terlebih dahulu.']);
        }

        $user = Auth::user();
        if (!$user || !($user instanceof User)) {
            Log::error('User not found or invalid instance during update', ['user' => $user, 'class' => get_class($user) ?? 'null']);
            return redirect()->route('profile.edit')->withErrors(['error' => 'User tidak ditemukan atau tidak valid.']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Show the change password form.
     *
     * @return \Illuminate\View\View
     */
    public function showChangePasswordForm()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['error' => 'Silakan login terlebih dahulu.']);
        }

        $user = Auth::user();
        if (!$user || !($user instanceof User)) {
            Log::error('User not found or invalid instance for password form', ['user' => $user, 'class' => get_class($user) ?? 'null']);
            abort(403, 'User tidak ditemukan atau tidak valid.');
        }

        return view('profile.change-password', compact('user'));
    }

    /**
     * Change the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changePassword(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['error' => 'Silakan login terlebih dahulu.']);
        }

        $user = Auth::user();
        if (!$user || !($user instanceof User)) {
            Log::error('User not found or invalid instance for password change', ['user' => $user, 'class' => get_class($user) ?? 'null']);
            return redirect()->route('profile.edit')->withErrors(['error' => 'User tidak ditemukan atau tidak valid.']);
        }

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('profile.edit')->with('success', 'Password berhasil diperbarui.');
    }
}