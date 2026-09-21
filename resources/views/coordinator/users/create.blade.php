<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('New Staff Account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                <form method="POST" action="{{ route('coordinator.users.store') }}">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
                    </div>

                    <div class="mt-4">
                        <x-input-label :value="__('Roles')" />
                        <label class="flex items-center mt-2 text-sm">
                            <input type="checkbox" name="roles[]" value="reviewer" class="rounded border-slate-300 text-indigo-600 shadow-sm" {{ in_array('reviewer', old('roles', [])) ? 'checked' : '' }}>
                            <span class="ms-2">{{ __('Reviewer') }}</span>
                        </label>
                        <label class="flex items-center mt-2 text-sm">
                            <input type="checkbox" name="roles[]" value="coordinator" class="rounded border-slate-300 text-indigo-600 shadow-sm" {{ in_array('coordinator', old('roles', [])) ? 'checked' : '' }}>
                            <span class="ms-2">{{ __('Moderator') }}</span>
                        </label>
                        <x-input-error :messages="$errors->get('roles')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <x-primary-button>{{ __('Create account') }}</x-primary-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
