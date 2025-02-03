<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function editProfile()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        // Validasi input
        $request->validate([
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Ambil pengguna yang sedang login
        $user = Auth::user();

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Hapus gambar profil lama jika ada
            if ($user->profile_picture) {
                Storage::delete($user->profile_picture);
            }

            // Simpan gambar profil baru
            $path = $request->file('profile_picture')->store('public/profile_pictures');
            $user->profile_picture = $path;
        }

        // Jika tidak ada gambar baru, biarkan profile_picture tidak berubah
        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Foto Profil berhasil diupdate!');
    }

    public function deletePhoto()
    {
        try {
            $user = Auth::user();

            // Hapus file foto dari storage
            if ($user->profile_picture) {
                Storage::delete($user->profile_picture);
            }

            // Reset field profile_picture
            $user->profile_picture = null;
            $user->save();

            return redirect()->back()->with('success', 'Foto profil berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus foto profil.');
        }
    }
}
