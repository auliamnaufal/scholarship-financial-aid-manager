<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|sora:600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="min-h-screen lg:flex">
            <!-- Brand panel -->
            <div class="relative hidden overflow-hidden bg-gradient-to-br from-indigo-700 via-violet-700 to-fuchsia-600 lg:flex lg:w-1/2 lg:flex-col lg:justify-between lg:p-12">
                <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-white/10 blur-3xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute bottom-0 right-0 h-96 w-96 translate-x-1/3 translate-y-1/3 rounded-full bg-fuchsia-400/30 blur-3xl" aria-hidden="true"></div>

                <a href="/" class="relative flex items-center gap-2 font-display text-xl font-bold text-white">
                    <x-application-logo class="h-9 w-9 fill-current text-white" />
                    {{ config('app.name') }}
                </a>

                <div class="relative max-w-md">
                    <h1 class="font-display text-4xl font-bold leading-tight tracking-tight text-white">
                        {{ __('Fund your future.') }}<br>{{ __('Apply with confidence.') }}
                    </h1>
                    <p class="mt-4 text-indigo-100">
                        {{ __('Manage scholarship and financial aid applications, reviews, and disbursements — all in one place.') }}
                    </p>
                </div>

                <p class="relative text-sm text-indigo-200">&copy; {{ now()->year }} {{ config('app.name') }}</p>
            </div>

            <!-- Form panel -->
            <div class="flex flex-1 flex-col justify-center bg-slate-50 px-6 py-12 lg:bg-white lg:px-16">
                <div class="mx-auto w-full max-w-sm">
                    <a href="/" class="mb-8 flex items-center justify-center gap-2 font-display text-xl font-bold text-slate-900 lg:hidden">
                        <x-application-logo class="h-9 w-9 fill-current text-indigo-600" />
                        {{ config('app.name') }}
                    </a>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
