@php
    $staff = $staff ?? null;
    $roles = old('roles', $staff?->getRoleNames()->all() ?? []);
@endphp

<div>
    <x-input-label for="name" :value="__('Name')" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $staff?->name)" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="email" :value="__('Email')" />
    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $staff?->email)" required />
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="password" :value="$staff ? __('New password (leave blank to keep current)') : __('Password')" />
    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="! $staff" />
    <x-input-error :messages="$errors->get('password')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="! $staff" />
</div>

<div class="mt-4">
    <x-input-label :value="__('Roles')" />
    <label class="flex items-center mt-2 text-sm">
        <input type="checkbox" name="roles[]" value="reviewer" class="rounded border-slate-300 text-indigo-600 shadow-sm" {{ in_array('reviewer', $roles) ? 'checked' : '' }}>
        <span class="ms-2">{{ __('Reviewer') }}</span>
    </label>
    <label class="flex items-center mt-2 text-sm">
        <input type="checkbox" name="roles[]" value="coordinator" class="rounded border-slate-300 text-indigo-600 shadow-sm" {{ in_array('coordinator', $roles) ? 'checked' : '' }}>
        <span class="ms-2">{{ __('Moderator') }}</span>
    </label>
    <p class="mt-2 text-sm text-slate-500">{{ __('A moderator is always a reviewer too.') }}</p>
    <x-input-error :messages="$errors->get('roles')" class="mt-2" />
</div>
