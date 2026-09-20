<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm ring-1 ring-slate-900/5 rounded-xl p-6">
                <p class="text-gray-700 mb-4">
                    {{ __('Your account holds more than one role. Choose which dashboard to open:') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('coordinator.dashboard') }}" class="flex-1 text-center px-4 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        {{ __('Moderator Dashboard') }}
                    </a>
                    <a href="{{ route('reviewer.dashboard') }}" class="flex-1 text-center px-4 py-3 bg-gray-700 text-white rounded-md hover:bg-gray-800">
                        {{ __('Reviewer Dashboard') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
