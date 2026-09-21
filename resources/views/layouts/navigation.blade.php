<div x-data="{ open: false }">
    <!-- Mobile top bar -->
    <div class="sticky top-0 z-40 flex items-center justify-between gap-4 border-b border-slate-200 bg-white px-4 py-3 lg:hidden">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-display font-bold text-slate-900">
            <x-application-logo class="h-7 w-7 fill-current text-indigo-600" />
            {{ config('app.name') }}
        </a>
        <button @click="open = true" aria-label="{{ __('Open menu') }}" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
            </svg>
        </button>
    </div>

    <!-- Mobile overlay -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-slate-900/60 lg:hidden"
         style="display: none;"
         @click="open = false"></div>

    <!-- Sidebar -->
    <aside
        :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-50 flex w-72 shrink-0 transform flex-col bg-gradient-to-b from-slate-900 via-slate-900 to-indigo-950 transition-transform duration-200 ease-in-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
    >
        <div class="flex items-center gap-2 px-6 py-6">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-display text-lg font-bold text-white">
                <x-application-logo class="h-8 w-8 fill-current text-indigo-400" />
                {{ config('app.name') }}
            </a>
            <button @click="open = false" aria-label="{{ __('Close menu') }}" class="ms-auto text-slate-400 hover:text-white lg:hidden">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-4 pb-4">
            <x-nav-link :href="route('home')" :active="request()->routeIs('home', 'scholarships.show')">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6.5A2.5 2.5 0 0 1 6.5 4H12v16H6.5A2.5 2.5 0 0 1 4 17.5v-11Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 6.5A2.5 2.5 0 0 0 17.5 4H12v16h5.5a2.5 2.5 0 0 0 2.5-2.5v-11Z" />
                </svg>
                {{ __('Scholarships') }}
            </x-nav-link>

            @role('student')
                <x-nav-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard', 'student.applications.*')">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <rect x="5" y="3" width="14" height="18" rx="2" />
                        <path stroke-linecap="round" d="M8.5 8h7M8.5 12h7M8.5 16h4" />
                    </svg>
                    {{ __('My Applications') }}
                </x-nav-link>
            @endrole

            @role('reviewer')
                <x-nav-link :href="route('reviewer.dashboard')" :active="request()->routeIs('reviewer.dashboard', 'reviewer.applications.*')">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12.5l2.5 2.5L16 9.5" />
                    </svg>
                    {{ __('Review Applications') }}
                </x-nav-link>
            @endrole

            @role('coordinator')
                <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Moderator') }}</p>
                <x-nav-link :href="route('coordinator.dashboard')" :active="request()->routeIs('coordinator.dashboard', 'coordinator.programs.*')">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <line x1="4" y1="6" x2="20" y2="6" /><circle cx="9" cy="6" r="1.75" fill="currentColor" stroke="none" />
                        <line x1="4" y1="12" x2="20" y2="12" /><circle cx="15" cy="12" r="1.75" fill="currentColor" stroke="none" />
                        <line x1="4" y1="18" x2="20" y2="18" /><circle cx="7" cy="18" r="1.75" fill="currentColor" stroke="none" />
                    </svg>
                    {{ __('Scholarships Management') }}
                </x-nav-link>
                <x-nav-link :href="route('coordinator.applications.index')" :active="request()->routeIs('coordinator.applications.*')">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <rect x="5" y="3" width="14" height="18" rx="2" />
                        <path stroke-linecap="round" d="M8.5 8h7M8.5 12h7M8.5 16h4" />
                    </svg>
                    {{ __('Applications') }}
                </x-nav-link>
                <x-nav-link :href="route('coordinator.users.index')" :active="request()->routeIs('coordinator.users.*')">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <circle cx="9" cy="8" r="3" />
                        <path stroke-linecap="round" d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6" />
                        <circle cx="17" cy="9" r="2.5" />
                        <path stroke-linecap="round" d="M15.5 14.1c2.6.5 4.5 2.7 4.5 5.9" />
                    </svg>
                    {{ __('Manage Users') }}
                </x-nav-link>
            @endrole
        </nav>

        <div class="border-t border-white/10 p-4">
            <div class="flex items-center gap-3 rounded-xl bg-white/5 p-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-fuchsia-500 text-xs font-semibold text-white">
                    {{ Str::of(Auth::user()->name)->substr(0, 1)->upper() }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                    <p class="truncate text-xs text-slate-400">{{ Auth::user()->email }}</p>
                </div>
            </div>

            <div class="mt-2 space-y-1">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <circle cx="12" cy="8" r="3.25" />
                        <path stroke-linecap="round" d="M5 20c0-3.9 3.1-7 7-7s7 3.1 7 7" />
                    </svg>
                    {{ __('Profile') }}
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}"
                       onclick="event.preventDefault(); this.closest('form').submit();"
                       class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M18 12h-9m9 0l-3-3m3 3l-3 3" />
                        </svg>
                        {{ __('Log Out') }}
                    </a>
                </form>
            </div>
        </div>
    </aside>
</div>
