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
            'label' => 'Learn',
            'icon' => 'graduation-cap',
            'url' => '/learn',
            'active' => request()->is('learn') || request()->is('learn/*'),
        ],
        [
            'label' => 'Articles',
            'icon' => 'newspaper',
            'url' => '/articles',
            'active' => request()->is('articles') || request()->is('articles/*'),
        ],
        [
            'label' => 'Messages',
            'icon' => 'message-circle',
            'url' => '/messages',
            'active' => request()->is('messages') || request()->is('messages/*'),
        ],
    ];
@endphp

<nav
    class="fixed bottom-0 left-0 right-0 z-50 lg:hidden"
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $item['active'] ? 'text-[#EB5233]' : 'text-gray-400 dark:text-gray-500' }} transition-colors duration-200">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                        @elseif ($item['icon'] === 'briefcase')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $item['active'] ? 'text-[#EB5233]' : 'text-gray-400 dark:text-gray-500' }} transition-colors duration-200">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                        @elseif ($item['icon'] === 'graduation-cap')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $item['active'] ? 'text-[#EB5233]' : 'text-gray-400 dark:text-gray-500' }} transition-colors duration-200">
                                <path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/>
                                <path d="M22 10v6"/>
                                <path d="M6 12.5v5c3 3 8.4 3 12 0v-5"/>
                            </svg>
                        @elseif ($item['icon'] === 'newspaper')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $item['active'] ? 'text-[#EB5233]' : 'text-gray-400 dark:text-gray-500' }} transition-colors duration-200">
                                <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9h2"/>
                                <path d="M18 14h-8"/>
                                <path d="M15 18h-5"/>
                                <path d="M10 6h8v4h-8V6Z"/>
                            </svg>
                        @elseif ($item['icon'] === 'message-circle')
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $item['active'] ? 'text-[#EB5233]' : 'text-gray-400 dark:text-gray-500' }} transition-colors duration-200">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                            </svg>
                        @endif
                    </div>

                    <span class="text-[10px] leading-tight whitespace-nowrap transition-colors duration-200 @if ($item['active']) text-[#EB5233] font-semibold @else text-gray-400 dark:text-gray-500 font-medium @endif">
                        {{ $item['label'] }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</nav>
