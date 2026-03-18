<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmployerSetupController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();

        if ($user->onboarding_completed) {
            return redirect()->route('employer.dashboard');
        }

        $company = $user->company;

        return view('employer.setup', [
            'company' => $company,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->onboarding_completed) {
            return redirect()->route('employer.dashboard');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'required|string|max:255',
            'company_size' => 'required|string|max:50',
            'location' => 'required|string|max:255',
            'location_country' => 'required|string|max:255',
            'website' => 'nullable|url|max:255',
            'phone_number' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'linkedin_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'culture_description' => 'nullable|string',
            'work_model' => 'required|in:remote,hybrid,onsite',
        ]);

        $companyData = [
            'user_id' => $user->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::random(6),
            'industry' => $validated['industry'],
            'company_size' => $validated['company_size'],
            'location' => $validated['location'],
            'location_country' => $validated['location_country'],
            'website' => $validated['website'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'description' => $validated['description'] ?? null,
            'linkedin_url' => $validated['linkedin_url'] ?? null,
            'instagram_url' => $validated['instagram_url'] ?? null,
            'twitter_url' => $validated['twitter_url'] ?? null,
            'culture_description' => $validated['culture_description'] ?? null,
            'work_model' => $validated['work_model'],
        ];

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('company-logos', 'public');
            $companyData['logo_url'] = $path;
        }

        Company::updateOrCreate(
            ['user_id' => $user->id],
            $companyData
        );

        $user->update(['onboarding_completed' => true]);

        return redirect()->route('employer.dashboard')
            ->with('success', 'Your company profile has been set up successfully!');
    }
}
