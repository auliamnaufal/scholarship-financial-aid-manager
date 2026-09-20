<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Staff Accounts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            <x-card>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">{{ __('Reviewers & Moderators') }}</h3>
                    <a href="{{ route('coordinator.users.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500 text-sm font-medium">
                        {{ __('New account') }}
                    </a>
                </div>

                @if ($users->isEmpty())
                    <p class="text-gray-500">{{ __('No staff accounts yet.') }}</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                                <th class="px-4 py-2">{{ __('Name') }}</th>
                                <th class="px-4 py-2">{{ __('Email') }}</th>
                                <th class="px-4 py-2">{{ __('Roles') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($users as $user)
                                <tr>
                                    <td class="px-4 py-2">{{ $user->name }}</td>
                                    <td class="px-4 py-2">{{ $user->email }}</td>
                                    <td class="px-4 py-2 space-x-1">
                                        @foreach ($user->roles as $role)
                                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800">
                                                {{ $role->name === 'coordinator' ? 'Moderator' : ucfirst($role->name) }}
                                            </span>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>
