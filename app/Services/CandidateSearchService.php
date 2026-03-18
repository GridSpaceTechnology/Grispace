<?php

namespace App\Services;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CandidateSearchService
{
    public function search(array $filters): Builder
    {
        $query = User::query()
            ->select('users.*')
            ->join('candidates', 'candidates.user_id', '=', 'users.id')
            ->where('users.role', 'candidate')
            ->where('candidates.onboarding_completed', true)
            ->with([
                'candidateProfile',
                'candidateSkills.skill',
                'candidateSignals',
                'candidateAssessment',
            ]);

        $this->applyFilters($query, $filters);

        return $query;
    }

    public function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['keywords'])) {
            $keywords = array_filter(explode(' ', $filters['keywords']));
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhereExists(function ($sub) use ($keyword) {
                        $sub->select(DB::raw(1))
                            ->from('candidate_signals')
                            ->whereColumn('candidate_signals.user_id', 'users.id')
                            ->where('candidate_signals.value', 'like', "%{$keyword}%");
                    });
                }
            });
        }

        if (! empty($filters['skills'])) {
            $skillIds = $this->resolveSkillIds($filters['skills']);
            if (! empty($skillIds)) {
                $query->whereExists(function ($q) use ($skillIds) {
                    $q->select(DB::raw(1))
                        ->from('candidate_skills')
                        ->whereColumn('candidate_skills.candidate_id', 'candidates.id')
                        ->whereIn('skill_id', $skillIds);
                });
            }
        }

        if (! empty($filters['skill_count_min'])) {
            $query->whereExists(function ($q) use ($filters) {
                $q->select(DB::raw(1))
                    ->from('candidate_skills as cs2')
                    ->whereColumn('cs2.candidate_id', 'candidates.id')
                    ->groupBy('cs2.candidate_id')
                    ->havingRaw('COUNT(*) >= ?', [$filters['skill_count_min']]);
            });
        }

        if (! empty($filters['experience_level'])) {
            $query->where('candidates.experience_level', $filters['experience_level']);
        }

        if (! empty($filters['years_of_experience_min'])) {
            $query->where('candidates.years_of_experience', '>=', $filters['years_of_experience_min']);
        }

        if (! empty($filters['years_of_experience_max'])) {
            $query->where('candidates.years_of_experience', '<=', $filters['years_of_experience_max']);
        }

        if (! empty($filters['work_preference'])) {
            $query->where('candidates.work_preference', $filters['work_preference']);
        }

        if (! empty($filters['availability'])) {
            $query->where('candidates.availability', $filters['availability']);
        }

        if (! empty($filters['salary_expectation_max'])) {
            $query->where('candidates.salary_expectation_max', '<=', $filters['salary_expectation_max']);
        }

        if (! empty($filters['location_country'])) {
            $query->where('candidates.location_country', $filters['location_country']);
        }

        if (! empty($filters['industries'])) {
            $query->whereIn('candidates.industry', $filters['industries']);
        }

        if (! empty($filters['signal_types'])) {
            $query->whereExists(function ($q) use ($filters) {
                $q->select(DB::raw(1))
                    ->from('candidate_signals')
                    ->whereColumn('candidate_signals.user_id', 'users.id')
                    ->whereIn('signal_type', $filters['signal_types'])
                    ->whereIn('value', $filters['signal_values'] ?? []);
            });
        }
    }

    public function sort(Builder $query, string $sortBy, ?int $matchScore = null): void
    {
        match ($sortBy) {
            'relevance' => $query->orderByDesc('candidates.profile_completion_percentage'),
            'experience_desc' => $query->orderByDesc('candidates.years_of_experience'),
            'experience_asc' => $query->orderByAsc('candidates.years_of_experience'),
            'salary_asc' => $query->orderByAsc('candidates.salary_expectation_min'),
            'salary_desc' => $query->orderByDesc('candidates.salary_expectation_max'),
            'availability' => $query->orderByRaw("FIELD(candidates.availability, 'immediately','2_weeks','1_month','2_months','3_months','passive')"),
            'newest' => $query->orderByDesc('users.created_at'),
            default => $query->orderByDesc('candidates.profile_completion_percentage'),
        };
    }

    public function getSkillCounts(User $user): array
    {
        $skills = DB::table('candidate_skills')
            ->join('skills', 'skills.id', '=', 'candidate_skills.skill_id')
            ->where('candidate_skills.candidate_id', $user->id)
            ->groupBy('skills.category')
            ->select('skills.category', DB::raw('COUNT(*) as count'))
            ->pluck('count', 'category')
            ->toArray();

        return $skills;
    }

    public function getSignalCounts(User $user): array
    {
        return DB::table('candidate_signals')
            ->where('user_id', $user->id)
            ->groupBy('signal_type')
            ->select('signal_type', DB::raw('COUNT(*) as count'))
            ->pluck('count', 'signal_type')
            ->toArray();
    }

    private function resolveSkillIds(array $skills): array
    {
        $ids = [];
        foreach ($skills as $skill) {
            if (is_numeric($skill)) {
                $ids[] = (int) $skill;
            } else {
                $found = Skill::where('slug', Str::slug($skill))->first();
                if ($found) {
                    $ids[] = $found->id;
                }
            }
        }

        return $ids;
    }

    public function getDistinctValues(string $field): array
    {
        return DB::table('candidates')
            ->whereNotNull($field)
            ->distinct()
            ->pluck($field)
            ->sort()
            ->toArray();
    }
}
