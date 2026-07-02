<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateLastSeen
{
    /**
     * Throttle update last_seen_at agar tidak terlalu sering hit database.
     * Hanya update jika sudah lewat 1 menit dari update terakhir.
     */
    protected const THROTTLE_MINUTES = 1;

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            $shouldUpdate = ! $user->last_seen_at
                || $user->last_seen_at->lt(now()->subMinutes(self::THROTTLE_MINUTES));

            if ($shouldUpdate) {
                // updateQuietly: tidak trigger event model, tidak update updated_at
                $user->timestamps = false;
                $user->update(['last_seen_at' => now()]);
            }
        }

        return $next($request);
    }
}
