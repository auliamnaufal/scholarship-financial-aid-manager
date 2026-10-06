<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('New Requirement') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                <form method="POST" action="{{ route('coordinator.requirement-types.store') }}">
                    @csrf

                    @include('coordinator.requirement-types._form', ['type' => null])

                    <div class="mt-6 flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('coordinator.requirement-types.index') }}" class="text-sm font-medium text-slate-600 hover:underline">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
