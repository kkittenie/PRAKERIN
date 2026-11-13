<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ShiftKasir;
use App\Models\UserDeviceToken;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Logout. Kalau kasir masih punya shift berjalan, diarahkan dulu ke halaman
     * selesai shift untuk mengisi sisa uang di laci.
     */
    public function destroy(Request $request)
    {
        try {
            $user = Auth::user();

            if ($user && ShiftKasir::aktifUntuk($user->id)) {
                return redirect()->route('shift.tutup')
                    ->with('error', 'Selesaikan shift dulu (isi sisa uang di laci) sebelum keluar.');
            }

            return $this->keluarkan($request);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Proses logout sebenarnya (hapus token push, akhiri sesi). Dipakai juga oleh
     * halaman selesai shift saat kasir memilih "Selesai Shift & Keluar".
     */
    public function keluarkan(Request $request)
    {
        try {
            $user = Auth::user();
            activity()
                ->causedBy($user)
                ->performedOn($user)
                ->withProperties([
                    'user' => $user->name,
                    'email' => $user->email,
                ])
                ->log('Logout dari sistem');

            // Hapus token push notification device ini, supaya setelah logout
            // HP-nya tidak lagi menerima notif pesanan/alert milik akun ini.
            if ($request->session()->has('fcm_token')) {
                UserDeviceToken::where('token', $request->session()->get('fcm_token'))->delete();
            }

            Auth::guard('web')->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return redirect()->route('admin.login');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}