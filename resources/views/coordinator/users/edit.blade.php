<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('Edit Staff Account') }}: {{ $staff->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                <form method="POST" action="{{ route('coordinator.users.update', $staff) }}">
                    @csrf
                    @method('PUT')

                    @include('coordinator.users._form')

                    <div class="flex items-center justify-end mt-6 gap-4">
                        <a href="{{ route('coordinator.users.index') }}" class="text-sm font-medium text-slate-600 hover:underline">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
