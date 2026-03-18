<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditSearch
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('api/*') && $request->isMethod('GET')) {
            $this->logSearchIfApplicable($request);
        }

        return $response;
    }

    private function logSearchIfApplicable(Request $request): void
    {
        $searchRoutes = [
            'api/marketplace/talent/search',
            'api/marketplace/jobs/search',
        ];

        $path = $request->path();

        foreach ($searchRoutes as $route) {
            if (str_contains($path, $route)) {
                $user = $request->user();
                if ($user) {
                    $entityType = str_contains($route, 'talent') ? 'candidate' : 'job';
                    AuditLog::logSearch($user->id, $entityType, $request->query()->toArray());
                }
                break;
            }
        }
    }
}
