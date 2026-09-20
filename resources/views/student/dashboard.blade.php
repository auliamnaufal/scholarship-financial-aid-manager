<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Applications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm ring-1 ring-slate-900/5 rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">{{ __('Your Applications') }}</h3>
                    <a href="{{ route('home') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">
                        {{ __('Browse Programs') }}
                    </a>
                </div>

                @if ($applications->isEmpty())
                    <p class="text-gray-500">{{ __("You haven't applied to any programs yet.") }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                                    <th class="px-4 py-2">{{ __('Program') }}</th>
                                    <th class="px-4 py-2">{{ __('Semester') }}</th>
                                    <th class="px-4 py-2">{{ __('Submitted') }}</th>
                                    <th class="px-4 py-2">{{ __('Status') }}</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($applications as $application)
                                    <tr>
                                        <td class="px-4 py-2">{{ $application->program->name }}</td>
                                        <td class="px-4 py-2">{{ $application->semester }}</td>
                                        <td class="px-4 py-2">{{ $application->submission_date->format('Y-m-d') }}</td>
                                        <td class="px-4 py-2">
                                            <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $application->status->badgeClasses() }}">
                                                {{ $application->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <a href="{{ route('student.applications.show', $application) }}" class="text-indigo-600 hover:underline">
                                                {{ __('View') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
