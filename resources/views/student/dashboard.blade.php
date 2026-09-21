<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('My Applications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-soft ring-1 ring-slate-900/5 rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">{{ __('Your Applications') }}</h3>
                    <a href="{{ route('home') }}" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl shadow-sm transition hover:from-indigo-500 hover:to-violet-500 text-sm font-medium">
                        {{ __('Browse Programs') }}
                    </a>
                </div>

                @if ($applications->isEmpty())
                    <p class="text-slate-500">{{ __("You haven't applied to any programs yet.") }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2">{{ __('Program') }}</th>
                                    <th class="px-4 py-2">{{ __('Semester') }}</th>
                                    <th class="px-4 py-2">{{ __('Submitted') }}</th>
                                    <th class="px-4 py-2">{{ __('Status') }}</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($applications as $application)
                                    <tr>
                                        <td class="px-4 py-2">{{ $application->program->name }}</td>
                                        <td class="px-4 py-2">{{ $application->semester }}</td>
                                        <td class="px-4 py-2">{{ $application->submission_date->format('Y-m-d') }}</td>
                                        <td class="px-4 py-2">
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full ring-1 ring-inset ring-black/5 {{ $application->status->badgeClasses() }}">
                                                <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>{{ $application->status->label() }}
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
