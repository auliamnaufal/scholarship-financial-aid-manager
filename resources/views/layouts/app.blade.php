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
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-slate-50 lg:flex"
             x-data="{
                 open: false,
                 collapsed: (() => { try { return localStorage.getItem('sidebar-collapsed') === '1' } catch (e) { return false } })(),
             }"
             x-init="$watch('collapsed', value => { try { localStorage.setItem('sidebar-collapsed', value ? '1' : '0') } catch (e) {} })">
            @include('layouts.navigation')

            <div class="flex min-h-screen flex-1 flex-col lg:min-w-0">
                <!-- Page Heading -->
                @isset($header)
                    <header class="border-b border-slate-200 bg-white">
                        <div class="mx-auto flex max-w-7xl items-center gap-4 px-4 py-8 sm:px-6 lg:px-8">
                            <button type="button" @click="collapsed = ! collapsed"
                                :aria-label="collapsed ? '{{ __('Open menu') }}' : '{{ __('Close menu') }}'" :title="collapsed ? '{{ __('Open menu') }}' : '{{ __('Close menu') }}'"
                                class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-100 hover:text-slate-900 lg:inline-flex">
                            <svg class="h-5 w-5 transition-transform duration-300" :class="collapsed ? '' : 'rotate-90'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                            </svg>
                        </button>
                            <div class="min-w-0 flex-1">
                                {{ $header }}
                            </div>
                        </div>
                    </header>
                @else
                    <div class="hidden px-8 pt-6 lg:block">
                        <button type="button" @click="collapsed = ! collapsed"
                                :aria-label="collapsed ? '{{ __('Open menu') }}' : '{{ __('Close menu') }}'" :title="collapsed ? '{{ __('Open menu') }}' : '{{ __('Close menu') }}'"
                                class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-100 hover:text-slate-900 lg:inline-flex">
                            <svg class="h-5 w-5 transition-transform duration-300" :class="collapsed ? '' : 'rotate-90'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                            </svg>
                        </button>
                    </div>
                @endisset

                <!-- Page Content -->
                <main class="flex-1 animate-fade-in">
                    {{ $slot }}
                </main>
            </div>
        </div>
        <x-confirm-dialog />
    </body>
</html>
