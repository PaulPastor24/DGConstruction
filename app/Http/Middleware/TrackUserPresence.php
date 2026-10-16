<?php

namespace App\Http\Middleware;

use App\Services\UserPresence;
use Closure;
use Illuminate\Http\Request;

class TrackUserPresence
{
    public function handle(Request $request, Closure $next)
    {
        $presence = app(UserPresence::class);
        $before = $request->user();
        $sessionId = $request->session()->getId();
        if ($before && $before->is_active) {
            $presence->touch((int) $before->getKey(), $sessionId);
        }
        $response = $next($request);
        $after = $request->user();
        $changed = $before?->getKey() !== $after?->getKey() || $sessionId !== $request->session()->getId();
        if ($changed && $before) {
            $presence->forget((int) $before->getKey(), $sessionId);
        }
        if ($changed && $after && $after->is_active) {
            $presence->touch((int) $after->getKey(), $request->session()->getId());
        }

        return $response;
    }
}
