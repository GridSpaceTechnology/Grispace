<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class CandidateProfileController extends Controller
{
    public function show(Request $request, User $candidate)
    {
        $candidate->load([
            'candidateProfile',
            'candidateSkills',
            'candidateExperiences',
            'candidateEducation',
            'candidateAssessment',
        ]);

        return view('candidate.profile.show', [
            'candidate' => $candidate,
        ]);
    }
}
