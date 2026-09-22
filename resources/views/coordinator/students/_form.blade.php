@php
    $student = $student ?? null;
    $profile = $student?->studentProfile;
    // On create the password is compulsory; on edit, blank leaves it alone.
    $creating = $student === null;
@endphp

<section>
    <h3 class="font-display text-lg font-semibold text-slate-900">{{ __('Account') }}</h3>

    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="name" :value="__('Full name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $student?->name)" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $student?->email)" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="$creating ? __('Password') : __('New password (leave blank to keep current)')" />
            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="$creating" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm password')" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="$creating" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>
    </div>
</section>

<div class="mt-8 border-t border-slate-200 pt-6">
    @include("partials._student-profile-fields", ["profile" => $profile])
</div>
