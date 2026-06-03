<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\CandidateVerification;
use App\Models\TrustScore;
use App\Models\VerificationDocument;
use App\Models\VerificationType;
use App\Notifications\VerificationSubmitted;
use App\Services\TrustScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidateVerificationController extends Controller
{
    protected TrustScoreService $trustScoreService;

    public function __construct(TrustScoreService $trustScoreService)
    {
        $this->trustScoreService = $trustScoreService;
    }

    public function index()
    {
        $user = Auth::user();

        $verificationTypes = VerificationType::active()->get();

        $existingVerifications = CandidateVerification::forCandidate($user)
            ->with(['verificationType', 'documents'])
            ->get()
            ->keyBy('verification_type_id');

        $trustScore = TrustScore::firstOrCreate(
            ['candidate_id' => $user->id],
            ['score' => 0, 'level' => 'Beginner']
        );

        $emailVerified = ! is_null($user->email_verified_at);
        $phoneVerified = ! is_null($user->phone_verified_at);

        return view('candidate.verification.index', compact(
            'verificationTypes',
            'existingVerifications',
            'trustScore',
            'emailVerified',
            'phoneVerified'
        ));
    }

    public function submit(Request $request, VerificationType $verificationType)
    {
        $user = Auth::user();

        $existing = CandidateVerification::forCandidate($user)
            ->where('verification_type_id', $verificationType->id)
            ->first();

        if ($existing && in_array($existing->status, ['pending', 'under_review'])) {
            return back()->with('error', 'You already have a pending verification for this type.');
        }

        $request->validate([
            'documents' => 'required|array|min:1|max:5',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'document_names' => 'required|array|min:1|max:5',
            'document_names.*' => 'string|max:255',
        ]);

        $verification = CandidateVerification::updateOrCreate(
            [
                'candidate_id' => $user->id,
                'verification_type_id' => $verificationType->id,
            ],
            [
                'status' => 'pending',
                'submitted_at' => now(),
                'notes' => null,
            ]
        );

        foreach ($request->file('documents') as $index => $file) {
            $path = $file->store('verifications/'.$user->id, 'public');

            VerificationDocument::create([
                'candidate_verification_id' => $verification->id,
                'document_name' => $request->document_names[$index] ?? $file->getClientOriginalName(),
                'document_path' => $path,
                'document_type' => $file->getClientMimeType(),
                'uploaded_at' => now(),
            ]);
        }

        $user->notify(new VerificationSubmitted($verification));

        return redirect()->route('candidate.verification')
            ->with('success', 'Your '.$verificationType->name.' has been submitted for review.');
    }

    public function verifyPhone(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'phone_number' => 'required|string|max:20',
        ]);

        $user->update(['phone_number' => $request->phone_number]);

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        session(['phone_otp' => $otp, 'phone_otp_expires' => now()->addMinutes(10)]);

        // In production, send OTP via SMS service
        // For now, store in session for testing
        session(['phone_otp_display' => $otp]);

        return back()->with('success', 'OTP sent to your phone. (Dev: '.$otp.')');
    }

    public function verifyPhoneOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $storedOtp = session('phone_otp');
        $expires = session('phone_otp_expires');

        if (! $storedOtp || ! $expires || now()->gt($expires)) {
            return back()->with('error', 'OTP has expired. Please request a new one.');
        }

        if ($request->otp !== $storedOtp) {
            return back()->with('error', 'Invalid OTP. Please try again.');
        }

        $user = Auth::user();
        $user->update(['phone_verified_at' => now()]);

        $this->trustScoreService->recalculateForCandidate($user);

        session()->forget(['phone_otp', 'phone_otp_expires', 'phone_otp_display']);

        return redirect()->route('candidate.verification')
            ->with('success', 'Phone number verified successfully!');
    }
}
