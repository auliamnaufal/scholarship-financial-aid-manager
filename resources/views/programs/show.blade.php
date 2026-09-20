<x-public-layout>
    <x-slot name="header">
        <a href="{{ route('home') }}" class="text-sm text-indigo-600 hover:underline">&larr; {{ __('Back to listings') }}</a>
        <h1 class="font-semibold text-2xl text-slate-900 mt-2">{{ $program->name }}</h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                <div class="flex justify-between items-start gap-2">
                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800">
                        {{ $program->type->label() }}
                    </span>
                    @if (! $program->isOpen())
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">{{ __('Closed') }}</span>
                    @endif
                </div>

                <dl class="grid grid-cols-2 gap-4 text-sm mt-4">
                    <div>
                        <dt class="text-slate-500">{{ __('Funded by') }}</dt>
                        <dd>{{ $program->funding_source }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Application deadline') }}</dt>
                        <dd>{{ $program->application_deadline->format('Y-m-d') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Budget') }}</dt>
                        <dd>{{ number_format($program->budget, 2) }}</dd>
                    </div>
                    @if ($program->type->value === 'need_based')
                        <div>
                            <dt class="text-slate-500">{{ __('Max family income') }}</dt>
                            <dd>{{ number_format($program->max_family_income, 2) }}</dd>
                        </div>
                    @else
                        <div>
                            <dt class="text-slate-500">{{ __('Minimum GPA') }}</dt>
                            <dd>{{ number_format($program->min_gpa, 2) }}</dd>
                        </div>
                    @endif
                </dl>

                @if ($program->description)
                    <p class="text-slate-600 mt-4">{{ $program->description }}</p>
                @endif

                @if ($program->isOpen())
                    <a href="{{ route('student.applications.create', $program) }}" class="mt-6 inline-block px-5 py-2.5 bg-indigo-600 text-white rounded-md hover:bg-indigo-500 text-sm font-medium">
                        {{ __('Apply now') }}
                    </a>
                    <p class="text-xs text-slate-400 mt-2">{{ __("You'll be asked to log in first.") }}</p>
                @endif
            </x-card>
        </div>
    </div>
</x-public-layout>
