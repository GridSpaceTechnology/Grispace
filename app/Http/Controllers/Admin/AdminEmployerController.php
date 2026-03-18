<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\EmployerVerified;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminEmployerController extends Controller
{
    public function index(): View
    {
        $employers = User::with(['company', 'employerProfile'])
            ->where('role', 'employer')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.employers.index', ['employers' => $employers]);
    }

    public function verify(User $employer): RedirectResponse
    {
        $company = $employer->company;

        if (! $company) {
            return back()->with('error', 'Employer does not have a company profile.');
        }

        $company->update(['is_verified' => ! $company->is_verified]);

        $employer->notify(new EmployerVerified($company->is_verified));

        $status = $company->is_verified ? 'verified' : 'unverified';

        return back()->with('success', "Employer {$status} successfully.");
    }
}
