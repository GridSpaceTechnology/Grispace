<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $users = User::with(['candidateProfile', 'company'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.index', ['users' => $users]);
    }

    public function show(User $user): View
    {
        $user->load(['candidateProfile', 'company', 'postedJobs', 'jobApplications']);

        return view('admin.users.show', ['user' => $user]);
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Cannot delete an admin user.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }

    public function verify(User $user): RedirectResponse
    {
        if ($user->role !== 'employer') {
            return back()->with('error', 'Only employer accounts can be verified.');
        }

        $company = $user->company;

        if (! $company) {
            return back()->with('error', 'Employer has no company profile.');
        }

        $company->update(['is_verified' => true]);

        return back()->with('success', 'Employer verified successfully.');
    }
}
