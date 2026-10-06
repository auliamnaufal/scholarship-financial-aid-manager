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

            <div class="grid gap-4 sm:grid-cols-3">
                <x-stat-card :label="__('Waiting to be claimed')" :value="$claimable->count()" tone="indigo">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 13h4l2 3h4l2-3h4M5 5h14l2 8v6H3v-6l2-8Z" /></svg>
                </x-stat-card>
                <x-stat-card :label="__('Awaiting your review')" :value="$awaitingMyReview->count()" tone="amber">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5Z" /></svg>
                </x-stat-card>
                <x-stat-card :label="__('Reviews you have written')" :value="$reviewedByMe" tone="emerald">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="m8.5 12.5 2.5 2.5 4.5-5" /></svg>
                </x-stat-card>
            </div>

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
                                    <td class="px-4 py-2">{{ $application->semester_label }}</td>
                                    <td class="px-4 py-2">
                                        <form method="POST" action="{{ route('reviewer.applications.claim', $application) }}"
                                        data-confirm-title="{{ __('Claim this application?') }}"
                                        data-confirm-message="{{ __('It will move to under review, and you can then give it a score.') }}"
                                        data-confirm-label="{{ __('Yes, claim') }}"
                                        data-confirm-tone="primary">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-indigo-700 text-white rounded-xl shadow-sm transition hover:bg-indigo-600 text-xs font-medium">
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
                                    <td class="px-4 py-2">{{ $application->semester_label }}</td>
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
                                    <td class="px-4 py-2">{{ $application->semester_label }}</td>
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

                    <div class="mt-4">{{ $allSubmissions->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
