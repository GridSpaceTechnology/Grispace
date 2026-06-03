<?php

namespace App\Http\Controllers\Employer\ATS;

use App\Http\Controllers\Controller;
use App\Models\CandidateRating;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidateRatingController extends Controller
{
    public function store(Request $request, JobApplication $application)
    {
        if ($application->job->employer_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'category' => 'required|in:skills,communication,experience,culture_fit,overall',
            'review' => 'nullable|string|max:2000',
        ]);

        $rating = CandidateRating::updateOrCreate(
            [
                'application_id' => $application->id,
                'employer_id' => Auth::id(),
                'category' => $validated['category'],
            ],
            [
                'rating' => $validated['rating'],
                'review' => $validated['review'] ?? null,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json($rating, 201);
        }

        return redirect()->back()->with('success', 'Rating saved successfully.');
    }

    public function show(JobApplication $application)
    {
        if ($application->job->employer_id !== Auth::id()) {
            abort(403);
        }

        $ratings = $application->ratings()
            ->where('employer_id', Auth::id())
            ->get()
            ->keyBy('category');

        return response()->json($ratings);
    }
}
