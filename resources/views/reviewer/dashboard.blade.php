<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('Reviewer Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-soft ring-1 ring-slate-900/5 rounded-xl p-6">
                <h3 class="text-lg font-medium mb-4">{{ __('Open Applications (claim to review)') }}</h3>
                @if ($claimable->isEmpty())
                    <p class="text-slate-500">{{ __('No applications waiting to be claimed.') }}</p>
                @else
                    <div class="overflow-x-auto"><table class="data-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">{{ __('Student') }}</th>
                                <th class="px-4 py-2">{{ __('Program') }}</th>
                                <th class="px-4 py-2">{{ __('Semester') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($claimable as $application)
                                <tr>
                                    <td class="px-4 py-2">{{ $application->student->name }}</td>
                                    <td class="px-4 py-2">{{ $application->program->name }}</td>
                                    <td class="px-4 py-2">{{ $application->semester }}</td>
                                    <td class="px-4 py-2">
                                        <form method="POST" action="{{ route('reviewer.applications.claim', $application) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl shadow-sm transition hover:from-indigo-500 hover:to-violet-500 text-xs font-medium">
                                                {{ __('Claim') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>

            <div class="bg-white overflow-hidden shadow-soft ring-1 ring-slate-900/5 rounded-xl p-6">
                <h3 class="text-lg font-medium mb-4">{{ __('Awaiting Your Review') }}</h3>
                @if ($awaitingMyReview->isEmpty())
                    <p class="text-slate-500">{{ __('Nothing awaiting your review.') }}</p>
                @else
                    <div class="overflow-x-auto"><table class="data-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">{{ __('Student') }}</th>
                                <th class="px-4 py-2">{{ __('Program') }}</th>
                                <th class="px-4 py-2">{{ __('Semester') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
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
                    </table></div>
                @endif
            </div>

            <div class="bg-white overflow-hidden shadow-soft ring-1 ring-slate-900/5 rounded-xl p-6">
                <h3 class="text-lg font-medium mb-4">{{ __('All Submissions') }}</h3>
                @if ($allSubmissions->isEmpty())
                    <p class="text-slate-500">{{ __('No applications have been submitted yet.') }}</p>
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
                            @foreach ($allSubmissions as $application)
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
                                        <a href="{{ route('reviewer.applications.show', $application) }}" class="text-indigo-600 hover:underline">
                                            {{ __('View') }}
                                        </a>
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
