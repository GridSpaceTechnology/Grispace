<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class PublicJobController extends Controller
{
    public function index(Request $request)
    {
        $query = Job::where('status', 'open')
            ->with('employer.company');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->has('location') && $request->location) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        $jobs = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('jobs.index', [
            'jobs' => $jobs,
        ]);
    }

    public function show(Job $job)
    {
        $job->load('employer.company');

        return view('jobs.show', [
            'job' => $job,
        ]);
    }
}
