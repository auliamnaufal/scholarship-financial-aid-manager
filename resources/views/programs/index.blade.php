<x-public-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-2xl text-slate-900">{{ __('Open Scholarships') }}</h1>
        <p class="text-slate-500 mt-1">{{ __('Browse available scholarship & financial aid programs. Log in to apply.') }}</p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($programs->isEmpty())
                <x-card class="text-slate-500">{{ __('No open programs right now.') }}</x-card>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($programs as $program)
                        <x-card class="hover:shadow-md transition">
                            <div class="flex justify-between items-start gap-2">
                                <h3 class="font-medium text-lg text-slate-900">{{ $program->name }}</h3>
                                <span class="shrink-0 px-2.5 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800">
                                    {{ $program->type->label() }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-500 mt-1">{{ __('Funded by') }} {{ $program->funding_source }}</p>
                            <p class="text-sm text-slate-500">{{ __('Deadline') }}: {{ $program->application_deadline->format('Y-m-d') }}</p>

                            <a href="{{ route('scholarships.show', $program) }}" class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500 text-sm font-medium">
                                {{ __('View details') }}
                            </a>
                        </x-card>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-public-layout>
