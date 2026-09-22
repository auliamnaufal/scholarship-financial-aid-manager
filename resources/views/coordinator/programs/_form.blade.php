@php
    $program = $program ?? null;
@endphp

<div x-data="{ type: '{{ old('type', $program?->type?->value ?? 'need_based') }}' }">
    <div>
        <x-input-label for="name" :value="__('Program Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $program?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="type" :value="__('Type')" />
        <select id="type" name="type" x-model="type" class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" required>
            <option value="need_based" @selected(old('type', $program?->type?->value) === 'need_based')>{{ __('Need-based') }}</option>
            <option value="merit_based" @selected(old('type', $program?->type?->value) === 'merit_based')>{{ __('Merit-based') }}</option>
        </select>
        <x-input-error :messages="$errors->get('type')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="funding_source" :value="__('Funding Source')" />
        <x-text-input id="funding_source" name="funding_source" type="text" class="mt-1 block w-full" :value="old('funding_source', $program?->funding_source)" required />
        <x-input-error :messages="$errors->get('funding_source')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="budget" :value="__('Budget')" />
        <x-text-input id="budget" name="budget" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('budget', $program?->budget)" required />
        <x-input-error :messages="$errors->get('budget')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="application_deadline" :value="__('Application Deadline')" />
        <x-text-input id="application_deadline" name="application_deadline" type="date" class="mt-1 block w-full" :value="old('application_deadline', $program?->application_deadline?->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('application_deadline')" class="mt-2" />
    </div>

    <div class="mt-4" x-show="type === 'need_based'">
        <x-input-label for="max_family_income" :value="__('Max Family Income')" />
        <x-text-input id="max_family_income" name="max_family_income" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('max_family_income', $program?->max_family_income)" />
        <x-input-error :messages="$errors->get('max_family_income')" class="mt-2" />
    </div>

    <div class="mt-4" x-show="type === 'merit_based'">
        <x-input-label for="min_gpa" :value="__('Minimum GPA')" />
        <x-text-input id="min_gpa" name="min_gpa" type="number" step="0.01" min="0" max="4" class="mt-1 block w-full" :value="old('min_gpa', $program?->min_gpa)" />
        <x-input-error :messages="$errors->get('min_gpa')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">{{ old('description', $program?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    @php
        // What this programme currently asks for, keyed by requirement type.
        $current = $program?->requirements->keyBy('requirement_type_id') ?? collect();
    @endphp

    <fieldset class="mt-8 border-t border-slate-200 pt-6">
        <legend class="sr-only">{{ __('Application requirements') }}</legend>

        <h3 class="font-display text-lg font-semibold text-slate-900">{{ __('Application requirements') }}</h3>
        <p class="mt-1 text-sm text-slate-500">
            {{ __('Tick what applicants must send in. Only the items you tick appear on their application form, so a scholarship that needs no recommendation letter simply does not ask for one.') }}
        </p>

        <div class="mt-4 space-y-3">
            @foreach ($requirementTypes as $type)
                @php
                    $existing = $current->get($type->id);
                    $enabled = (bool) old("requirements.{$type->id}.enabled", $existing !== null);
                    $isRequired = (bool) old("requirements.{$type->id}.is_required", $existing?->is_required ?? true);
                @endphp

                <div
                    x-data="{ enabled: {{ $enabled ? 'true' : 'false' }} }"
                    class="rounded-xl border border-slate-200 p-4 transition"
                    :class="enabled ? 'bg-indigo-50/40 border-indigo-200' : 'bg-white'"
                >
                    <label class="flex items-start gap-3">
                        <input type="hidden" name="requirements[{{ $type->id }}][enabled]" value="0">
                        <input
                            type="checkbox"
                            name="requirements[{{ $type->id }}][enabled]"
                            value="1"
                            {{-- Checked server-side as well as by Alpine: without it the
                                 box renders unticked before Alpine boots, and submitting
                                 then would clear the programme's requirements. --}}
                            @checked($enabled)
                            x-model="enabled"
                            class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        >
                        <span>
                            <span class="font-medium text-slate-900">{{ $type->name }}</span>
                            <span class="ml-2 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                                {{ $type->kind->label() }}
                            </span>
                            @if ($type->description)
                                <span class="mt-0.5 block text-sm text-slate-500">{{ $type->description }}</span>
                            @endif
                        </span>
                    </label>

                    <div x-show="enabled" x-cloak class="mt-3 space-y-3 pl-7">
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="hidden" name="requirements[{{ $type->id }}][is_required]" value="0">
                            <input
                                type="checkbox"
                                name="requirements[{{ $type->id }}][is_required]"
                                value="1"
                                @checked($isRequired)
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            >
                            {{ __('Compulsory — the application cannot be submitted without it') }}
                        </label>

                        <div>
                            <x-input-label
                                for="requirements-{{ $type->id }}-instructions"
                                :value="__('Instructions for applicants (optional)')"
                                class="text-xs"
                            />
                            <textarea
                                id="requirements-{{ $type->id }}-instructions"
                                name="requirements[{{ $type->id }}][instructions]"
                                rows="2"
                                class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >{{ old("requirements.{$type->id}.instructions", $existing?->instructions) }}</textarea>
                            <x-input-error :messages="$errors->get("requirements.{$type->id}.instructions")" class="mt-1" />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </fieldset>
</div>
