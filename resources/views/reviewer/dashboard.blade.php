<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reviewer Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm ring-1 ring-slate-900/5 rounded-xl p-6">
                <h3 class="text-lg font-medium mb-4">{{ __('Open Applications (claim to review)') }}</h3>
                @if ($claimable->isEmpty())
                    <p class="text-gray-500">{{ __('No applications waiting to be claimed.') }}</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                                <th class="px-4 py-2">{{ __('Student') }}</th>
                                <th class="px-4 py-2">{{ __('Program') }}</th>
                                <th class="px-4 py-2">{{ __('Semester') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($claimable as $application)
                                <tr>
                                    <td class="px-4 py-2">{{ $application->student->name }}</td>
                                    <td class="px-4 py-2">{{ $application->program->name }}</td>
                                    <td class="px-4 py-2">{{ $application->semester }}</td>
                                    <td class="px-4 py-2">
                                        <form method="POST" action="{{ route('reviewer.applications.claim', $application) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs">
                                                {{ __('Claim') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="bg-white overflow-hidden shadow-sm ring-1 ring-slate-900/5 rounded-xl p-6">
                <h3 class="text-lg font-medium mb-4">{{ __('Awaiting Your Review') }}</h3>
                @if ($awaitingMyReview->isEmpty())
                    <p class="text-gray-500">{{ __('Nothing awaiting your review.') }}</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                                <th class="px-4 py-2">{{ __('Student') }}</th>
                                <th class="px-4 py-2">{{ __('Program') }}</th>
                                <th class="px-4 py-2">{{ __('Semester') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($awaitingMyReview as $application)
                                <tr>
                                    <td class="px-4 py-2">{{ $application->student->name }}</td>
                                    <td class="px-4 py-2">{{ $application->program->name }}</td>
                                    <td class="px-4 py-2">{{ $application->semester }}</td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('reviewer.applications.show', $application) }}" class="text-indigo-600 hover:underline">
                                            {{ __('Review') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="bg-white overflow-hidden shadow-sm ring-1 ring-slate-900/5 rounded-xl p-6">
                <h3 class="text-lg font-medium mb-4">{{ __('All Submissions') }}</h3>
                @if ($allSubmissions->isEmpty())
                    <p class="text-gray-500">{{ __('No applications have been submitted yet.') }}</p>
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
                            @foreach ($allSubmissions as $application)
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
                                        <a href="{{ route('reviewer.applications.show', $application) }}" class="text-indigo-600 hover:underline">
                                            {{ __('View') }}
                                        </a>
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
