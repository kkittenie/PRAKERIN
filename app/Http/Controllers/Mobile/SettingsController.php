<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        try {
            return view('mobile.settings.index');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function ubahPassword()
    {
        try {
            $member = Auth::guard('member')->user();

            return view('mobile.settings.ubah-password', [
                // Akun yang daftar lewat Google belum punya password => langsung buat password baru
                'belumPunyaPassword' => $member && is_null($member->password),
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Step 1: Verifikasi password lama sebelum lanjut ke step 2 (input password baru).
     */
    public function verifikasiPasswordLama(Request $request)
    {
        try {
            $request->validate([
                'password_lama' => ['required', 'string'],
            ], [
                'password_lama.required' => 'Please enter your current password.',
            ]);

            $member = Auth::guard('member')->user();

            if (is_null($member->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account does not have a password yet. Please create a new password directly.',
                ], 422);
            }

            if (!Hash::check($request->password_lama, $member->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The current password you entered is incorrect.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Current password verified.',
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Step 2: Simpan password baru.
     * - Akun biasa: password lama divalidasi ulang di sini untuk keamanan.
     * - Akun Google yang belum punya password: cukup password baru (tanpa password lama).
     */
    public function updatePassword(Request $request)
    {
        try {
            $member = Auth::guard('member')->user();
            $belumPunyaPassword = is_null($member->password);

            $rules = [
                'password_baru' => ['required', 'confirmed', 'min:6'],
            ];
            $messages = [
                'password_baru.required' => 'Please enter your new password.',
                'password_baru.min' => 'New password must be at least 6 characters.',
                'password_baru.confirmed' => 'New password confirmation does not match.',
            ];

            if (!$belumPunyaPassword) {
                $rules['password_lama'] = ['required', 'string'];
                $messages['password_lama.required'] = 'Please enter your current password.';
            }

            $data = $request->validate($rules, $messages);

            if (!$belumPunyaPassword && !Hash::check($data['password_lama'], $member->password)) {
                return back()->withErrors(['password_lama' => 'The current password you entered is incorrect.']);
            }

            $member->update([
                'password' => Hash::make($data['password_baru']),
            ]);

            return redirect()->route('member.settings.index')->with(
                'success',
                $belumPunyaPassword
                    ? 'Password created successfully.'
                    : 'Your password has been changed successfully.'
            );
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}