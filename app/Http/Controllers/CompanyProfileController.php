<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyProfileController extends Controller
{
    public function show(Request $request, Company $company): View
    {
        $company->load(['user']);

        $jobs = $company->jobs()
            ->where('status', 'open')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $viewer = $request->user();

        return view('companies.show', [
            'company' => $company,
            'jobs' => $jobs,
            'canMessage' => $this->canMessage($viewer, $company),
        ]);
    }

    private function canMessage(?object $viewer, Company $company): bool
    {
        if (! $viewer || ! $viewer->isCandidate()) {
            return false;
        }

        if ($viewer->isSuspendedForUnverifiedEmail()) {
            return false;
        }

        return $company->allow_candidate_messages !== false;
    }
}
