<?php

use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\TalentController;
use Illuminate\Support\Facades\Route;

Route::prefix('marketplace')->group(function () {
    Route::get('/jobs/search', [MarketplaceController::class, 'searchJobs']);
    Route::get('/jobs/filters', [MarketplaceController::class, 'getSearchFilters']);
    Route::get('/jobs/{job}', [MarketplaceController::class, 'getJob']);

    Route::middleware('auth')->group(function () {
        Route::get('/jobs/recommended', [MarketplaceController::class, 'getRecommendedJobs']);
        Route::post('/jobs/{job}/apply', [MarketplaceController::class, 'applyForJob']);

        Route::middleware('role:employer')->group(function () {
            Route::get('/talent/search', [TalentController::class, 'search']);
            Route::get('/talent/filters', [TalentController::class, 'getFilters']);
            Route::get('/talent/{candidate}', [TalentController::class, 'getCandidate']);
            Route::get('/employer/jobs/{job}/candidates', [MarketplaceController::class, 'getCandidatesForJob']);
            Route::get('/employer/jobs/{job}/candidates/ranked', [TalentController::class, 'searchWithMatch']);
        });
    });
});
