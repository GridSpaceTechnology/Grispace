<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
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
}
