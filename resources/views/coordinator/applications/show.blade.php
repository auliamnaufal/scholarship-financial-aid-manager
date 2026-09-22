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
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">{{ __('Student') }}</dt>
                        <dd>{{ $application->student->name }} ({{ $application->student->email }})</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Program') }}</dt>
                        <dd>{{ $application->program->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Semester') }}</dt>
                        <dd>{{ $application->semester }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">{{ __('Status') }}</dt>
                        <dd>
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full ring-1 ring-inset ring-black/5 {{ $application->status->badgeClasses() }}">
                                <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>{{ $application->status->label() }}
                            </span>
                        </dd>
                    </div>
                    @if ($application->student->studentProfile)
                        <div>
                            <dt class="text-slate-500">{{ __('GPA') }}</dt>
                            <dd>{{ $application->student->studentProfile->gpa }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">{{ __('Year Enrolled') }}</dt>
                            <dd>{{ $application->student->studentProfile->year_enrolled }}</dd>
                        </div>
                    @endif
                </dl>

                @can('decide', $application)
                    @php $budgetLeft = $application->program->remainingBudget(); @endphp

                    <div class="mt-6 border-t border-slate-200 pt-6">
                        <form method="POST" action="{{ route('coordinator.applications.approve', $application) }}" class="flex flex-wrap items-end gap-3">
                            @csrf
                            <div>
                                <x-input-label for="awarded_amount" :value="__('Amount to award')" />
                                <x-text-input id="awarded_amount" name="awarded_amount" type="number" step="0.01" min="0.01"
                                    max="{{ $budgetLeft }}" class="mt-1 block w-48" :value="old('awarded_amount')" required />
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ __('Budget left in this program:') }} {{ number_format($budgetLeft, 2) }}
                                </p>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg shadow-sm transition hover:bg-emerald-500 text-sm font-medium">{{ __('Approve') }}</button>
                        </form>

                        <x-input-error :messages="$errors->get('awarded_amount')" class="mt-2" />

                        <form method="POST" action="{{ route('coordinator.applications.reject', $application) }}" class="mt-4">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg shadow-sm transition hover:bg-red-500 text-sm font-medium">{{ __('Reject') }}</button>
                        </form>
                    </div>
                @elseif ($application->status->value === 'under_review' && $application->reviews->isEmpty())
                    <p class="text-sm text-slate-500 mt-6">{{ __('At least one review is required before this application can be approved or rejected.') }}</p>
                @endif
            </div>

            <div class="bg-white overflow-hidden shadow-soft ring-1 ring-slate-900/5 rounded-xl p-6">
                <h3 class="text-lg font-medium mb-4">{{ __('Reviews') }}</h3>
                @if ($application->reviews->isEmpty())
                    <p class="text-slate-500">{{ __('No reviews yet.') }}</p>
                @else
                    <div class="space-y-3">
                        @foreach ($application->reviews as $review)
                            <div class="rounded-lg border border-slate-200 p-3 text-sm">
                                <p class="font-medium">{{ $review->reviewer->name }} — {{ __('Score') }}: {{ $review->score }}</p>
                                @if ($review->comments)
                                    <p class="text-slate-600">{{ $review->comments }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <x-card>
                <x-requirement-checklist :application="$application" />
            </x-card>

            <div class="bg-white overflow-hidden shadow-soft ring-1 ring-slate-900/5 rounded-xl p-6">
                <h3 class="text-lg font-medium mb-4">{{ __('Disbursements') }}</h3>

                @if ($application->awarded_amount !== null)
                    <dl class="mb-4 grid grid-cols-3 gap-4 rounded-xl bg-slate-50 p-4 text-sm">
                        <div>
                            <dt class="text-slate-500">{{ __('Awarded') }}</dt>
                            <dd class="font-medium text-slate-900">{{ number_format((float) $application->awarded_amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">{{ __('Paid so far') }}</dt>
                            <dd class="font-medium text-slate-900">{{ number_format($application->disbursedTotal(), 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">{{ __('Still to pay') }}</dt>
                            <dd class="font-medium text-indigo-700">{{ number_format($application->remainingAward(), 2) }}</dd>
                        </div>
                    </dl>

                    @unless ($application->student->studentProfile?->hasBankAccount())
                        <p class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                            {{ __('This student has no bank account on file, so there is nowhere to send the money.') }}
                        </p>
                    @endunless
                @endif

                @if ($application->disbursements->isNotEmpty())
                    <div class="overflow-x-auto"><table class="data-table mb-4">
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

                @can('create', [\App\Models\Disbursement::class, $application])
                    <form method="POST" action="{{ route('coordinator.disbursements.store', $application) }}" class="grid grid-cols-2 gap-4">
                        @csrf
                        <div>
                            <x-input-label for="seq_no" :value="__('Sequence #')" />
                            <x-text-input id="seq_no" name="seq_no" type="number" min="1" class="mt-1 block w-full" :value="old('seq_no')" required />
                            <x-input-error :messages="$errors->get('seq_no')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="amount" :value="__('Amount')" />
                            <x-text-input id="amount" name="amount" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('amount')" required />
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="disbursement_date" :value="__('Date')" />
                            <x-text-input id="disbursement_date" name="disbursement_date" type="date" class="mt-1 block w-full" :value="old('disbursement_date')" required />
                            <x-input-error :messages="$errors->get('disbursement_date')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="semester" :value="__('Semester')" />
                            <x-text-input id="semester" name="semester" type="text" class="mt-1 block w-full" :value="old('semester', $application->semester)" required />
                            <x-input-error :messages="$errors->get('semester')" class="mt-2" />
                        </div>
                        <div class="col-span-2">
                            <x-primary-button>{{ __('Record Disbursement') }}</x-primary-button>
                        </div>
                    </form>
                @elseif ($application->status->value !== 'approved')
                    <p class="text-sm text-slate-500">{{ __('Disbursements can only be recorded once the application is approved.') }}</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
