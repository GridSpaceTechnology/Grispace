<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CandidatePersonalityProfile;
use App\Models\EmployerCultureProfile;
use Illuminate\View\View;

class PersonalityProfileController extends Controller
{
    public function index(): View
    {
        $candidateProfiles = CandidatePersonalityProfile::where('assessment_completed', true)
            ->with('candidate')
            ->latest()
            ->paginate(20);

        return view('admin.personality.profiles.candidates', compact('candidateProfiles'));
    }

    public function employerProfiles(): View
    {
        $employerProfiles = EmployerCultureProfile::whereNotNull('culture_summary')
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('admin.personality.profiles.employers', compact('employerProfiles'));
    }
}
