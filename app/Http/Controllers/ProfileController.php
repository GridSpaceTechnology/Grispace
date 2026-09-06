<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\ProfileCompletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->safe()->except(['profile_photo']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                try {
                    Storage::disk('public')->delete($user->profile_photo_path);
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            try {
                $path = $request->file('profile_photo')->store('profile-photos', 'public');
            } catch (\Throwable $e) {
                report($e);

                return Redirect::route('profile.edit')->withErrors([
                    'profile_photo' => __('There was a problem saving your profile picture. Please try again.'),
                ]);
            }

            if ($path === false) {
                return Redirect::route('profile.edit')->withErrors([
                    'profile_photo' => __('There was a problem saving your profile picture. Please try again.'),
                ]);
            }

            $user->profile_photo_path = $path;
        }

        $user->save();

        if ($user->isCandidate()) {
            app(ProfileCompletionService::class)->sync($user);
        }

        return back()->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroyPhoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->profile_photo_path) {
            try {
                Storage::disk('public')->delete($user->profile_photo_path);
            } catch (\Throwable $e) {
                report($e);
            }

            $user->profile_photo_path = null;
            $user->save();

            if ($user->isCandidate()) {
                app(ProfileCompletionService::class)->sync($user);
            }
        }

        return back()->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
