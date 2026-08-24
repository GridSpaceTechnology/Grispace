<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = auth()->user();

        if ($user->isSuspendedForUnverifiedEmail()) {
            return redirect()
                ->route('verification.notice')
                ->with('suspended', __('Your account was deactivated because your email address was not verified. Please verify your email below to reactivate it.'));
        }

        $request->session()->regenerate();

        return redirect()->intended(function () {
            $user = auth()->user();

            if ($user->role === 'admin') {
                return '/dashboard';
            }

            if ($user->onboarding_completed) {
                return '/dashboard';
            }

            return $user->role === 'candidate'
                ? '/candidate/onboarding'
                : '/employer/onboarding';
        });
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
