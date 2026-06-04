<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Gridspace') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 pb-20 md:pb-0">
        <div class="min-h-screen flex flex-col">
            @if(!request()->routeIs('admin*'))
                @include('layouts.navigation')
                <x-mobile-bottom-nav />
            @endif

            @isset($header)
                <header class="bg-white shadow-sm border-b border-slate-200 z-30">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1 z-0">
                {{ $slot ?? '' }}
                @yield('content')
            </main>

            @stack('scripts')
        </div>
    </body>
</html>
