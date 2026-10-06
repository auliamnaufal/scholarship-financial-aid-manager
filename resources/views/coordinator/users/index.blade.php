<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('Staff Accounts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ session('status') }}</div>
            @endif

            <x-input-error :messages="$errors->get('user')" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4" />

            <x-card>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">{{ __('Reviewers & Moderators') }}</h3>
                    <a href="{{ route('coordinator.users.create') }}" class="px-4 py-2 bg-indigo-700 text-white rounded-xl shadow-sm transition hover:bg-indigo-600 text-sm font-medium">
                        {{ __('New account') }}
                    </a>
                </div>

                @if ($users->isEmpty())
                    <p class="text-slate-500">{{ __('No staff accounts yet.') }}</p>
                @else
                    <div class="overflow-x-auto"><table class="data-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">{{ __('Name') }}</th>
                                <th class="px-4 py-2">{{ __('Email') }}</th>
                                <th class="px-4 py-2">{{ __('Roles') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                @php $archived = $user->trashed(); @endphp
                                <tr class="{{ $archived ? 'opacity-60' : '' }}">
                                    <td class="px-4 py-2">
                                        {{ $user->name }}
                                        @if ($archived)
                                            <span class="ml-2 inline-flex items-center rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-600">{{ __('Archived') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">{{ $user->email }}</td>
                                    <td class="px-4 py-2 space-x-1">
                                        @foreach ($user->roles as $role)
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full ring-1 ring-inset ring-black/5 bg-indigo-100 text-indigo-700">
                                                {{ $role->name === 'coordinator' ? 'Moderator' : ucfirst($role->name) }}
                                            </span>
                                        @endforeach
                                        @if ($user->hasRole('coordinator') && ! $user->hasRole('reviewer'))
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full ring-1 ring-inset ring-black/5 bg-slate-100 text-slate-600" title="{{ __('A moderator is always a reviewer too.') }}">
                                                Reviewer
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="flex items-center justify-end gap-3">
                                            @if ($archived)
                                                <form method="POST" action="{{ route('coordinator.users.restore', $user->id) }}">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Restore') }}</button>
                                                </form>
                                            @else
                                                <a href="{{ route('coordinator.users.edit', $user) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                                @if ($user->is(auth()->user()))
                                                    <span class="text-sm text-slate-400" title="{{ __('You cannot archive your own account here.') }}">{{ __('Archive') }}</span>
                                                @elseif ($user->programs_managed_count > 0)
                                                    <span class="text-sm text-slate-400" title="{{ __('Still manages scholarships, so it cannot be archived.') }}">{{ __('Archive') }}</span>
                                                @else
                                                    <form
                                                        method="POST"
                                                        action="{{ route('coordinator.users.destroy', $user) }}"
                                                        data-confirm-title="{{ __('Archive this account?') }}"
                                                        data-confirm-message="{{ __('They can no longer sign in. Reviews they wrote are kept.') }}"
                                                        data-confirm-label="{{ __('Yes, archive') }}"
                                                        data-confirm-tone="danger"
                                                    >
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-sm font-medium text-red-600 hover:underline">{{ __('Archive') }}</button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>
