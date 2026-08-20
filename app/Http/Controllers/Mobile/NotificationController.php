<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\MemberDeviceToken;
use App\Support\ActivityLogHelper;
use App\Support\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Halaman daftar semua notifikasi member (lonceng di beranda ngarah
     * ke sini). Buka halaman ini otomatis nandain semua notif dibaca,
     * sama kaya WA.
     */
    public function index()
    {
        try {
            $member = Auth::guard('member')->user();

            $notifikasi = NotificationHelper::getNotifikasiMember($member->id);

            AppNotification::untukMember($member->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return view('mobile.notifikasi.index', [
                'member' => $member,
                'notifikasi' => $notifikasi,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Dipanggil lewat fetch() polling di beranda buat nampilin badge titik
     * merah di lonceng — cukup jumlahnya aja, ringan.
     */
    public function unreadCount()
    {
        $member = Auth::guard('member')->user();

        return response()->json([
            'unread' => NotificationHelper::hitungBelumDibacaMember($member->id),
        ]);
    }

    /**
     * Tandai satu notifikasi dibaca (dipanggil pas member tap salah satu
     * item, sebelum diarahkan ke url tujuannya).
     */
    public function read(AppNotification $notification)
    {
        $member = Auth::guard('member')->user();

        abort_unless($notification->member_id === $member->id, 404);

        $notification->update(['is_read' => true]);

        return response()->json(['status' => 'success']);
    }

    public function readAll()
    {
        $member = Auth::guard('member')->user();

        AppNotification::untukMember($member->id)->where('is_read', false)->update(['is_read' => true]);

        return back();
    }

    /**
     * Dipanggil dari JS di WebView (window.Android.getFcmToken(), lihat
     * AndroidBridge.kt) setelah member login, buat daftarin token FCM
     * device-nya supaya bisa dikirimin push notification.
     */
    public function daftarkanToken(Request $request)
    {
        $member = Auth::guard('member')->user();

        $validated = $request->validate([
            'token' => 'required|string|max:255',
            'platform' => 'nullable|string|max:20',
        ]);

        MemberDeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'member_id' => $member->id,
                'platform' => $validated['platform'] ?? 'android',
                'last_used_at' => now(),
            ]
        );

        return response()->json(['status' => 'success']);
    }
}
