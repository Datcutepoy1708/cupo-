<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Lấy danh sách thông báo của người dùng hiện tại (kèm phân trang).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['data' => [], 'unread_count' => 0]);
        }

        $limit = (int) $request->query('limit', 15);
        $notifications = $user->notifications()->paginate($limit);

        return response()->json([
            'data' => $notifications->items(),
            'unread_count' => $user->unreadNotifications()->count(),
            'current_page' => $notifications->currentPage(),
            'last_page' => $notifications->lastPage(),
            'total' => $notifications->total(),
        ]);
    }

    /**
     * Lấy số lượng thông báo chưa đọc.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        $unreadCount = $user ? $user->unreadNotifications()->count() : 0;

        return response()->json([
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Đánh dấu 1 thông báo là đã đọc.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notification = $user->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Đánh dấu tất cả thông báo là đã đọc.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }
}
