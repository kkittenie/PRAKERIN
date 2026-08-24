<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
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