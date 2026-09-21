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
</div>
