<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $key = 'user-online-'.$user->id;
            Cache::put($key, true, now()->addMinutes(5));
            Cache::put('user-last-seen-'.$user->id, now()->toIso8601String(), now()->addDay());
        }

        return $next($request);
    }
}
