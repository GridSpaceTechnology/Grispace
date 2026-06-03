<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CandidateVerification;
use App\Models\TrustScore;
use App\Notifications\VerificationApproved;
use App\Notifications\VerificationInfoRequested;
use App\Notifications\VerificationRejected;
use App\Services\TrustScoreService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminVerificationController extends Controller
{
    protected TrustScoreService $trustScoreService;

    public function __construct(TrustScoreService $trustScoreService)
    {
        $this->trustScoreService = $trustScoreService;
    }

    public function index(): View
    {
        $verifications = CandidateVerification::with(['candidate', 'verificationType', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => CandidateVerification::count(),
            'pending' => CandidateVerification::pending()->count(),
            'under_review' => CandidateVerification::underReview()->count(),
            'approved' => CandidateVerification::approved()->count(),
            'rejected' => CandidateVerification::rejected()->count(),
        ];

        return view('admin.verifications.index', compact('verifications', 'stats'));
    }

    public function pending(): View
    {
        $verifications = CandidateVerification::pending()
            ->with(['candidate', 'verificationType'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.verifications.pending', compact('verifications'));
    }

    public function show(CandidateVerification $verification): View
    {
        $verification->load(['candidate', 'verificationType', 'documents', 'reviewer']);

        return view('admin.verifications.review', compact('verification'));
    }

    public function approve(Request $request, CandidateVerification $verification)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $verification->update([
            'status' => 'approved',
            'verified_at' => now(),
            'reviewed_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        $this->trustScoreService->recalculateForCandidate($verification->candidate);

        $verification->candidate->notify(new VerificationApproved($verification));

        return redirect()->route('admin.verifications.index')
            ->with('success', 'Verification approved successfully.');
    }

    public function reject(Request $request, CandidateVerification $verification)
    {
        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $verification->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        $verification->candidate->notify(new VerificationRejected($verification));

        return redirect()->route('admin.verifications.index')
            ->with('success', 'Verification rejected.');
    }

    public function requestInfo(Request $request, CandidateVerification $verification)
    {
        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $verification->update([
            'status' => 'info_requested',
            'reviewed_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        $verification->candidate->notify(new VerificationInfoRequested($verification));

        return redirect()->route('admin.verifications.index')
            ->with('success', 'Additional information requested from candidate.');
    }

    public function stats(): View
    {
        $total = CandidateVerification::count();
        $approved = CandidateVerification::approved()->count();
        $rejected = CandidateVerification::rejected()->count();
        $pending = CandidateVerification::pending()->count();
        $underReview = CandidateVerification::underReview()->count();

        $approvalRate = $total > 0 ? round(($approved / $total) * 100) : 0;
        $completionRate = $total > 0 ? round((($approved + $rejected) / $total) * 100) : 0;

        $verificationTypeCounts = CandidateVerification::selectRaw('verification_type_id, COUNT(*) as count')
            ->groupBy('verification_type_id')
            ->with('verificationType')
            ->get();

        $mostCommonType = $verificationTypeCounts->sortByDesc('count')->first();

        $averageScore = TrustScore::avg('score') ?? 0;

        return view('admin.verifications.stats', compact(
            'total', 'approved', 'rejected', 'pending', 'underReview',
            'approvalRate', 'completionRate', 'verificationTypeCounts',
            'mostCommonType', 'averageScore'
        ));
    }
}
