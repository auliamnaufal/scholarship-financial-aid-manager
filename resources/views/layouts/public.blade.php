<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-900">
        <div class="min-h-screen flex flex-col">
            <nav class="bg-white border-b border-slate-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex h-16 items-center justify-between">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold text-slate-900">
                        <x-application-logo class="h-8 w-8 fill-current text-indigo-600" />
                        {{ config('app.name') }}
                    </a>
                    <div class="flex items-center gap-4 text-sm font-medium">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-slate-600 hover:text-slate-900">{{ __('Dashboard') }}</a>
                        @else
                            <a href="{{ route('login') }}" class="text-slate-600 hover:text-slate-900">{{ __('Log in') }}</a>
                            <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-white hover:bg-indigo-500">{{ __('Register') }}</a>
                        @endauth
                    </div>
                </div>
            </nav>

            @isset($header)
                <header class="bg-white border-b border-slate-200">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
