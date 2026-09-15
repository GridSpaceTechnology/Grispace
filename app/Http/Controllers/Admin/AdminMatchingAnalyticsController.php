<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MatchAnalyticsService;
use Illuminate\View\View;

/**
 * Admin-facing matching quality analytics. Aggregate-only, PII-free reports
 * on how well match scores predict real outcomes, so product owners can make
 * human-controlled calibration decisions from evidence.
 */
class AdminMatchingAnalyticsController extends Controller
{
    public function __construct(protected MatchAnalyticsService $analytics) {}

    public function index(): View
    {
        $topK = (int) config('matching.metrics.top_k', 10);

        return view('admin.matching-analytics', [
            'minSample' => (int) config('matching.metrics.min_sample', 20),
            'sampleSatisfied' => $this->analytics->sampleSatisfied(),
            'funnel' => $this->analytics->funnel(),
            'bands' => $this->analytics->bandsReport(),
            'precision' => $this->analytics->precisionRecallK($topK, 'applied'),
            'viewPrecision' => $this->analytics->precisionRecallK($topK, 'viewed'),
            'versions' => $this->analytics->versionUsage(),
            'feedback' => $this->analytics->feedbackReport(),
            'experiments' => (bool) config('matching.experiments.live_routing', false),
            'weights' => config('matching.weights', []),
            'thresholds' => config('matching.thresholds', []),
            'semantic' => $this->analytics->semanticReport(),
        ]);
    }
}
