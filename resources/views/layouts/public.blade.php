<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>document.documentElement.classList.add('js')</script>

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|plus-jakarta-sans:600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-900">
        <div class="min-h-screen flex flex-col">
            <nav x-data="{ open: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 8" :class="scrolled ? 'shadow-md' : ''" class="sticky top-0 z-40 animate-fade-in border-b border-slate-200/80 bg-white/90 backdrop-blur transition-shadow duration-300">
                <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <a href="{{ route('home') }}" class="group flex items-center gap-2.5 font-display font-bold text-slate-900">
                        <x-application-logo class="h-9 w-9 transition duration-300 group-hover:rotate-6 group-hover:scale-110" />
                        {{ config('app.name') }}
                    </a>

                    <div class="hidden items-center gap-1 text-sm font-medium text-slate-600 md:flex">
                        <a href="{{ route('home') }}#beasiswa" class="relative rounded-lg px-3 py-2 transition hover:text-slate-900 after:absolute after:inset-x-3 after:bottom-1 after:h-0.5 after:origin-left after:scale-x-0 after:rounded-full after:bg-indigo-600 after:transition-transform after:duration-300 hover:after:scale-x-100">Beasiswa</a>
                        <a href="{{ route('home') }}#cara-daftar" class="relative rounded-lg px-3 py-2 transition hover:text-slate-900 after:absolute after:inset-x-3 after:bottom-1 after:h-0.5 after:origin-left after:scale-x-0 after:rounded-full after:bg-indigo-600 after:transition-transform after:duration-300 hover:after:scale-x-100">Cara Daftar</a>
                        <a href="{{ route('home') }}#faq" class="relative rounded-lg px-3 py-2 transition hover:text-slate-900 after:absolute after:inset-x-3 after:bottom-1 after:h-0.5 after:origin-left after:scale-x-0 after:rounded-full after:bg-indigo-600 after:transition-transform after:duration-300 hover:after:scale-x-100">Tanya Jawab</a>
                    </div>

                    <div class="flex items-center gap-2 text-sm font-medium">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-lg bg-indigo-700 px-4 py-2 text-white shadow-sm transition hover:bg-indigo-600">{{ __('Dashboard') }}</a>
                        @else
                            <a href="{{ route('login') }}" class="hidden rounded-lg px-3 py-2 text-slate-700 transition hover:bg-slate-100 sm:inline-block">{{ __('Log in') }}</a>
                            <a href="{{ route('register') }}" class="rounded-lg bg-indigo-700 px-4 py-2 text-white shadow-sm transition hover:bg-indigo-600">{{ __('Register') }}</a>
                        @endauth
                        <button type="button" @click="open = ! open" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 md:hidden" aria-label="Menu">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div x-show="open" x-cloak x-transition class="border-t border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 md:hidden">
                    <a @click="open = false" href="{{ route('home') }}#beasiswa" class="block rounded-lg px-3 py-2 hover:bg-slate-100">Beasiswa</a>
                    <a @click="open = false" href="{{ route('home') }}#cara-daftar" class="block rounded-lg px-3 py-2 hover:bg-slate-100">Cara Daftar</a>
                    <a @click="open = false" href="{{ route('home') }}#faq" class="block rounded-lg px-3 py-2 hover:bg-slate-100">Tanya Jawab</a>
                    @guest
                        <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-100">{{ __('Log in') }}</a>
                    @endguest
                </div>
            </nav>

            @isset($header)
                <header class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-indigo-800">
                    <div class="pointer-events-none absolute -left-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-3xl" aria-hidden="true"></div>
                    <div class="pointer-events-none absolute bottom-0 right-0 h-80 w-80 translate-x-1/4 translate-y-1/4 rounded-full bg-sky-400/15 blur-3xl" aria-hidden="true"></div>
                    <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="bg-slate-900 text-slate-300">
                <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-4 lg:px-8">
                    <div class="md:col-span-2">
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-display text-lg font-bold text-white">
                            <x-application-logo class="h-9 w-9" />
                            {{ config('app.name') }}
                        </a>
                        <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-400">
                            Sistem pengelolaan beasiswa dan bantuan keuangan yang mencatat seluruh proses secara transparan mulai dari pendaftaran dan penilaian hingga pencairan dana.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Jelajahi</h3>
                        <ul class="mt-4 space-y-2 text-sm">
                            <li><a href="{{ route('home') }}#beasiswa" class="hover:text-white">Daftar Beasiswa</a></li>
                            <li><a href="{{ route('home') }}#cara-daftar" class="hover:text-white">Cara Mendaftar</a></li>
                            <li><a href="{{ route('home') }}#faq" class="hover:text-white">Tanya Jawab</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Akun</h3>
                        <ul class="mt-4 space-y-2 text-sm">
                            @auth
                                <li><a href="{{ route('dashboard') }}" class="hover:text-white">{{ __('Dashboard') }}</a></li>
                            @else
                                <li><a href="{{ route('login') }}" class="hover:text-white">{{ __('Log in') }}</a></li>
                                <li><a href="{{ route('register') }}" class="hover:text-white">{{ __('Register') }}</a></li>
                            @endauth
                        </ul>
                    </div>
                </div>
                <div class="border-t border-white/10">
                    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-slate-500 sm:flex-row sm:px-6 lg:px-8">
                        <p>&copy; {{ now()->year }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
                        <p>{{ __('Scholarship & Financial Aid Manager') }}</p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
