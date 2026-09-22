@php
    // Shared by the coordinator student form and the student biodata page.
    $profile = $profile ?? null;
@endphp

<section>
    <h3 class="font-display text-lg font-semibold text-slate-900">{{ __('Academic details') }}</h3>

    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="nim" :value="__('Student number (NIM)')" />
            <x-text-input id="nim" name="nim" type="text" class="mt-1 block w-full" :value="old('nim', $profile?->nim)" />
            <x-input-error :messages="$errors->get('nim')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="faculty" :value="__('Faculty')" />
            <x-text-input id="faculty" name="faculty" type="text" class="mt-1 block w-full" :value="old('faculty', $profile?->faculty)" />
            <x-input-error :messages="$errors->get('faculty')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="study_program" :value="__('Study programme')" />
            <x-text-input id="study_program" name="study_program" type="text" class="mt-1 block w-full" :value="old('study_program', $profile?->study_program)" />
            <x-input-error :messages="$errors->get('study_program')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="year_enrolled" :value="__('Year enrolled')" />
            <x-text-input id="year_enrolled" name="year_enrolled" type="number" min="1900" max="{{ date('Y') + 1 }}" class="mt-1 block w-full" :value="old('year_enrolled', $profile?->year_enrolled)" required />
            <x-input-error :messages="$errors->get('year_enrolled')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="gpa" :value="__('GPA')" />
            <x-text-input id="gpa" name="gpa" type="number" step="0.01" min="0" max="4" class="mt-1 block w-full" :value="old('gpa', $profile?->gpa)" required />
            <x-input-error :messages="$errors->get('gpa')" class="mt-2" />
        </div>
    </div>
</section>

<section class="mt-8 border-t border-slate-200 pt-6">
    <h3 class="font-display text-lg font-semibold text-slate-900">{{ __('Contact') }}</h3>

    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="phone" :value="__('Phone number')" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $profile?->phone)" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="address" :value="__('Address')" />
            <textarea id="address" name="address" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $profile?->address) }}</textarea>
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>
    </div>
</section>

<section class="mt-8 border-t border-slate-200 pt-6">
    <h3 class="font-display text-lg font-semibold text-slate-900">{{ __('Family circumstances') }}</h3>
    <p class="mt-1 text-sm text-slate-500">
        {{ __('Need-based scholarships are checked against the family income recorded here.') }}
    </p>

    <div class="mt-4 grid gap-4 sm:grid-cols-3">
        <div>
            <x-input-label for="family_income" :value="__('Family income (per year)')" />
            <x-text-input id="family_income" name="family_income" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('family_income', $profile?->family_income)" />
            <x-input-error :messages="$errors->get('family_income')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="parent_occupation" :value="__('Parent occupation')" />
            <x-text-input id="parent_occupation" name="parent_occupation" type="text" class="mt-1 block w-full" :value="old('parent_occupation', $profile?->parent_occupation)" />
            <x-input-error :messages="$errors->get('parent_occupation')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="dependents_count" :value="__('Dependents in the household')" />
            <x-text-input id="dependents_count" name="dependents_count" type="number" min="0" max="20" class="mt-1 block w-full" :value="old('dependents_count', $profile?->dependents_count)" />
            <x-input-error :messages="$errors->get('dependents_count')" class="mt-2" />
        </div>
    </div>
</section>

<section class="mt-8 border-t border-slate-200 pt-6">
    <h3 class="font-display text-lg font-semibold text-slate-900">{{ __('Bank account') }}</h3>
    <p class="mt-1 text-sm text-slate-500">
        {{ __('Where an awarded scholarship is paid. Without it a disbursement has nowhere to go.') }}
    </p>

    <div class="mt-4 grid gap-4 sm:grid-cols-3">
        <div>
            <x-input-label for="bank_name" :value="__('Bank')" />
            <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1 block w-full" :value="old('bank_name', $profile?->bank_name)" />
            <x-input-error :messages="$errors->get('bank_name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="bank_account_number" :value="__('Account number')" />
            <x-text-input id="bank_account_number" name="bank_account_number" type="text" class="mt-1 block w-full" :value="old('bank_account_number', $profile?->bank_account_number)" />
            <x-input-error :messages="$errors->get('bank_account_number')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="bank_account_holder" :value="__('Account holder')" />
            <x-text-input id="bank_account_holder" name="bank_account_holder" type="text" class="mt-1 block w-full" :value="old('bank_account_holder', $profile?->bank_account_holder)" />
            <x-input-error :messages="$errors->get('bank_account_holder')" class="mt-2" />
        </div>
    </div>
</section>
