<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-soft ring-1 ring-slate-900/5 rounded-xl p-6">
                <p class="text-slate-700 mb-4">
                    {{ __('Your account holds more than one role. Choose which dashboard to open:') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('coordinator.dashboard') }}" class="flex-1 text-center px-4 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl shadow-sm transition hover:from-indigo-500 hover:to-violet-500 text-sm font-medium">
                        {{ __('Moderator Dashboard') }}
                    </a>
                    <a href="{{ route('reviewer.dashboard') }}" class="flex-1 text-center px-4 py-3 bg-slate-800 text-white rounded-lg shadow-sm transition hover:bg-slate-700 text-sm font-medium">
                        {{ __('Reviewer Dashboard') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
