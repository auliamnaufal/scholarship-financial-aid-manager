<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>document.documentElement.classList.add('js')</script>

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|plus-jakarta-sans:600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="min-h-screen lg:flex">
            <!-- Brand panel -->
            <div class="relative hidden overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-indigo-800 lg:flex lg:w-1/2 lg:flex-col lg:justify-between lg:p-12">
                <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 animate-blob rounded-full bg-white/10 blur-3xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute bottom-0 right-0 h-96 w-96 translate-x-1/3 translate-y-1/3 animate-blob rounded-full bg-sky-400/15 blur-3xl" style="animation-delay: -6s" aria-hidden="true"></div>
                <x-floating-icons layout="panel" />

                <a href="/" class="relative flex items-center gap-2 font-display text-xl font-bold text-white">
                    <x-application-logo class="h-9 w-9" />
                    {{ config('app.name') }}
                </a>

                <div class="relative max-w-md animate-fade-up">
                    <h1 class="font-display text-4xl font-bold leading-tight tracking-tight text-white">
                        {{ __('Fund your future.') }}<br>
                        <span class="text-amber-300">{{ __('Apply with confidence.') }}</span>
                    </h1>
                    <p class="mt-4 text-indigo-100">
                        {{ __('Manage scholarship and financial aid applications, reviews, and disbursements in one place.') }}
                    </p>
                </div>

                <p class="relative text-sm text-indigo-200">&copy; {{ now()->year }} {{ config('app.name') }}</p>
            </div>

            <!-- Form panel -->
            <div class="flex flex-1 flex-col justify-center bg-slate-50 px-6 py-12 lg:bg-white lg:px-16">
                <div class="mx-auto w-full max-w-sm animate-fade-up" style="animation-delay: 150ms">
                    <a href="/" class="mb-8 flex items-center justify-center gap-2 font-display text-xl font-bold text-slate-900 lg:hidden">
                        <x-application-logo class="h-9 w-9" />
                        {{ config('app.name') }}
                    </a>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
