<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('Edit Requirement') }}: {{ $type->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                @if ($type->isInUse())
                    <p class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                        {{ __('This requirement is already in use, so how it is answered cannot change.') }}
                    </p>
                @endif

                <form method="POST" action="{{ route('coordinator.requirement-types.update', $type) }}">
                    @csrf
                    @method('PUT')

                    @include('coordinator.requirement-types._form')

                    <div class="mt-6 flex items-center gap-4">
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                        <a href="{{ route('coordinator.requirement-types.index') }}" class="text-sm font-medium text-slate-600 hover:underline">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
