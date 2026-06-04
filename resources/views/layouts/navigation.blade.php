<nav class="hidden md:block bg-white border-b border-slate-200 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <img src="{{ asset('logo.jpeg') }}" alt="Gridspace" class="h-10 w-auto">
                </a>

                @auth
                    @php
                        $user = auth()->user();
                        $isEmployer = $user->role === 'employer';
                        $isAdmin = $user->role === 'admin';
                    @endphp

                    <div class="flex ml-10 space-x-8">
                        @if($isAdmin)
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                                Dashboard
                            </x-nav-link>
                        @elseif($isEmployer)
                            <x-nav-link :href="route('employer.dashboard')" :active="request()->routeIs('employer.dashboard')">
                                Dashboard
                            </x-nav-link>
                            <x-nav-link :href="route('employer.jobs.index')" :active="request()->routeIs('employer.jobs.*')">
                                My Jobs
                            </x-nav-link>
                            <x-nav-link :href="route('employer.ats.dashboard')" :active="request()->routeIs('employer.ats.*')">
                                ATS
                            </x-nav-link>
                            <x-nav-link :href="route('employer.marketplace.index')" :active="request()->routeIs('employer.marketplace.*')">
                                Talent Marketplace
                            </x-nav-link>
                            <x-nav-link :href="route('employer.messages')" :active="request()->routeIs('employer.messages.*')">
                                Messages
                            </x-nav-link>
                        @else
                            <x-nav-link :href="route('candidate.dashboard')" :active="request()->routeIs('candidate.dashboard')">
                                Dashboard
                            </x-nav-link>
                            <x-nav-link :href="route('candidate.jobs')" :active="request()->routeIs('candidate.jobs')">
                                Browse Jobs
                            </x-nav-link>
                            <x-nav-link :href="route('messages.index')" :active="request()->routeIs('messages.*')">
                                Messages
                            </x-nav-link>
                            <x-nav-link :href="route('candidate.personality.start')" :active="request()->routeIs('candidate.personality.*')">
                                Assessment
                            </x-nav-link>
                        @endif
                    </div>
                @else
                    <div class="flex ml-10 space-x-8">
                        <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                            Home
                        </x-nav-link>
                        <x-nav-link :href="route('jobs.index')" :active="request()->routeIs('jobs.*')">
                            Browse Jobs
                        </x-nav-link>
                        <x-nav-link :href="route('marketplace.index')" :active="request()->routeIs('marketplace.*')">
                            Talent Marketplace
                        </x-nav-link>
                    </div>
                @endauth
            </div>

            <div class="flex items-center gap-4">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-brand-primary/10 rounded-full flex items-center justify-center">
                                        <span class="text-brand-primary font-medium text-sm">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    </div>
                                    <div>{{ Auth::user()->name }}</div>
                                </div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @php
                                $user = auth()->user();
                                $isEmployer = $user->role === 'employer';
                                $isAdmin = $user->role === 'admin';
                            @endphp

                            @if($isAdmin)
                                <x-dropdown-link :href="route('admin.dashboard')">
                                    Dashboard
                                </x-dropdown-link>
                            @else
                                <x-dropdown-link :href="$isEmployer ? route('employer.dashboard') : route('candidate.dashboard')">
                                    Dashboard
                                </x-dropdown-link>
                            @endif

                            <x-dropdown-link :href="$isEmployer ? route('employer.profile.edit') : ($isAdmin ? route('profile.edit') : route('candidate.profile.edit'))">
                                Settings
                            </x-dropdown-link>

                            <div class="border-t border-gray-200 my-1"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    Log Out
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-gray-900 font-medium">
                        Log in
                    </a>
                    <a href="{{ route('register') }}" class="text-sm bg-brand-primary text-white px-4 py-2 rounded-lg hover:bg-brand-primary-hover font-medium transition-colors">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>
