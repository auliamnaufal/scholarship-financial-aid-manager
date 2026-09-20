<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Programs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm ring-1 ring-slate-900/5 rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">{{ __('Programs You Manage') }}</h3>
                    <a href="{{ route('coordinator.programs.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">
                        {{ __('New Program') }}
                    </a>
                </div>

                @if ($programs->isEmpty())
                    <p class="text-gray-500">{{ __("You haven't created any programs yet.") }}</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                                <th class="px-4 py-2">{{ __('Name') }}</th>
                                <th class="px-4 py-2">{{ __('Type') }}</th>
                                <th class="px-4 py-2">{{ __('Deadline') }}</th>
                                <th class="px-4 py-2">{{ __('Applications') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($programs as $program)
                                <tr>
                                    <td class="px-4 py-2">{{ $program->name }}</td>
                                    <td class="px-4 py-2">{{ $program->type->label() }}</td>
                                    <td class="px-4 py-2">{{ $program->application_deadline->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2">{{ $program->applications_count }}</td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('coordinator.programs.edit', $program) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
