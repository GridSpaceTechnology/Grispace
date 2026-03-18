@props([
    'type' => 'candidate',
])

@php
    $isOnboardingComplete = auth()->user()->onboarding_completed ?? false;
    
    $messages = [
        'candidate' => [
            'title' => 'Welcome to Gridspace',
            'subtitle' => $isOnboardingComplete ? 'Onboarding Completed' : 'Complete your profile to get matched with jobs that fit your skills and preferences.',
            'actionText' => $isOnboardingComplete ? 'Browse Jobs' : 'Complete Profile',
            'actionRoute' => $isOnboardingComplete ? route('candidate.jobs') : route('candidate.onboarding.step', ['step' => 1]),
        ],
        'employer' => [
            'title' => 'Welcome to Gridspace',
            'subtitle' => 'Start posting jobs and discover top talent for your team.',
            'actionText' => 'Post a Job',
            'actionRoute' => route('employer.jobs.create'),
        ],
    ];

    $content = $messages[$type] ?? $messages['candidate'];
@endphp

<div 
    x-data="{ show: true, dismissed: false }"
    x-show="show && !dismissed"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-init="setTimeout(() => { dismissed = true; @this.call('dismiss'); }, 5000)"
    class="mb-6 bg-gradient-to-r from-brand-secondary to-brand-primary rounded-xl shadow-lg overflow-hidden"
>
    <div class="p-6">
        <div class="flex items-start justify-between">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-white">{{ $content['title'] }}</h3>
                    <p class="mt-1 text-white/80">{{ $content['subtitle'] }}</p>
                    <a 
                        href="{{ $content['actionRoute'] }}" 
                        class="inline-flex items-center mt-3 px-4 py-2 bg-white text-brand-primary text-sm font-medium rounded-lg hover:bg-white/90 transition-colors"
                    >
                        {{ $content['actionText'] }}
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
            <button 
                @click="dismissed = true; @this.call('dismiss')" 
                type="button"
                class="text-white/80 hover:text-white transition-colors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
    function welcomeDismiss() {
        fetch('{{ route("welcome.dismiss") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        });
    }
</script>
