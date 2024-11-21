<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()
            ->userNotifications()
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    public function unread()
    {
        $notifications = Auth::user()
            ->userNotifications()
            ->where('is_read', false)
            ->latest()
            ->get();

        return response()->json($notifications);
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Marked as read']);
    }

    public function markAllAsRead()
    {
        Auth::user()
            ->userNotifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted']);
    }
} 