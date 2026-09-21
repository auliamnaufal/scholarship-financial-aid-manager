<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('Applications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-soft ring-1 ring-slate-900/5 rounded-xl p-6">
                <form method="GET" action="{{ route('coordinator.applications.index') }}" class="mb-4 flex items-center gap-3">
                    <label for="status" class="text-sm text-slate-600">{{ __('Filter by status') }}</label>
                    <select id="status" name="status" class="border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm" onchange="this.form.submit()">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </form>

                @if ($applications->isEmpty())
                    <p class="text-slate-500">{{ __('No applications found.') }}</p>
                @else
                    <div class="overflow-x-auto"><table class="data-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">{{ __('Student') }}</th>
                                <th class="px-4 py-2">{{ __('Program') }}</th>
                                <th class="px-4 py-2">{{ __('Semester') }}</th>
                                <th class="px-4 py-2">{{ __('Status') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($applications as $application)
                                <tr>
                                    <td class="px-4 py-2">{{ $application->student->name }}</td>
                                    <td class="px-4 py-2">{{ $application->program->name }}</td>
                                    <td class="px-4 py-2">{{ $application->semester }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full ring-1 ring-inset ring-black/5 {{ $application->status->badgeClasses() }}">
                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>{{ $application->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('coordinator.applications.show', $application) }}" class="text-indigo-600 hover:underline">{{ __('View') }}</a>
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
