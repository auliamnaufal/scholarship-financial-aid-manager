<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p>{{ __("You're logged in!") }}</p>
                    <p class="mt-2 text-sm text-gray-500">
                        {{ __('No role has been assigned to your account yet, so there is nothing else to show here. Ask a coordinator to assign you a role.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
