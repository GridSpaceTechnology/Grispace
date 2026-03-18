<nav x-data="{ open: false }" class="bg-white border-b border-slate-200 sticky top-0 z-50">
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

                    <div class="hidden md:flex ml-10 space-x-8">
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
                            <x-nav-link :href="route('employer.marketplace.index')" :active="request()->routeIs('employer.marketplace.*')">
                                Talent Marketplace
                            </x-nav-link>
                        @else
                            <x-nav-link :href="route('candidate.dashboard')" :active="request()->routeIs('candidate.dashboard')">
                                Dashboard
                            </x-nav-link>
                            <x-nav-link :href="route('candidate.jobs')" :active="request()->routeIs('candidate.jobs')">
                                Browse Jobs
                            </x-nav-link>
                        @endif
                    </div>
                @else
                    <div class="hidden md:flex ml-10 space-x-8">
                        <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                            Home
                        </x-nav-link>
                        <x-nav-link :href="route('candidate.jobs')" :active="request()->routeIs('candidate.jobs')">
                            Browse Jobs
                        </x-nav-link>
                        <x-nav-link :href="route('employer.marketplace.index')" :active="request()->routeIs('employer.marketplace.*')">
                            Talent Marketplace
                        </x-nav-link>
                    </div>
                @endauth
            </div>

            <div class="hidden md:flex items-center gap-4">
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

                            <x-dropdown-link :href="route('profile.edit')">
                                Profile
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

            <div class="-me-2 flex items-center md:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden md:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
                @php
                    $user = auth()->user();
                    $isEmployer = $user->role === 'employer';
                    $isAdmin = $user->role === 'admin';
                @endphp

                @if($isAdmin)
                    <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                        Dashboard
                    </x-responsive-nav-link>
                @elseif($isEmployer)
                    <x-responsive-nav-link :href="route('employer.dashboard')" :active="request()->routeIs('employer.dashboard')">
                        Dashboard
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('employer.jobs.index')" :active="request()->routeIs('employer.jobs.*')">
                        My Jobs
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('employer.marketplace.index')" :active="request()->routeIs('employer.marketplace.*')">
                        Talent Marketplace
                    </x-responsive-nav-link>
                @else
                    <x-responsive-nav-link :href="route('candidate.dashboard')" :active="request()->routeIs('candidate.dashboard')">
                        Dashboard
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('candidate.jobs')" :active="request()->routeIs('candidate.jobs')">
                        Browse Jobs
                    </x-responsive-nav-link>
                @endif
            @else
                <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">
                    Home
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('candidate.jobs')" :active="request()->routeIs('candidate.jobs')">
                    Browse Jobs
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('employer.marketplace.index')" :active="request()->routeIs('employer.marketplace.*')">
                    Talent Marketplace
                </x-responsive-nav-link>
            @endauth
        </div>

        @auth
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        Profile
                    </x-responsive-nav-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                            Log Out
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="space-y-1 px-4">
                    <a href="{{ route('login') }}" class="block px-4 py-2 text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">
                        Log in
                    </a>
                    <a href="{{ route('register') }}" class="block px-4 py-2 text-base font-medium text-indigo-600 hover:text-indigo-700 hover:bg-gray-50">
                        Register
                    </a>
                </div>
            </div>
        @endauth
    </div>
</nav>
