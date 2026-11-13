<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\MemberDeviceToken;
use App\Models\UserDeviceToken;
use App\Support\ActivityLogHelper;
use App\Support\NotificationHelper;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Halaman Semua Notifikasi.
     */
    public function index()
    {
        try {
            $notifications = NotificationHelper::getLiveNotifications(50);

            return view('pages.notifications.index', [
                'title' => 'Semua Notifikasi',
                'notifications' => $notifications,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Endpoint API JSON untuk Polling Real-time Notifikasi (tanpa reload).
     */
    public function unreadJson()
    {
        try {
            $notifications = NotificationHelper::getLiveNotifications(15);
            $unreadCount = count(array_filter($notifications, fn ($n) => !$n['is_read']));

            return response()->json([
                'status' => 'success',
                'unread_count' => $unreadCount,
                'notifications' => $notifications,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tandai notifikasi sebagai sudah dibaca.
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            if (str_starts_with($id, 'db_') || is_numeric($id)) {
                $cleanId = str_replace('db_', '', $id);
                // whereNull('member_id') = cuma boleh nandain notif admin, bukan
                // notif pribadi member yang kebetulan lolos ke daftar ini.
                AppNotification::where('id', $cleanId)->whereNull('member_id')->update(['is_read' => true]);
            }

            $readNotifs = session()->get('read_notifications', []);
            $readNotifs[] = (string) $id;
            session()->put('read_notifications', array_values(array_unique($readNotifs)));

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'success']);
            }

            return back()->with('success', 'Notifikasi ditandai sudah dibaca');
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Daftarkan token FCM device ini ke user yang sedang login, supaya HP-nya
     * ikut dapat push notification (pesanan masuk, stok menipis, dsb).
     */
    public function daftarkanToken(Request $request)
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string|max:255',
                'platform' => 'nullable|string|max:20',
            ]);

            // Satu device cuma boleh jadi milik satu akun. Kalau HP ini
            // sebelumnya dipakai login sebagai member (atau user lain),
            // lepas dulu supaya push-nya tidak nyasar ke akun yang salah.
            MemberDeviceToken::where('token', $validated['token'])->delete();

            UserDeviceToken::updateOrCreate(
                ['token' => $validated['token']],
                [
                    'user_id' => $request->user()->id,
                    'platform' => $validated['platform'] ?? 'android',
                    'last_used_at' => now(),
                ]
            );

            // Disimpan di session supaya waktu logout token device ini bisa
            // dihapus (lihat AuthenticatedSessionController::destroy).
            session()->put('fcm_token', $validated['token']);

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Hapus token FCM device ini (dipanggil saat logout / lepas device).
     */
    public function hapusToken(Request $request)
    {
        try {
            $token = $request->input('token')
                ?: $request->input('fcm_token')
                ?: $request->cookie('fcm_token')
                ?: $request->cookie('fcm_device_token')
                ?: ($request->session()->has('fcm_token') ? $request->session()->get('fcm_token') : null);

            if (!empty($token)) {
                UserDeviceToken::where('token', $token)->delete();
                MemberDeviceToken::where('token', $token)->delete();
            }

            if ($request->user()) {
                UserDeviceToken::where('user_id', $request->user()->id)->delete();
            }

            $request->session()->forget('fcm_token');

            return response()->json(['status' => 'success'])
                ->withoutCookie('fcm_token')
                ->withoutCookie('fcm_device_token');
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function markAllAsRead(Request $request)
    {
        try {
            AppNotification::where('is_read', false)->whereNull('member_id')->update(['is_read' => true]);

            $allLive = NotificationHelper::getLiveNotifications(100);
            $allKeys = array_map('strval', array_column($allLive, 'id'));
            session()->put('read_notifications', array_values(array_unique($allKeys)));

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'success']);
            }

            return back()->with('success', 'Semua notifikasi ditandai sudah dibaca');
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
            return ActivityLogHelper::handleException($e);
        }
    }
}