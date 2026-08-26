<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RecalculateJobMatches;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminJobController extends Controller
{
    public function index(): View
    {
        $jobs = Job::with(['employer', 'company'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.jobs.index', ['jobs' => $jobs]);
    }

    public function show(Request $request, Job $job): View
    {
        $this->authorize('view', $job);

        $job->load(['employer', 'applications.user.candidateProfile']);

        return view('employer.jobs.show', [
            'job' => $job,
        ]);
    }

    public function edit(Request $request, Job $job): View
    {
        $this->authorize('update', $job);

        return view('employer.jobs.edit', [
            'job' => $job,
            'formAction' => route('admin.jobs.update', $job),
            'cancelUrl' => route('admin.jobs'),
            'deleteAction' => route('admin.jobs.destroy', $job),
        ]);
    }

    public function update(Request $request, Job $job): RedirectResponse
    {
        $this->authorize('update', $job);

        $validated = $request->validate($this->jobValidationRules());

        $wasOpen = $job->status === 'open';

        $job->update([
            ...collect($validated)->except(['required_skills'])->all(),
            'salary_visible' => $request->boolean('salary_visible'),
            'minimum_experience' => $validated['minimum_experience'] ?? 0,
            'experience_level' => $validated['experience_level'] ?? 'any',
            'required_skills_json' => $this->normalizeSkills($validated['required_skills'] ?? null),
            'status' => $validated['status'] ?? $job->status,
            'published_at' => (! $wasOpen && ($validated['status'] ?? $job->status) === 'open' && ! $job->published_at)
                ? now()
                : $job->published_at,
        ]);

        RecalculateJobMatches::dispatch($job);

        return redirect()->route('admin.jobs')
            ->with('success', "Job \"{$job->title}\" updated successfully.");
    }

    public function destroy(Request $request, Job $job): RedirectResponse
    {
        $this->authorize('delete', $job);

        $title = $job->title;
        $job->delete();

        return redirect()->route('admin.jobs')
            ->with('success', "Job \"{$title}\" deleted successfully.");
    }

    public function toggleStatus(Request $request, Job $job): RedirectResponse
    {
        $this->authorize('update', $job);

        if ($job->status === 'open') {
            $job->update(['status' => 'paused']);

            return redirect()->route('admin.jobs')
                ->with('success', "Job \"{$job->title}\" deactivated.");
        }

        $job->update([
            'status' => 'open',
            'published_at' => $job->published_at ?? now(),
        ]);

        return redirect()->route('admin.jobs')
            ->with('success', "Job \"{$job->title}\" reactivated.");
    }

    private function jobValidationRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'location_country' => 'nullable|string|max:100',
            'employment_type' => 'required|in:full_time,part_time,contract,freelance,internship',
            'work_preference' => 'required|in:remote,hybrid,onsite,flexible',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => ['nullable', 'numeric', 'min:0', 'gte:salary_min'],
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
        ];
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
