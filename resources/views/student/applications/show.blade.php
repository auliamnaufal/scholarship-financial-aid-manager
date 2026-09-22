<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('Application') }} #{{ $application->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-soft ring-1 ring-slate-900/5 rounded-xl p-6">
                <h3 class="text-lg font-medium mb-4">{{ $application->program->name }}</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">{{ __('Semester') }}</dt>
                        <dd>{{ $application->semester }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Submitted') }}</dt>
                        <dd>{{ $application->submission_date->format('Y-m-d') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Status') }}</dt>
                        <dd>
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full ring-1 ring-inset ring-black/5 {{ $application->status->badgeClasses() }}">
                                <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>{{ $application->status->label() }}
                            </span>
                            @if ($application->cancelled_at)
                                <span class="block mt-1 text-xs text-slate-500">{{ __('Withdrawn on') }} {{ $application->cancelled_at->format('d M Y') }}</span>
                            @endif
                        </dd>
                    </div>
                    @if ($application->awarded_amount !== null)
                        <div>
                            <dt class="text-slate-500">{{ __('Awarded') }}</dt>
                            <dd>{{ number_format((float) $application->awarded_amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">{{ __('Still to be paid') }}</dt>
                            <dd class="font-medium text-slate-900">{{ number_format($application->remainingAward(), 2) }}</dd>
                        </div>
                    @endif
                </dl>

                @can('cancel', $application)
                    <form
                        method="POST"
                        action="{{ route('student.applications.cancel', $application) }}"
                        class="mt-6 border-t border-slate-200 pt-4"
                        onsubmit="return confirm('{{ __('Withdraw this application? You cannot undo this, but you may apply again while the scholarship is still open.') }}')"
                    >
                        @csrf
                        <button type="submit" class="text-sm font-medium text-red-600 hover:underline">
                            {{ __('Withdraw this application') }}
                        </button>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ __('You can withdraw until the coordinator makes a decision.') }}
                        </p>
                    </form>
                @endcan
            </div>

            <x-card>
                <x-requirement-checklist :application="$application" />
            </x-card>

            <div class="bg-white overflow-hidden shadow-soft ring-1 ring-slate-900/5 rounded-xl p-6">
                <h3 class="text-lg font-medium mb-4">{{ __('Review Feedback') }}</h3>
                @if (! $application->reviewsVisibleToStudent())
                    <p class="text-slate-500">{{ __('Review comments will appear here once a decision has been made.') }}</p>
                @elseif ($application->reviews->isEmpty())
                    <p class="text-slate-500">{{ __('No reviews were recorded for this application.') }}</p>
                @else
                    <div class="space-y-4">
                        @foreach ($application->reviews as $review)
                            <div class="rounded-lg border border-slate-200 p-4">
                                <p class="font-medium">{{ __('Score') }}: {{ $review->score }}</p>
                                @if ($review->comments)
                                    <p class="text-slate-600 mt-1">{{ $review->comments }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white overflow-hidden shadow-soft ring-1 ring-slate-900/5 rounded-xl p-6">
                <h3 class="text-lg font-medium mb-4">{{ __('Disbursement History') }}</h3>
                @if ($application->disbursements->isEmpty())
                    <p class="text-slate-500">{{ __('No disbursements recorded yet.') }}</p>
                @else
                    <div class="overflow-x-auto"><table class="data-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">#</th>
                                <th class="px-4 py-2">{{ __('Amount') }}</th>
                                <th class="px-4 py-2">{{ __('Date') }}</th>
                                <th class="px-4 py-2">{{ __('Semester') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($application->disbursements->sortBy('seq_no') as $disbursement)
                                <tr>
                                    <td class="px-4 py-2">{{ $disbursement->seq_no }}</td>
                                    <td class="px-4 py-2">{{ number_format($disbursement->amount, 2) }}</td>
                                    <td class="px-4 py-2">{{ $disbursement->disbursement_date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2">{{ $disbursement->semester }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
