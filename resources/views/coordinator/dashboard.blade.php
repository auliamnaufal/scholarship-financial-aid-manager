<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('My Programs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-soft ring-1 ring-slate-900/5 rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">{{ __('Programs You Manage') }}</h3>
                    <a href="{{ route('coordinator.programs.create') }}" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl shadow-sm transition hover:from-indigo-500 hover:to-violet-500 text-sm font-medium">
                        {{ __('New Program') }}
                    </a>
                </div>

                @if ($programs->isEmpty())
                    <p class="text-slate-500">{{ __("You haven't created any programs yet.") }}</p>
                @else
                    <div class="overflow-x-auto"><table class="data-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">{{ __('Name') }}</th>
                                <th class="px-4 py-2">{{ __('Type') }}</th>
                                <th class="px-4 py-2">{{ __('Deadline') }}</th>
                                <th class="px-4 py-2">{{ __('Applications') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
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
                    </table></div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
