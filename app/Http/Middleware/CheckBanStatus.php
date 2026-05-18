<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckBanStatus
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Skip untuk admin — admin tidak bisa di-ban
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        if ($user->isBanned()) {
            // Logout paksa
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($user->isBannedPermanently()) {
                $message = 'Akun kamu telah dinonaktifkan secara permanen karena melanggar syarat & ketentuan EduLiving. '
                    . ($user->ban_reason ? 'Alasan: ' . $user->ban_reason : '');
            } else {
                $message = 'Akun kamu ditangguhkan sementara hingga ' . $user->banned_until->format('d M Y, H:i') . '. '
                    . ($user->ban_reason ? 'Alasan: ' . $user->ban_reason : '');
            }

            // Untuk API request — return JSON
            if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $message,
                    'banned'  => true,
                    'ban_type' => $user->ban_type,
                    'banned_until' => $user->banned_until?->toIso8601String(),
                ], 403);
            }

            return redirect()->route('login')->with('ban_error', $message);
        }

        return $next($request);
    }
}