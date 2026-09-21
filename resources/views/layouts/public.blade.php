<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|sora:600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-900">
        <div class="min-h-screen flex flex-col">
            <nav class="sticky top-0 z-40 bg-white border-b border-slate-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex h-16 items-center justify-between">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 font-display font-bold text-slate-900">
                        <x-application-logo class="h-8 w-8 fill-current text-indigo-600" />
                        {{ config('app.name') }}
                    </a>
                    <div class="flex items-center gap-3 text-sm font-medium">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">{{ __('Dashboard') }}</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">{{ __('Log in') }}</a>
                            <a href="{{ route('register') }}" class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-white shadow-glow hover:from-indigo-500 hover:to-violet-500 transition">{{ __('Register') }}</a>
                        @endauth
                    </div>
                </div>
            </nav>

            @isset($header)
                <header class="relative overflow-hidden bg-gradient-to-br from-indigo-700 via-violet-700 to-fuchsia-600">
                    <div class="pointer-events-none absolute -left-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-3xl" aria-hidden="true"></div>
                    <div class="pointer-events-none absolute bottom-0 right-0 h-80 w-80 translate-x-1/4 translate-y-1/4 rounded-full bg-fuchsia-400/30 blur-3xl" aria-hidden="true"></div>
                    <div class="relative max-w-7xl mx-auto py-14 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="border-t border-slate-200 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-slate-500">
                    <p>&copy; {{ now()->year }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
                    <p>{{ __('Scholarship & Financial Aid Manager') }}</p>
                </div>
            </footer>
        </div>
    </body>
</html>
