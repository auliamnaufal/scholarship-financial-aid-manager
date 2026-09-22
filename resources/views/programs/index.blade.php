<x-public-layout>
    <x-slot name="header">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-200">{{ __('Scholarship & Financial Aid') }}</p>
        <h1 class="font-display text-4xl font-bold tracking-tight text-white mt-3 sm:text-5xl">
            {{ __('Open Scholarships') }}
        </h1>
        <p class="text-indigo-100 mt-3 max-w-xl text-base sm:text-lg">
            {{ __('Browse available scholarship & financial aid programs. Log in to apply.') }}
        </p>

        @if ($programs->isNotEmpty())
            <dl class="mt-8 flex flex-wrap gap-x-10 gap-y-4 text-white">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-indigo-200">{{ __('Open programs') }}</dt>
                    <dd class="font-display text-2xl font-bold">{{ $programs->count() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-indigo-200">{{ __('Total funding') }}</dt>
                    <dd class="font-display text-2xl font-bold">{{ number_format($programs->sum('budget')) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-indigo-200">{{ __('Closing soonest') }}</dt>
                    <dd class="font-display text-2xl font-bold">{{ $programs->first()->application_deadline->format('d M Y') }}</dd>
                </div>
            </dl>
        @endif
    </x-slot>

    @if ($programs->count() >= 3)
        @php
            $slides = $programs->map(fn ($program) => [
                'id' => $program->id,
                'title' => $program->name.'.',
                'description' => trim(\Illuminate\Support\Str::limit($program->description ?? '', 140))
                    ?: __('Funded by :source, closing :date.', [
                        'source' => $program->funding_source,
                        'date' => $program->application_deadline->format('d M Y'),
                    ]),
                'image' => $program->coverImage(),
                'imageAlt' => $program->name,
                'background' => $program->coverGradient(),
                'overlay' => $program->type->label(),
                'action' => __('View details'),
                'href' => route('scholarships.show', $program),
            ])->all();
        @endphp

        <section class="border-b border-slate-200 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="font-display text-2xl font-bold tracking-tight text-slate-900">{{ __('Now accepting applications') }}</h2>
                        <p class="text-sm text-slate-500 mt-1">{{ __('Use the arrows, or click a panel to open it.') }}</p>
                    </div>
                </div>

                <x-squeeze-carousel
                    :slides="$slides"
                    :label="__('Open scholarships')"
                    height="clamp(200px, 32vw, 340px)"
                    autoplay
                    :interval="7000"
                />
            </div>
        </section>
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($programs->isEmpty())
                <x-card class="text-slate-500">{{ __('No open programs right now.') }}</x-card>
            @else
                <h2 class="font-display text-2xl font-bold tracking-tight text-slate-900 mb-6">{{ __('All open programs') }}</h2>

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

                            <a href="{{ route('scholarships.show', $program) }}" class="mt-4 inline-flex items-center gap-1 rounded-xl btn-gradient px-4 py-2 text-white shadow-sm transition text-sm font-semibold">
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
