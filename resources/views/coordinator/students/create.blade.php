<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('New Student') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                <form method="POST" action="{{ route('coordinator.students.store') }}">
                    @csrf

                    @include('coordinator.students._form')

                    <div class="flex items-center justify-end mt-8 gap-3">
                        <a href="{{ route('coordinator.students.index') }}" class="text-slate-600 hover:underline">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Create Student') }}</x-primary-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
