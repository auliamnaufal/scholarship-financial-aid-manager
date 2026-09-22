<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('My Biodata') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ session('status') }}</div>
            @endif

            @php $missing = $profile?->missingFields() ?? []; @endphp

            @if ($missing)
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    <p class="font-medium">{{ __('Some details are still missing.') }}</p>
                    <p class="mt-1">
                        {{ __('Scholarships check these when you apply, and an award cannot be paid without a bank account. Still blank:') }}
                        <span class="font-medium">{{ implode(', ', $missing) }}</span>
                    </p>
                </div>
            @endif

            <x-card>
                <p class="text-sm text-slate-500">
                    {{ __('This is the information scholarships are matched against. Your name, email and password live on the') }}
                    <a href="{{ route('profile.edit') }}" class="font-medium text-indigo-600 hover:underline">{{ __('account page') }}</a>.
                </p>

                <form method="POST" action="{{ route('student.biodata.update') }}" class="mt-6">
                    @csrf
                    @method('PUT')

                    @include('student.biodata._fields')

                    <div class="flex items-center justify-end mt-8 gap-3">
                        <x-primary-button>{{ __('Save Biodata') }}</x-primary-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
