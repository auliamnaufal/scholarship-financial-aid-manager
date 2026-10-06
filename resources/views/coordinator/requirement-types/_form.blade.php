@php
    $type = $type ?? null;
@endphp

<div>
    <x-input-label for="name" :value="__('Name')" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $type?->name)" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="kind" :value="__('How it is answered')" />
    <select id="kind" name="kind" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach (\App\Enums\RequirementKind::cases() as $kind)
            <option value="{{ $kind->value }}" @selected(old('kind', $type?->kind?->value ?? 'file') === $kind->value)>{{ $kind->label() }}</option>
        @endforeach
    </select>
    <p class="mt-1 text-sm text-slate-500">{{ __('A file upload takes a PDF, DOC or DOCX of up to 5 MB. A written answer is typed straight into the form, 20 to 5000 characters.') }}</p>
    <x-input-error :messages="$errors->get('kind')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="description" :value="__('Description')" />
    <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $type?->description) }}</textarea>
    <p class="mt-1 text-sm text-slate-500">{{ __('Shown to applicants as a hint on the form.') }}</p>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>
