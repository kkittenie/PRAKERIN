<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function index()
    {
        return view('member.settings.index');
    }

    public function ubahPassword()
    {
        return view('member.settings.ubah-password');
    }

    /**
     * Step 1: Verifikasi password lama sebelum lanjut ke step 2 (input password baru).
     */
    public function verifikasiPasswordLama(Request $request)
    {
        $request->validate([
            'password_lama' => ['required', 'string'],
        ], [
            'password_lama.required' => 'Masukkan password lama kamu.',
        ]);

        $member = Auth::guard('member')->user();

        if (!Hash::check($request->password_lama, $member->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama yang kamu masukkan salah.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password lama terverifikasi.',
        ]);
    }

    /**
     * Step 2: Simpan password baru (password lama tetap divalidasi ulang di sini untuk keamanan).
     */
    public function updatePassword(Request $request)
    {
        $member = Auth::guard('member')->user();

        $data = $request->validate([
            'password_lama' => ['required', 'string'],
            'password_baru' => ['required', 'confirmed', Password::min(6)],
        ], [
            'password_lama.required' => 'Masukkan password lama kamu.',
            'password_baru.required' => 'Masukkan password baru kamu.',
            'password_baru.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (!Hash::check($data['password_lama'], $member->password)) {
            return back()->withErrors(['password_lama' => 'Password lama yang kamu masukkan salah.']);
        }

        $member->update([
            'password' => Hash::make($data['password_baru']),
        ]);

        return redirect()->route('member.settings.index')->with('success', 'Password kamu berhasil diubah.');
    }
}