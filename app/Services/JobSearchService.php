<?php

namespace App\Services;

use App\Models\Job;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class JobSearchService
{
    public function search(array $filters): Builder
    {
        $query = Job::query()
            ->with(['company', 'jobSkills.skill'])
            ->where('status', 'open');

        if (! empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('role', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('industry', 'like', "%{$keyword}%");
            });
        }

        if (! empty($filters['industry'])) {
            $query->where('industry', $filters['industry']);
        }

        if (! empty($filters['employment_type'])) {
            $query->where('employment_type', $filters['employment_type']);
        }

        if (! empty($filters['work_preference'])) {
            $query->where('work_preference', $filters['work_preference']);
        }

        if (! empty($filters['salary_min'])) {
            $query->where('salary_max', '>=', $filters['salary_min']);
        }

        if (! empty($filters['salary_max'])) {
            $query->where('salary_min', '<=', $filters['salary_max']);
        }

        if (! empty($filters['experience_min'])) {
            $query->where('minimum_experience', '>=', $filters['experience_min']);
        }

        if (! empty($filters['skills'])) {
            $skillIds = $this->resolveSkillIds($filters['skills']);
            if (! empty($skillIds)) {
                $query->whereHas('jobSkills', function ($q) use ($skillIds) {
                    $q->whereIn('skill_id', $skillIds);
                });
            }
        }

        if (! empty($filters['company_size'])) {
            $query->whereHas('company', function ($q) use ($filters) {
                $q->where('company_size', $filters['company_size']);
            });
        }

        if (! empty($filters['is_verified'])) {
            $query->whereHas('company', function ($q) {
                $q->where('is_verified', true);
            });
        }

        return $query;
    }

    public function getUniqueIndustries(): Collection
    {
        return Job::where('status', 'open')
            ->whereNotNull('industry')
            ->distinct()
            ->pluck('industry')
            ->sort();
    }

    public function getUniqueEmploymentTypes(): Collection
    {
        return Job::where('status', 'open')
            ->whereNotNull('employment_type')
            ->distinct()
            ->pluck('employment_type')
            ->sort();
    }

    public function getUniqueWorkPreferences(): Collection
    {
        return Job::where('status', 'open')
            ->whereNotNull('work_preference')
            ->distinct()
            ->pluck('work_preference')
            ->sort();
    }

    public function getPopularSkills(int $limit = 20): Collection
    {
        return Skill::where('is_active', true)
            ->withCount('jobSkills')
            ->orderByDesc('job_skills_count')
            ->limit($limit)
            ->get();
    }

    private function resolveSkillIds(array $skills): array
    {
        $ids = [];
        foreach ($skills as $skill) {
            if (is_numeric($skill)) {
                $ids[] = (int) $skill;
            } else {
                $found = Skill::where('slug', \Str::slug($skill))->first();
                if ($found) {
                    $ids[] = $found->id;
                }
            }
        }

        return $ids;
    }
}
