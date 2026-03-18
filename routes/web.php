<?php

use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminCandidateController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEmployerController;
use App\Http\Controllers\Admin\AdminJobController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\CandidateDashboardController;
use App\Http\Controllers\CandidateOnboardingController;
use App\Http\Controllers\CandidateProfileController;
use App\Http\Controllers\CandidateRecommendationController;
use App\Http\Controllers\EmployerDashboardController;
use App\Http\Controllers\EmployerInterviewController;
use App\Http\Controllers\EmployerJobCandidateController;
use App\Http\Controllers\EmployerJobController;
use App\Http\Controllers\EmployerMarketplaceController;
use App\Http\Controllers\EmployerPipelineController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    if ($user->role === 'candidate') {
        return redirect()->route('candidate.dashboard');
    }

    return redirect()->route('employer.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/welcome/dismiss', function () {
        auth()->user()->dismissWelcome();

        return response()->json(['success' => true]);
    })->name('welcome.dismiss');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'role:candidate'])->group(function () {
    Route::get('/candidate/onboarding', [CandidateOnboardingController::class, 'show'])
        ->name('candidate.onboarding');

    Route::get('/candidate/onboarding/step/{step}', [CandidateOnboardingController::class, 'show'])
        ->name('candidate.onboarding.step');

    Route::post('/candidate/onboarding/step/{step}', [CandidateOnboardingController::class, 'store'])
        ->name('candidate.onboarding.store');

    Route::post('/candidate/onboarding/skip', [CandidateOnboardingController::class, 'skip'])
        ->name('candidate.onboarding.skip');

    Route::get('/candidate/dashboard', [CandidateDashboardController::class, 'index'])
        ->name('candidate.dashboard');

    Route::get('/candidate/jobs', [CandidateDashboardController::class, 'jobs'])
        ->name('candidate.jobs');

    Route::post('/candidate/jobs/{job}/apply', [CandidateDashboardController::class, 'apply'])
        ->name('candidate.jobs.apply');

    Route::get('/candidate/applications', [CandidateDashboardController::class, 'applications'])
        ->name('candidate.applications');

    Route::get('/candidate/interviews', [CandidateDashboardController::class, 'interviews'])
        ->name('candidate.interviews');

    Route::get('/candidate/profile/{user}', [CandidateProfileController::class, 'show'])
        ->name('candidate.profile.show');

    Route::get('/candidate/recommended-jobs', [CandidateRecommendationController::class, 'index'])
        ->name('candidate.recommended-jobs');

    Route::get('/messages', [MessageController::class, 'index'])
        ->name('messages.index');

    Route::get('/messages/{conversation}', [MessageController::class, 'show'])
        ->name('messages.show');
});

