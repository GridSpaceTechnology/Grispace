@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50">
    {{-- Hero Section --}}
    <section class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-slate-900 tracking-tight">
                        Discover Talent Beyond the Resume
                    </h1>
                    <p class="mt-6 text-lg md:text-xl text-slate-600 max-w-xl">
                        Gridspace reveals skills, personality, and work temperament — helping employers hire the right people faster.
                    </p>
                    <div class="mt-10 flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('candidate.jobs') }}" class="inline-flex items-center justify-center px-8 py-3 text-base font-medium text-white bg-brand-primary rounded-lg hover:bg-brand-primary-hover transition-colors">
                            Find Jobs
                        </a>
                        <a href="{{ route('register') }}?role=employer" class="inline-flex items-center justify-center px-8 py-3 text-base font-medium text-brand-primary bg-white border-2 border-brand-primary rounded-lg hover:bg-brand-primary/5 transition-colors">
                            Hire Talent
                        </a>
                    </div>
                </div>
                <div class="hidden lg:block">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-brand-primary/10 to-brand-secondary/10 rounded-2xl transform rotate-3"></div>
                        <div class="relative bg-white rounded-2xl shadow-xl p-6 border border-slate-100">
                            <div class="space-y-4">
                                @php
                                $candidates = [
                                    ['name' => 'Sarah Chen', 'role' => 'Senior Full Stack Engineer', 'match' => '92%'],
                                    ['name' => 'Marcus Thompson', 'role' => 'Product Design Lead', 'match' => '88%'],
                                    ['name' => 'Elena Rodriguez', 'role' => 'Growth Marketing Manager', 'match' => '85%'],
                                ];
                                @endphp
                                @foreach($candidates as $candidate)
                                    <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-lg">
                                        <div class="w-10 h-10 bg-brand-primary/10 rounded-full flex items-center justify-center">
                                            <span class="text-brand-primary font-semibold text-sm">{{ substr($candidate['name'], 0, 2) }}</span>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-slate-900 text-sm">{{ $candidate['name'] }}</div>
                                            <div class="text-xs text-slate-500">{{ $candidate['role'] }}</div>
                                        </div>
                                        <div class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full text-xs font-medium">
                                            {{ $candidate['match'] }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Talent Marketplace Preview --}}
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-slate-900">Featured Candidates</h2>
                <p class="mt-2 text-slate-600">Discover verified professionals ready to join your team</p>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                {{-- Left Sidebar --}}
                <aside class="lg:w-56 flex-shrink-0">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sticky top-24">
                        <h3 class="font-semibold text-slate-900 mb-3 text-sm">Categories</h3>
                        <ul class="space-y-1">
                            @php
                            $categories = ['Technology', 'Marketing', 'HR & Culture', 'Design', 'Data Science', 'Finance'];
                            @endphp
                            @foreach($categories as $category)
                                <li>
                                    <a href="{{ route('employer.marketplace.index') }}" class="block px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors text-sm">
                                        {{ $category }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        {{-- Verification Card --}}
                        <div class="mt-4 pt-4 border-t border-slate-200">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-medium text-slate-900 text-sm">Verified Only</span>
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                All candidates go through a structured vetting process including skills, personality and role-based assessment.
                            </p>
                        </div>
                    </div>
                </aside>

                {{-- Candidate Grid --}}
                <div class="flex-1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @php
                        $candidates = [
                            ['name' => 'Sarah Chen', 'role' => 'Senior Full Stack Engineer', 'skills' => ['React', 'TypeScript', 'Node'], 'badge' => 'Available Now', 'badgeVariant' => 'success', 'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&h=150&fit=crop&crop=face'],
                            ['name' => 'Marcus Thompson', 'role' => 'Product Design Lead', 'skills' => ['Figma', 'Strategy', 'Systems'], 'badge' => 'Top Pick', 'badgeVariant' => 'purple', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face'],
                            ['name' => 'Elena Rodriguez', 'role' => 'Growth Marketing Manager', 'skills' => ['SEO', 'Analytics', 'HubSpot'], 'badge' => 'Trending', 'badgeVariant' => 'info', 'image' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face'],
                            ['name' => 'James Wilson', 'role' => 'Data Scientist', 'skills' => ['Python', 'ML', 'SQL'], 'badge' => 'Vetted', 'badgeVariant' => 'indigo', 'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&h=150&fit=crop&crop=face'],
                            ['name' => 'Priya Sharma', 'role' => 'DevOps Engineer', 'skills' => ['AWS', 'Kubernetes', 'Terraform'], 'badge' => 'Interviewing', 'badgeVariant' => 'warning', 'image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&h=150&fit=crop&crop=face'],
                            ['name' => 'David Kim', 'role' => 'Frontend Developer', 'skills' => ['Vue.js', 'CSS', 'JavaScript'], 'badge' => 'Available Now', 'badgeVariant' => 'success', 'image' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=150&h=150&fit=crop&crop=face'],
                        ];
                        @endphp
                        @foreach($candidates as $candidate)
                            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 hover:shadow-md transition-shadow duration-200">
                                <div class="flex items-center gap-4 mb-3">
                                    <img src="{{ $candidate['image'] }}" alt="{{ $candidate['name'] }}" class="w-12 h-12 rounded-full object-cover flex-shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <h4 class="font-semibold text-slate-900 text-sm truncate">{{ $candidate['name'] }}</h4>
                                            <x-badge :variant="$candidate['badgeVariant']" size="xs">{{ $candidate['badge'] }}</x-badge>
                                        </div>
                                        <p class="text-xs text-slate-500 truncate">{{ $candidate['role'] }}</p>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($candidate['skills'] as $skill)
                                            <span class="px-2 py-0.5 bg-slate-100 rounded text-xs text-slate-600">{{ $skill }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <a href="{{ route('employer.marketplace.index') }}" class="block w-full text-center text-xs border border-gray-300 text-slate-700 py-1.5 rounded-lg hover:bg-slate-50 transition-colors">
                                    View Profile
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 text-center">
                        <a href="{{ route('employer.marketplace.index') }}" class="inline-flex items-center gap-2 text-brand-primary hover:text-indigo-700 font-medium transition-colors text-sm">
                            Explore all candidates
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- How It Works Section --}}
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-slate-900">How Gridspace Works</h2>
                <p class="mt-4 text-slate-600">A structured approach to hiring that works for everyone</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-slate-50 rounded-xl p-6 text-center hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">Structured Profiles</h3>
                    <p class="text-slate-600">Candidates complete comprehensive profiles with skills assessments, personality evaluations, and career achievements.</p>
                </div>

                <div class="bg-slate-50 rounded-xl p-6 text-center hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">Smart Matching</h3>
                    <p class="text-slate-600">Our engine matches candidates to jobs based on skill alignment, experience fit, and cultural compatibility.</p>
                </div>

                <div class="bg-slate-50 rounded-xl p-6 text-center hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">One-Click Applications</h3>
                    <p class="text-slate-600">Apply to jobs instantly with your complete profile. Employers see everything they need to make a decision.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Structured Hiring Explanation --}}
    <section class="py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900 mb-4">Structured Candidate Profiles</h2>
                    <p class="text-slate-600 mb-6">
                        Go beyond the traditional resume. Our comprehensive profiles capture the full picture of each candidate:
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-slate-700">Skills verification and assessment</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-slate-700">Personality trait analysis</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-slate-700">Temperament profiling</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-slate-700">Work preferences and motivations</span>
                        </li>
                    </ul>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="space-y-4">
                        <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-lg">
                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                <span class="text-brand-primary font-semibold">JD</span>
                            </div>
                            <div>
                                <div class="font-medium text-slate-900">John Doe</div>
                                <div class="text-sm text-slate-500">Senior Software Engineer</div>
                            </div>
                            <div class="ml-auto bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">
                                92% Match
                            </div>
                        </div>
                        <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-lg">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                <span class="text-purple-600 font-semibold">AS</span>
                            </div>
                            <div>
                                <div class="font-medium text-slate-900">Alice Smith</div>
                                <div class="text-sm text-slate-500">Product Designer</div>
                            </div>
                            <div class="ml-auto bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">
                                88% Match
                            </div>
                        </div>
                        <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-lg">
                            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                                <span class="text-amber-600 font-semibold">MJ</span>
                            </div>
                            <div>
                                <div class="font-medium text-slate-900">Michael Johnson</div>
                                <div class="text-sm text-slate-500">Data Analyst</div>
                            </div>
                            <div class="ml-auto bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">
                                85% Match
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Section --}}
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-slate-900">Employer Hiring Intelligence</h2>
                <p class="mt-4 text-slate-600">Make data-driven hiring decisions with comprehensive candidate insights</p>
            </div>

            <div class="grid md:grid-cols-4 gap-6">
                <div class="bg-slate-50 rounded-xl p-5">
                    <div class="text-3xl font-bold text-brand-primary mb-2">85%</div>
                    <div class="text-slate-600 text-sm">Average skill match score</div>
                </div>
                <div class="bg-slate-50 rounded-xl p-5">
                    <div class="text-3xl font-bold text-brand-primary mb-2">3.2x</div>
                    <div class="text-slate-600 text-sm">Faster time to hire</div>
                </div>
                <div class="bg-slate-50 rounded-xl p-5">
                    <div class="text-3xl font-bold text-brand-primary mb-2">92%</div>
                    <div class="text-slate-600 text-sm">Candidate quality improvement</div>
                </div>
                <div class="bg-slate-50 rounded-xl p-5">
                    <div class="text-3xl font-bold text-brand-primary mb-2">50%</div>
                    <div class="text-slate-600 text-sm">Reduction in interview cycles</div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-16 bg-brand-primary">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">Ready to Transform Your Hiring?</h2>
            <p class="text-slate-200 mb-8 text-lg">
                Join thousands of employers and candidates already using Gridspace to find their perfect match.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}?role=employer" class="inline-flex items-center justify-center px-8 py-3 text-base font-medium text-brand-primary bg-white rounded-lg hover:bg-slate-100 transition-colors">
                    Start Hiring
                </a>
                <a href="{{ route('register') }}?role=candidate" class="inline-flex items-center justify-center px-8 py-3 text-base font-medium text-white border-2 border-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Find Your Next Role
                </a>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-slate-400 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-white font-semibold mb-4">Gridspace</h3>
                    <p class="text-sm">Discover talent beyond the resume. Hire smarter with structured hiring intelligence.</p>
                </div>
                <div>
                    <h4 class="text-white font-medium mb-4">For Employers</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">Post a Job</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Browse Candidates</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-medium mb-4">For Candidates</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">Find Jobs</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Create Profile</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Resources</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-medium mb-4">Company</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">About</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Blog</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-sm text-center">
                &copy; {{ date('Y') }} Gridspace. All rights reserved.
            </div>
        </div>
    </footer>
</div>
@endsection
