<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmployerJobController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $jobs = Job::where('employer_id', $user->id)
            ->withCount('applications')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('employer.jobs.index', [
            'jobs' => $jobs,
        ]);
    }

    public function create(Request $request)
    {
        return view('employer.jobs.create');
    }

    public function store(Request $request)
    {
        Log::info('Auth check', ['id' => Auth::id(), 'user' => Auth::user()?->email]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'employment_type' => 'required|in:full_time,part_time,contract,freelance,internship',
            'work_preference' => 'required|in:remote,hybrid,onsite,flexible',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|gte:salary_min|min:0',
            'salary_currency' => ['nullable', 'string', Rule::in(Job::supportedCurrencies())],
            'salary_period' => ['nullable', 'string', Rule::in(array_keys(Job::salaryPeriods()))],
            'minimum_experience' => 'nullable|integer|min:0',
            'description' => 'required|string',
            'responsibilities' => 'nullable|string|max:10000',
            'requirements' => 'nullable|string|max:10000',
            'benefits' => 'nullable|string|max:10000',
            'required_skills' => 'nullable',
            'personality_preferences' => 'nullable',
            'temperament_preference' => 'nullable|string|max:100',
        ]);

        Log::info('Validation passed', $validated);

        try {
            $job = Job::create([
                ...$validated,
                'employer_id' => Auth::id(),
                'company_id' => Auth::user()->company?->id,
                'slug' => Str::slug($validated['title']).'-'.Str::random(6),
                'status' => 'open',
                'minimum_experience' => $validated['minimum_experience'] ?? 0,
                'required_skills_json' => $this->normalizeSkills($validated['required_skills'] ?? null),
                'personality_preferences_json' => is_array($validated['personality_preferences'] ?? null) ? $validated['personality_preferences'] : [],
            ]);

            Log::info('Job created: '.$job->id);

            return redirect()->to('/employer/jobs/'.$job->id)
                ->with('success', 'Job posted successfully!');
        } catch (\Exception $e) {
            Log::error('Job creation failed: '.$e->getMessage());

            return back()->with('error', 'Failed to create job: '.$e->getMessage());
        }
    }

    public function show(Request $request, Job $job)
    {
        $this->authorize('view', $job);

        $job->load(['employer', 'applications.user.candidateProfile']);

        return view('employer.jobs.show', [
            'job' => $job,
        ]);
    }

    public function edit(Request $request, Job $job)
    {
        $this->authorize('update', $job);

        return view('employer.jobs.edit', [
            'job' => $job,
        ]);
    }

    public function update(Request $request, Job $job)
    {
        $this->authorize('update', $job);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'location_country' => 'nullable|string|max:100',
            'employment_type' => 'required|in:full_time,part_time,contract,freelance,internship',
            'work_preference' => 'required|in:remote,hybrid,onsite,flexible',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|gte:salary_min|min:0',
            'salary_currency' => ['nullable', 'string', Rule::in(Job::supportedCurrencies())],
            'salary_period' => ['nullable', 'string', Rule::in(array_keys(Job::salaryPeriods()))],
            'salary_visible' => 'boolean',
            'minimum_experience' => 'nullable|integer|min:0',
            'experience_level' => 'nullable|in:entry,junior,mid,senior,lead,principal,any',
            'description' => 'required|string',
            'responsibilities' => 'nullable|string|max:10000',
            'requirements' => 'nullable|string|max:10000',
            'benefits' => 'nullable|string|max:10000',
            'required_skills' => 'nullable',
            'personality_preferences' => 'nullable|array',
            'temperament_preference' => 'nullable|string|max:100',
            'status' => 'nullable|in:draft,open,paused,closed,filled',
        ]);

        $job->update([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::random(6),
            'role' => $validated['role'],
            'location' => $validated['location'] ?? null,
            'location_country' => $validated['location_country'] ?? null,
            'employment_type' => $validated['employment_type'],
            'work_preference' => $validated['work_preference'],
            'salary_min' => $validated['salary_min'] ?? null,
            'salary_max' => $validated['salary_max'] ?? null,
            'salary_currency' => $validated['salary_currency'] ?? null,
            'salary_period' => $validated['salary_period'] ?? null,
            'salary_visible' => $validated['salary_visible'] ?? true,
            'minimum_experience' => $validated['minimum_experience'] ?? 0,
            'experience_level' => $validated['experience_level'] ?? 'any',
            'description' => $validated['description'],
            'responsibilities' => $validated['responsibilities'] ?? null,
            'requirements' => $validated['requirements'] ?? null,
            'benefits' => $validated['benefits'] ?? null,
            'required_skills_json' => $this->normalizeSkills($validated['required_skills'] ?? null),
            'personality_preferences_json' => $validated['personality_preferences'] ?? [],
            'temperament_preference' => $validated['temperament_preference'] ?? null,
            'status' => $validated['status'] ?? $job->status,
        ]);

        if (isset($validated['status']) && $validated['status'] === 'open' && ! $job->published_at) {
            $job->update(['published_at' => now()]);
        }

        return redirect()->route('employer.jobs.show', ['job' => $job->id])
            ->with('success', 'Job updated successfully!');
    }

    public function destroy(Request $request, Job $job)
    {
        $this->authorize('delete', $job);

        $job->delete();

        return redirect()->route('employer.jobs.index')
            ->with('success', 'Job deleted successfully!');
    }

    private function normalizeSkills(mixed $skills): array
    {
        if (is_array($skills)) {
            return array_values(array_filter(array_map('trim', $skills)));
        }

        if (is_string($skills)) {
            return array_values(array_filter(array_map('trim', explode(',', $skills))));
        }

        return [];
    }
}