Route::middleware(['auth', 'role:employer'])->group(function () {
    Route::get('/employer/onboarding', function () {
        $user = auth()->user();
        if ($user->onboarding_completed) {
            return redirect()->route('employer.dashboard');
        }

        return redirect()->route('employer.setup');
    })->name('employer.onboarding');

    Route::get('/employer/setup', [App\Http\Controllers\Employer\EmployerSetupController::class, 'show'])
        ->name('employer.setup');
    Route::post('/employer/setup', [App\Http\Controllers\Employer\EmployerSetupController::class, 'store']);

    Route::get('/employer/dashboard', [EmployerDashboardController::class, 'index'])
        ->name('employer.dashboard')
        ->middleware('verified');

    Route::get('/employer/jobs', [EmployerJobController::class, 'index'])
        ->name('employer.jobs.index');

    Route::get('/employer/jobs/create', [EmployerJobController::class, 'create'])
        ->name('employer.jobs.create');

    Route::post('/employer/jobs', [EmployerJobController::class, 'store'])
        ->name('employer.jobs.store');

    Route::get('/employer/jobs/{job}', [EmployerJobController::class, 'show'])
        ->name('employer.jobs.show');

    Route::get('/employer/jobs/{job}/edit', [EmployerJobController::class, 'edit'])
        ->name('employer.jobs.edit');

    Route::patch('/employer/jobs/{job}', [EmployerJobController::class, 'update'])
        ->name('employer.jobs.update');

    Route::delete('/employer/jobs/{job}', [EmployerJobController::class, 'destroy'])
        ->name('employer.jobs.destroy');

    Route::get('/employer/jobs/{job}/candidates', [EmployerJobCandidateController::class, 'index'])
        ->name('employer.jobs.candidates');

    Route::get('/employer/jobs/{job}/pipeline', [EmployerPipelineController::class, 'index'])
        ->name('employer.jobs.pipeline');

    Route::post('/employer/applications/{application}/move', [EmployerPipelineController::class, 'moveStage'])
        ->name('employer.applications.move');

    Route::get('/employer/applications/{application}/schedule-interview', [EmployerInterviewController::class, 'scheduleFromApplication'])
        ->name('employer.applications.schedule-interview');

    Route::post('/employer/applications/{application}/schedule-interview', [EmployerInterviewController::class, 'storeFromApplication'])
        ->name('employer.applications.schedule-interview.store');

    Route::get('/employer/marketplace', [EmployerMarketplaceController::class, 'index'])
        ->name('employer.marketplace.index');

    Route::get('/employer/marketplace/candidates', [EmployerMarketplaceController::class, 'candidates'])
        ->name('employer.marketplace.candidates');

    Route::post('/employer/marketplace/candidates/{candidate}/shortlist', [EmployerMarketplaceController::class, 'shortlist'])
        ->name('employer.marketplace.shortlist');

    Route::get('/employer/marketplace/candidates/{candidate}', [EmployerMarketplaceController::class, 'showCandidate'])
        ->name('employer.marketplace.candidate');

    Route::get('/employer/shortlists', [EmployerMarketplaceController::class, 'shortlists'])
        ->name('employer.shortlists');

    Route::get('/employer/interviews', [EmployerInterviewController::class, 'index'])
        ->name('employer.interviews.index');

    Route::get('/employer/interviews/create', [EmployerInterviewController::class, 'create'])
        ->name('employer.interviews.create');

    Route::post('/employer/interviews', [EmployerInterviewController::class, 'store'])
        ->name('employer.interviews.store');

    Route::get('/employer/interviews/{interview}', [EmployerInterviewController::class, 'show'])
        ->name('employer.interviews.show');

    Route::post('/employer/interviews/{interview}/cancel', [EmployerInterviewController::class, 'cancel'])
        ->name('employer.interviews.cancel');

    Route::post('/employer/interviews/{interview}/complete', [EmployerInterviewController::class, 'complete'])
        ->name('employer.interviews.complete');

    Route::get('/employer/messages', [MessageController::class, 'index'])
        ->name('employer.messages');

    Route::get('/employer/messages/{conversation}', [MessageController::class, 'show'])
        ->name('employer.messages.show');

    Route::post('/employer/messages/conversation/{candidate}', [MessageController::class, 'createOrGetConversation'])
        ->name('employer.messages.create');

    Route::get('/employer/messages/{conversation}/messages', [MessageController::class, 'getMessages'])
        ->name('employer.messages.get');

    Route::post('/employer/messages/{conversation}/send', [MessageController::class, 'sendMessage'])
        ->name('employer.messages.send');

    Route::post('/employer/messages/{conversation}/read', [MessageController::class, 'markAsRead'])
        ->name('employer.messages.read');
});

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('admin');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('admin.users.show');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
        Route::patch('/users/{user}/verify', [AdminUserController::class, 'verify'])->name('admin.users.verify');
        Route::get('/candidates', [AdminCandidateController::class, 'index'])->name('admin.candidates');
        Route::get('/employers', [AdminEmployerController::class, 'index'])->name('admin.employers');
        Route::patch('/employers/{employer}/verify', [AdminEmployerController::class, 'verify'])->name('admin.employers.verify');

        Route::get('/jobs', [AdminJobController::class, 'index'])->name('admin.jobs');

        Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('admin.analytics');
    });
