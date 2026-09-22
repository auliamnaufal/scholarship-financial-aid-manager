<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('Apply to') }} {{ $program->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @php
                $missing = $profile?->missingFields() ?? [];
                $requirements = $program->requirements->sortBy(fn ($r) => [$r->is_required ? 0 : 1, $r->requirementType->name]);
            @endphp

            @if (! $profile || $missing)
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    <p class="font-medium">{{ __('Your biodata is incomplete.') }}</p>
                    <p class="mt-1">
                        @if (! $profile)
                            {{ __('You have not filled in any biodata yet.') }}
                        @else
                            {{ __('Still blank:') }} <span class="font-medium">{{ implode(', ', $missing) }}</span>.
                        @endif
                        {{ __('Eligibility is checked against it, and an award cannot be paid without a bank account.') }}
                    </p>
                    <a href="{{ route('student.biodata.edit') }}" class="mt-2 inline-block font-medium underline">{{ __('Fill in biodata') }}</a>
                </div>
            @endif

            <x-card>
                <div class="text-sm text-slate-500">
                    <p>{{ __('Funded by') }} <span class="text-slate-900">{{ $program->funding_source }}</span> · {{ __('Deadline') }} {{ $program->application_deadline->format('d M Y') }}</p>
                    @if ($program->min_gpa)
                        <p class="mt-1">{{ __('Minimum GPA') }}: {{ number_format((float) $program->min_gpa, 2) }} — {{ __('yours is') }} {{ $profile?->gpa ?? '—' }}</p>
                    @endif
                    @if ($program->max_family_income)
                        <p class="mt-1">{{ __('Maximum family income') }}: {{ number_format((float) $program->max_family_income) }} — {{ __('yours is recorded as') }} {{ $profile?->family_income ? number_format((float) $profile->family_income) : '—' }}</p>
                    @endif
                </div>

                <x-input-error :messages="$errors->get('program_id')" class="mt-4" />

                <form method="POST" action="{{ route('student.applications.store') }}" enctype="multipart/form-data" class="mt-6">
                    @csrf
                    <input type="hidden" name="program_id" value="{{ $program->id }}">

                    <div>
                        <x-input-label for="semester" :value="__('Semester')" />
                        <x-text-input id="semester" name="semester" type="text" class="mt-1 block w-full"
                            placeholder="e.g. 2026-1" :value="old('semester')" required autofocus />
                        <x-input-error :messages="$errors->get('semester')" class="mt-2" />
                    </div>

                    @if ($requirements->isEmpty())
                        <p class="mt-6 text-sm text-slate-500">
                            {{ __('This scholarship asks for no supporting documents.') }}
                        </p>
                    @else
                        <div class="mt-8 border-t border-slate-200 pt-6">
                            <h3 class="font-display text-lg font-semibold text-slate-900">{{ __('What this scholarship asks for') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ __('Files must be PDF, DOC or DOCX, up to 5 MB each.') }}
                            </p>

                            <div class="mt-4 space-y-5">
                                @foreach ($requirements as $requirement)
                                    @php
                                        $type = $requirement->requirementType;
                                        $field = "answers.{$type->id}";
                                        $name = "answers[{$type->id}]";
                                    @endphp

                                    <div class="rounded-xl border border-slate-200 p-4">
                                        <x-input-label :for="$field">
                                            {{ $type->name }}
                                            @if ($requirement->is_required)
                                                <span class="text-red-600">*</span>
                                            @else
                                                <span class="ml-1 text-xs font-normal text-slate-500">({{ __('optional') }})</span>
                                            @endif
                                        </x-input-label>

                                        @if ($requirement->instructions)
                                            <p class="mt-1 text-sm text-slate-500">{{ $requirement->instructions }}</p>
                                        @elseif ($type->description)
                                            <p class="mt-1 text-sm text-slate-500">{{ $type->description }}</p>
                                        @endif

                                        @if ($type->isFile())
                                            <input
                                                id="{{ $field }}"
                                                name="{{ $name }}"
                                                type="file"
                                                accept=".pdf,.doc,.docx"
                                                @required($requirement->is_required)
                                                class="mt-2 block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100"
                                            >
                                        @else
                                            <textarea
                                                id="{{ $field }}"
                                                name="{{ $name }}"
                                                rows="6"
                                                @required($requirement->is_required)
                                                class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                placeholder="{{ __('At least 20 characters.') }}"
                                            >{{ old($field) }}</textarea>
                                        @endif

                                        <x-input-error :messages="$errors->get($field)" class="mt-2" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center justify-end mt-8 gap-3">
                        <a href="{{ route('scholarships.show', $program) }}" class="text-slate-600 hover:underline">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Submit Application') }}</x-primary-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
