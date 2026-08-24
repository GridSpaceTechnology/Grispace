@php
    $user = auth()->user();
    $daysLeft = $user->daysUntilDeactivation();
@endphp

@if (! $user->hasVerifiedEmail())
    <div
        x-data="{ show: true }"
        x-show="show"
        class="mb-6 rounded-xl border border-amber-300 bg-amber-50 shadow-sm overflow-hidden"
    >
        <div class="p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-amber-900">
                            @if ($daysLeft > 0)
                                {{ __('Verify your email — :days day(s) until your account is deactivated', ['days' => $daysLeft]) }}
                            @else
                                {{ __('Verify your email to avoid deactivation') }}
                            @endif
                        </h3>
                        <p class="mt-1 text-sm text-amber-800">
                            {{ __("You can keep using Gridspace for now, but accounts that aren't verified within the grace period are deactivated automatically.") }}
                        </p>

                        @if (session('status') == 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-green-700">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif

                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <a
                                href="{{ route('verification.notice') }}"
                                class="inline-flex items-center px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition-colors"
                            >
                                {{ __('Verify Now') }}
                            </a>

                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf

                                <button type="submit" class="text-sm font-medium text-amber-700 underline underline-offset-4 hover:text-amber-900 transition-colors">
                                    {{ __('Resend Link') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <button type="button" @click="show = false" class="text-amber-400 hover:text-amber-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endif
