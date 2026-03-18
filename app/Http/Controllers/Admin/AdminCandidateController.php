<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class AdminCandidateController extends Controller
{
    public function index(): View
    {
        $candidates = User::with(['candidateProfile', 'candidateSkills', 'candidateEducation', 'candidateExperiences'])
            ->where('role', 'candidate')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.candidates.index', ['candidates' => $candidates]);
    }
}
