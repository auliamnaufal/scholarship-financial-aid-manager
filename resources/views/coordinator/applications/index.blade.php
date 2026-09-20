<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Applications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm ring-1 ring-slate-900/5 rounded-xl p-6">
                <form method="GET" action="{{ route('coordinator.applications.index') }}" class="mb-4 flex items-center gap-3">
                    <label for="status" class="text-sm text-gray-600">{{ __('Filter by status') }}</label>
                    <select id="status" name="status" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </form>

                @if ($applications->isEmpty())
                    <p class="text-gray-500">{{ __('No applications found.') }}</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                                <th class="px-4 py-2">{{ __('Student') }}</th>
                                <th class="px-4 py-2">{{ __('Program') }}</th>
                                <th class="px-4 py-2">{{ __('Semester') }}</th>
                                <th class="px-4 py-2">{{ __('Status') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($applications as $application)
                                <tr>
                                    <td class="px-4 py-2">{{ $application->student->name }}</td>
                                    <td class="px-4 py-2">{{ $application->program->name }}</td>
                                    <td class="px-4 py-2">{{ $application->semester }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $application->status->badgeClasses() }}">
                                            {{ $application->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('coordinator.applications.show', $application) }}" class="text-indigo-600 hover:underline">{{ __('View') }}</a>
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
