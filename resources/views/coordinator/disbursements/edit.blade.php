<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('Edit Disbursement') }}: #{{ $disbursement->seq_no }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                <p class="mb-6 text-sm text-slate-500">
                    {{ $application->student->name }} &middot; {{ $application->program->name }}
                </p>

                <form method="POST" action="{{ route('coordinator.disbursements.update', [$application, $disbursement]) }}" class="grid grid-cols-2 gap-4">
                    @csrf
                    @method('PUT')

                    @include('coordinator.disbursements._fields')

                    <div class="col-span-2 flex items-center gap-4">
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                        <a href="{{ route('coordinator.applications.show', $application) }}" class="text-sm font-medium text-slate-600 hover:underline">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
