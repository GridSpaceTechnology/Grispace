<?php

namespace App\Http\Controllers;

use App\Http\Resources\CandidateCardResource;
use App\Models\Job;
use App\Models\User;
use App\Services\CandidateSearchService;
use App\Services\MatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TalentController extends Controller
{
    public function __construct(
        private CandidateSearchService $searchService,
        private MatchService $matchService,
    ) {}

    public function search(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'keywords' => 'nullable|string|max:100',
            'skills' => 'nullable|array',
            'skill_count_min' => 'nullable|integer|min:1',
            'experience_level' => 'nullable|in:entry,junior,mid,senior,lead,principal,executive',
            'years_of_experience_min' => 'nullable|integer|min:0',
            'years_of_experience_max' => 'nullable|integer|min:0',
            'work_preference' => 'nullable|in:remote,hybrid,onsite,flexible',
            'availability' => 'nullable|in:immediately,2_weeks,1_month,2_months,3_months,passive',
            'salary_expectation_max' => 'nullable|numeric|min:0',
            'location_country' => 'nullable|string|max:100',
            'industries' => 'nullable|array',
            'industries.*' => 'string|max:100',
            'signal_types' => 'nullable|array',
            'signal_types.*' => 'string',
            'sort_by' => 'nullable|in:relevance,experience_desc,experience_asc,salary_asc,salary_desc,availability,newest',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = $this->searchService->search($filters);

        $sortBy = $filters['sort_by'] ?? 'relevance';
        $this->searchService->sort($query, $sortBy);

        $perPage = $filters['per_page'] ?? 15;
        $candidates = $query->paginate($perPage);

        return response()->json([
            'data' => CandidateCardResource::collection($candidates),
            'meta' => [
                'current_page' => $candidates->currentPage(),
                'last_page' => $candidates->lastPage(),
                'per_page' => $candidates->perPage(),
                'total' => $candidates->total(),
            ],
        ]);
    }

    public function searchWithMatch(Request $request, Job $job): JsonResponse
    {
        $filters = $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|in:match_score,experience_desc,salary_asc,newest',
        ]);

        $perPage = $filters['per_page'] ?? 15;
        $sortBy = $filters['sort_by'] ?? 'match_score';

        $matches = $this->matchService->getTopMatchingCandidates($job, 100);

        if ($sortBy === 'match_score') {
            $matches = $matches->sortByDesc('match_score');
        } elseif ($sortBy === 'experience_desc') {
            $matches = $matches->sortByDesc(fn ($m) => $m['candidate']->candidateProfile?->years_of_experience ?? 0);
        } elseif ($sortBy === 'salary_asc') {
            $matches = $matches->sortBy(fn ($m) => $m['candidate']->candidateProfile?->salary_expectation_min ?? PHP_INT_MAX);
        } elseif ($sortBy === 'newest') {
            $matches = $matches->sortByDesc(fn ($m) => $m['candidate']->created_at);
        }

        $paginated = $matches->forPage(1, $perPage);
        $candidates = $paginated->pluck('candidate');

        $candidates->each(function ($candidate) use ($matches) {
            $match = $matches->firstWhere('candidate.id', $candidate->id);
            $candidate->match_score = $match['match_score'] ?? 0;
        });

        return response()->json([
            'data' => CandidateCardResource::collection($candidates)->resolve(),
            'match_data' => $matches->take($perPage)->mapWithKeys(fn ($m) => [$m['candidate']->id => ['match_score' => $m['match_score']]]),
            'meta' => [
                'current_page' => 1,
                'last_page' => ceil($matches->count() / $perPage),
                'per_page' => $perPage,
                'total' => $matches->count(),
            ],
        ]);
    }

    public function getFilters(): JsonResponse
    {
        return response()->json([
            'experience_levels' => $this->searchService->getDistinctValues('experience_level'),
            'work_preferences' => $this->searchService->getDistinctValues('work_preference'),
            'availability' => $this->searchService->getDistinctValues('availability'),
            'industries' => $this->searchService->getDistinctValues('industry'),
            'location_countries' => $this->searchService->getDistinctValues('location_country'),
        ]);
    }

    public function getCandidate(string $candidate): JsonResponse
    {
        $candidate = User::where('role', 'candidate')
            ->with([
                'candidateProfile',
                'candidateSkills.skill',
                'candidateSignals.category',
                'candidateExperience',
                'candidateEducation',
                'candidateAssessment',
            ])
            ->findOrFail($candidate);

        return response()->json([
            'id' => $candidate->id,
            'name' => $candidate->name,
            'profile' => $candidate->candidateProfile,
            'skills' => $candidate->candidateSkills->map(fn ($s) => [
                'id' => $s->skill_id,
                'name' => $s->skill?->name ?? $s->skill_name,
                'proficiency' => $s->proficiency_level,
            ]),
            'signals' => $candidate->candidateSignals->groupBy('signal_type')->map(fn ($signals) => $signals->pluck('value')),
            'experience' => $candidate->candidateExperience,
            'education' => $candidate->candidateEducation,
            'assessment' => $candidate->candidateAssessment,
        ]);
    }
}
