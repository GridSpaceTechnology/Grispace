<?php

namespace App\Http\Middleware;

use Closure;

class RoleMiddleware
{
    public function handle($request, Closure $next, string $role)
    {
        if (! auth()->check()) {
            return redirect('/login');
        }

        if (auth()->user()->is_suspended) {
            abort(403, 'Account suspended.');
        }

        if (auth()->user()->role !== $role) {
            abort(403);
        }

        return $next($request);
    }
}
