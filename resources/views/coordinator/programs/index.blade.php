<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('Programs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ session('status') }}</div>
            @endif

            <x-card>
                <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                    <h3 class="text-lg font-medium">{{ __('Programs you manage') }}</h3>
                    <a href="{{ route('coordinator.programs.create') }}" class="rounded-xl btn-gradient px-4 py-2 text-sm font-medium text-white shadow-sm transition">
                        {{ __('New Program') }}
                    </a>
                </div>

                @if ($programs->isEmpty())
                    <p class="text-slate-500">{{ __('You do not manage any programs yet.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2">{{ __('Program') }}</th>
                                    <th class="px-3 py-2">{{ __('Deadline') }}</th>
                                    <th class="px-3 py-2">{{ __('Applications') }}</th>
                                    <th class="px-3 py-2">{{ __('Budget') }}</th>
                                    <th class="px-3 py-2">{{ __('Still to disburse') }}</th>
                                    <th class="px-3 py-2">{{ __('Requirements') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($programs as $program)
                                    @php $archived = $program->trashed(); @endphp
                                    <tr class="{{ $archived ? 'opacity-60' : '' }}">
                                        <td class="px-3 py-3">
                                            <span class="font-medium text-slate-900">{{ $program->name }}</span>
                                            @if ($archived)
                                                <span class="ml-2 inline-flex items-center rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-600">{{ __('Archived') }}</span>
                                            @endif
                                            <span class="block text-xs text-slate-500">{{ $program->type->label() }} · {{ $program->funding_source }}</span>
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap">{{ $program->application_deadline->format('d M Y') }}</td>
                                        <td class="px-3 py-3">{{ $program->applications_count }}</td>
                                        <td class="px-3 py-3 whitespace-nowrap">{{ number_format($program->budget) }}</td>
                                        <td class="px-3 py-3 whitespace-nowrap font-medium text-slate-900">{{ number_format($program->remainingBudget()) }}</td>
                                        <td class="px-3 py-3">{{ $program->requirements()->count() }}</td>
                                        <td class="px-3 py-3">
                                            <div class="flex justify-end items-center gap-3">
                                                @if ($archived)
                                                    <form method="POST" action="{{ route('coordinator.programs.restore', $program->id) }}">
                                                        @csrf
                                                        <button type="submit" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Restore') }}</button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('coordinator.programs.edit', $program) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                                    <form
                                                        method="POST"
                                                        action="{{ route('coordinator.programs.destroy', $program) }}"
                                                        onsubmit="return confirm('{{ __('Archive this program? It disappears from the public listing and takes no new applications. Existing applications are kept.') }}')"
                                                    >
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-sm font-medium text-red-600 hover:underline">{{ __('Archive') }}</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>
