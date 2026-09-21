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

            <x-card>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">{{ __('Reviewers & Moderators') }}</h3>
                    <a href="{{ route('coordinator.users.create') }}" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl shadow-sm transition hover:from-indigo-500 hover:to-violet-500 text-sm font-medium">
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
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td class="px-4 py-2">{{ $user->name }}</td>
                                    <td class="px-4 py-2">{{ $user->email }}</td>
                                    <td class="px-4 py-2 space-x-1">
                                        @foreach ($user->roles as $role)
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full ring-1 ring-inset ring-black/5 bg-indigo-100 text-indigo-700">
                                                {{ $role->name === 'coordinator' ? 'Moderator' : ucfirst($role->name) }}
                                            </span>
                                        @endforeach
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
