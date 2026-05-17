<?php
// app/Http/Controllers/Api/NotificationController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::forUser(Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return NotificationResource::collection($notifications);
    }

    public function count()
    {
        $count = Notification::forUser(Auth::id())->unread()->count();

        return response()->json([
            'status' => 'success',
            'data'   => ['count' => $count],
        ]);
    }

    public function markRead(string $id)
    {
        $notification = Notification::forUser(Auth::id())
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json(['status' => 'error', 'message' => 'Notifikasi tidak ditemukan.'], 404);
        }

        $notification->markAsRead();

        return response()->json(['status' => 'success', 'message' => 'Notifikasi ditandai sudah dibaca.']);
    }

    public function markAllRead()
    {
        Notification::forUser(Auth::id())->unread()->update(['read_at' => now()]);

        return response()->json(['status' => 'success', 'message' => 'Semua notifikasi ditandai sudah dibaca.']);
    }
}