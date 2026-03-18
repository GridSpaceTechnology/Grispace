<?php

namespace App\Http\Middleware;

use App\Models\Consent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDataProcessingConsent
{
    public function handle(Request $request, Closure $next, string $consentType): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        if (! Consent::isGranted($request->user(), $consentType)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Consent required',
                    'message' => 'Data processing consent is required for this action',
                    'consent_type' => $consentType,
                ], 403);
            }

            return redirect()->route('consent.required', ['type' => $consentType]);
        }

        return $next($request);
    }
}
