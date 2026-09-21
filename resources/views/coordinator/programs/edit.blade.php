<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('Edit Program') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-soft ring-1 ring-slate-900/5 rounded-xl p-6">
                <form method="POST" action="{{ route('coordinator.programs.update', $program) }}">
                    @csrf
                    @method('PUT')

                    @include('coordinator.programs._form')

                    <div class="flex items-center justify-end mt-6 gap-3">
                        <a href="{{ route('coordinator.dashboard') }}" class="text-slate-600 hover:underline">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
