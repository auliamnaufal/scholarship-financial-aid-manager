<x-public-layout>
    <x-slot name="header">
        <h1 class="font-display text-4xl font-bold tracking-tight text-white">{{ __('Open Scholarships') }}</h1>
        <p class="text-indigo-100 mt-2 max-w-xl">{{ __('Browse available scholarship & financial aid programs. Log in to apply.') }}</p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($programs->isEmpty())
                <x-card class="text-slate-500">{{ __('No open programs right now.') }}</x-card>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($programs as $program)
                        @php $isNeedBased = $program->type->value === 'need_based'; @endphp
                        <x-card class="border-t-4 {{ $isNeedBased ? 'border-t-indigo-500' : 'border-t-fuchsia-500' }} transition hover:-translate-y-1 hover:shadow-lg">
                            <div class="flex justify-between items-start gap-2">
                                <h3 class="font-display font-semibold text-lg text-slate-900">{{ $program->name }}</h3>
                                <span class="shrink-0 inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full ring-1 ring-inset ring-black/5 {{ $isNeedBased ? 'bg-indigo-100 text-indigo-700' : 'bg-fuchsia-100 text-fuchsia-700' }}">
                                    {{ $program->type->label() }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-500 mt-1">{{ __('Funded by') }} {{ $program->funding_source }}</p>
                            <p class="text-sm text-slate-500">{{ __('Deadline') }}: {{ $program->application_deadline->format('Y-m-d') }}</p>

                            <a href="{{ route('scholarships.show', $program) }}" class="mt-4 inline-flex items-center gap-1 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-white shadow-sm transition hover:from-indigo-500 hover:to-violet-500 text-sm font-semibold">
                                {{ __('View details') }}
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        </x-card>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-public-layout>
