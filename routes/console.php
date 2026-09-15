<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('users:suspend-unverified')->dailyAt('03:00');

// Batch semantic embedding refreshes for shared hosting without a persistent
// queue worker. Only actually generates vectors when the embedding provider
// is enabled and configured; no-ops otherwise.
Schedule::command('semantic:refresh --entity=all --sync')
    ->dailyAt('03:30')
    ->when(fn () => (bool) config('matching.semantic.enabled', false));
