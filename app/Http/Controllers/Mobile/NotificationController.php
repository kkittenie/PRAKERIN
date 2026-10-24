<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\MemberDeviceToken;
use App\Models\UserDeviceToken;
use App\Support\ActivityLogHelper;
use App\Support\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        try {
            $member = Auth::guard('member')->user();

            $notifikasi = NotificationHelper::getNotifikasiMember($member->id);

            return view('mobile.notifikasi.index', [
                'member' => $member,
                'notifikasi' => $notifikasi,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
    public function unreadCount()
    {
        $member = Auth::guard('member')->user();

        return response()->json([
            'unread' => NotificationHelper::hitungBelumDibacaMember($member->id),
        ]);
    }

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

        return response()->json(['status' => 'success']);
    }
    public function daftarkanToken(Request $request)
    {
        $member = Auth::guard('member')->user();

        $validated = $request->validate([
            'token' => 'required|string|max:255',
            'platform' => 'nullable|string|max:20',
        ]);

        // Satu device cuma boleh jadi milik satu akun: kalau HP ini tadinya
        // dipakai login admin/kasir, lepas dulu supaya notif pesanan masuk
        // (khusus admin) tidak ikut muncul di HP member ini.
        UserDeviceToken::where('token', $validated['token'])->delete();

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