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

            if ($user) {
                activity()
                    ->causedBy($user)
                    ->performedOn($user)
                    ->withProperties([
                        'user' => $user->name,
                        'email' => $user->email,
                    ])
                    ->log('Logout dari sistem');

                // Dapatkan FCM token device ini dari request input, cookie, atau session
                $token = $request->input('fcm_token')
                    ?: $request->cookie('fcm_token')
                    ?: $request->cookie('fcm_device_token')
                    ?: ($request->session()->has('fcm_token') ? $request->session()->get('fcm_token') : null);

                if (!empty($token)) {
                    UserDeviceToken::where('token', $token)->delete();
                }

                // Pengaman utama: Hapus semua token push notification milik akun admin ini saat logout,
                // sehingga HP/tab tidak lagi menerima push notifikasi pesanan/alert admin.
                // Saat user login kembali, device akan otomatis mendaftarkan tokennya lagi.
                UserDeviceToken::where('user_id', $user->id)->delete();
            }

            Auth::guard('web')->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->withoutCookie('fcm_token')
                ->withoutCookie('fcm_device_token');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}