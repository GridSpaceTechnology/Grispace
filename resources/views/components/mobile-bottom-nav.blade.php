@php
    $navItems = [
        [
            'label' => 'Home',
            'icon' => 'home',
            'url' => route('home'),
            'active' => request()->routeIs('home'),
        ],
        [
            'label' => 'Jobs',
            'icon' => 'briefcase',
            'url' => '/jobs',
            'active' => request()->is('jobs') || request()->is('jobs/*'),
        ],
        [
            'label' => 'Marketplace',
            'icon' => 'store',
            'url' => route('marketplace.index'),
            'active' => request()->routeIs('marketplace*'),
        ],
        [
            'label' => 'Messages',
            'icon' => 'message-circle',
            'url' => '/messages',
            'active' => request()->is('messages') || request()->is('messages/*'),
        ],
        [
            'label' => 'Dashboard',
            'icon' => 'grid',
            'url' => auth()->check()
                ? (auth()->user()->role === 'admin'
                    ? route('admin.dashboard')
                    : (auth()->user()->role === 'employer'
                        ? route('employer.dashboard')
                        : route('candidate.dashboard')))
                : route('login'),
            'active' => request()->routeIs('*.dashboard') || request()->routeIs('admin.*'),
        ],
        [
            'label' => 'Profile',
            'icon' => 'user',
            'url' => route('profile.edit'),
            'active' => request()->routeIs('profile*'),
        ],
    ];
@endphp

<nav
    class="fixed bottom-0 left-0 right-0 z-50 md:hidden"
    aria-label="Mobile navigation"
>
    <div
        class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-lg border-t border-gray-200 dark:border-gray-700 shadow-[0_-4px_20px_rgba(0,0,0,0.08)] dark:shadow-[0_-4px_20px_rgba(0,0,0,0.3)]"
        style="padding-bottom: env(safe-area-inset-bottom, 0px)"
    >
        <div class="flex items-center justify-around h-[68px] px-2">
            @foreach ($navItems as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="relative flex flex-col items-center justify-center w-[64px] h-full gap-0.5 transition-all duration-200 group"
                    @if ($item['active']) aria-current="page" @endif
                >
                    @if ($item['active'])
                        <span class="absolute -top-px left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-[#EB5233]"></span>
                    @endif

                    <div class="relative flex items-center justify-center w-6 h-6 transition-transform duration-200 group-active:scale-90">
                        @if ($item['icon'] === 'home')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $item['active'] ? 'text-[#EB5233]' : 'text-gray-500 dark:text-gray-300' }} transition-colors duration-200">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                        @elseif ($item['icon'] === 'briefcase')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $item['active'] ? 'text-[#EB5233]' : 'text-gray-500 dark:text-gray-300' }} transition-colors duration-200">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                        @elseif ($item['icon'] === 'store')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $item['active'] ? 'text-[#EB5233]' : 'text-gray-500 dark:text-gray-300' }} transition-colors duration-200">
                                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                                <path d="M3 6h18"/>
                                <path d="M16 10a4 4 0 0 1-8 0"/>
                            </svg>
                        @elseif ($item['icon'] === 'grid')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $item['active'] ? 'text-[#EB5233]' : 'text-gray-500 dark:text-gray-300' }} transition-colors duration-200">
                                <rect x="3" y="3" width="7" height="7"/>
                                <rect x="14" y="3" width="7" height="7"/>
                                <rect x="14" y="14" width="7" height="7"/>
                                <rect x="3" y="14" width="7" height="7"/>
                            </svg>
                        @elseif ($item['icon'] === 'user')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $item['active'] ? 'text-[#EB5233]' : 'text-gray-500 dark:text-gray-300' }} transition-colors duration-200">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        @elseif ($item['icon'] === 'message-circle')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $item['active'] ? 'text-[#EB5233]' : 'text-gray-500 dark:text-gray-300' }} transition-colors duration-200">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                            </svg>
                        @endif
                    </div>

                    <span class="text-[10px] leading-tight whitespace-nowrap transition-colors duration-200 @if ($item['active']) text-[#EB5233] font-semibold @else text-gray-500 dark:text-gray-300 font-medium @endif">
                        {{ $item['label'] }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</nav>
