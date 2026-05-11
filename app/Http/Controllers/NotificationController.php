<?php
// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Dipakai polling — return jumlah notif belum dibaca
    public function count()
    {
        $count = Notification::forUser(Auth::id())->unread()->count();
        return response()->json(['count' => $count]);
    }

    // Load 15 notif terbaru untuk dropdown
    public function list()
    {
        $notifications = Notification::forUser(Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'message'    => $n->data['message'] ?? '',
                'url'        => $n->data['url'] ?? '/',
                'icon'       => $n->data['icon'] ?? 'fa-bell',
                'color'      => $n->data['color'] ?? 'blue',
                'is_unread'  => $n->isUnread(),
                'time'       => $n->created_at->diffForHumans(),
            ]);

        return response()->json(['notifications' => $notifications]);
    }

    // Tandai satu notif sudah dibaca + redirect ke URL terkait
    public function markRead(string $id)
    {
        $notification = Notification::forUser(Auth::id())->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? '/';
        return redirect($url);
    }

    // Tandai semua sudah dibaca
    public function markAllRead()
    {
        Notification::forUser(Auth::id())
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}