<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SuspendUnverifiedUsers extends Command
{
    protected $signature = 'users:suspend-unverified {--days= : Override the grace period in days}';

    protected $description = 'Deactivate accounts that have not verified their email within the grace period';

    public function handle(): int
    {
        $graceDays = (int) ($this->option('days') ?: config('auth.email_verification.grace_days', 14));
        $cutoff = Carbon::now()->subDays($graceDays);

        $suspendedCount = 0;

        User::query()
            ->whereNull('email_verified_at')
            ->where('is_suspended', false)
            ->where('created_at', '<=', $cutoff)
            ->chunkById(500, function ($users) use (&$suspendedCount) {
                foreach ($users as $user) {
                    $user->forceFill([
                        'is_suspended' => true,
                        'suspension_reason' => User::SUSPENSION_REASON_UNVERIFIED_EMAIL,
                    ])->save();

                    $suspendedCount++;
                }
            });

        if ($suspendedCount === 0) {
            $this->info('No unverified accounts past the grace period.');

            return self::SUCCESS;
        }

        $this->info("Suspended {$suspendedCount} account(s) for not verifying their email within {$graceDays} day(s).");

        return self::SUCCESS;
    }
}
