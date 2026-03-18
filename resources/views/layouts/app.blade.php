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
    <body class="font-sans antialiased bg-slate-50">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white shadow-sm border-b border-slate-200">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1">
                @yield('content')
            </main>

            <footer class="bg-white border-t border-slate-200 mt-auto">
                <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="text-sm text-gray-500">
                            &copy; {{ date('Y') }} Gridspace. All rights reserved.
                        </div>
                        <div class="flex gap-6 text-sm text-gray-500">
                            <a href="#" class="hover:text-gray-900 transition-colors">Privacy Policy</a>
                            <a href="#" class="hover:text-gray-900 transition-colors">Terms of Service</a>
                            <a href="#" class="hover:text-gray-900 transition-colors">Contact</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
