<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Application') }} #{{ $application->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm ring-1 ring-slate-900/5 rounded-xl p-6">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">{{ __('Student') }}</dt>
                        <dd>{{ $application->student->name }} ({{ $application->student->email }})</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Program') }}</dt>
                        <dd>{{ $application->program->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Semester') }}</dt>
                        <dd>{{ $application->semester }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Status') }}</dt>
                        <dd>
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $application->status->badgeClasses() }}">
                                {{ $application->status->label() }}
                            </span>
                        </dd>
                    </div>
                    @if ($application->student->studentProfile)
                        <div>
                            <dt class="text-gray-500">{{ __('GPA') }}</dt>
                            <dd>{{ $application->student->studentProfile->gpa }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ __('Year Enrolled') }}</dt>
                            <dd>{{ $application->student->studentProfile->year_enrolled }}</dd>
                        </div>
                    @endif
                </dl>

                @can('decide', $application)
                    <div class="flex gap-3 mt-6">
                        <form method="POST" action="{{ route('coordinator.applications.approve', $application) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">{{ __('Approve') }}</button>
                        </form>
                        <form method="POST" action="{{ route('coordinator.applications.reject', $application) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">{{ __('Reject') }}</button>
                        </form>
                    </div>
                @elseif ($application->status->value === 'under_review' && $application->reviews->isEmpty())
                    <p class="text-sm text-gray-500 mt-6">{{ __('At least one review is required before this application can be approved or rejected.') }}</p>
                @endif
            </div>

            <div class="bg-white overflow-hidden shadow-sm ring-1 ring-slate-900/5 rounded-xl p-6">
                <h3 class="text-lg font-medium mb-4">{{ __('Reviews') }}</h3>
                @if ($application->reviews->isEmpty())
                    <p class="text-gray-500">{{ __('No reviews yet.') }}</p>
                @else
                    <div class="space-y-3">
                        @foreach ($application->reviews as $review)
                            <div class="border rounded-md p-3 text-sm">
                                <p class="font-medium">{{ $review->reviewer->name }} — {{ __('Score') }}: {{ $review->score }}</p>
                                @if ($review->comments)
                                    <p class="text-gray-600">{{ $review->comments }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white overflow-hidden shadow-sm ring-1 ring-slate-900/5 rounded-xl p-6">
                <h3 class="text-lg font-medium mb-4">{{ __('Disbursements') }}</h3>

                @if ($application->disbursements->isNotEmpty())
                    <table class="min-w-full divide-y divide-gray-200 text-sm mb-4">
                        <thead>
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                                <th class="px-4 py-2">#</th>
                                <th class="px-4 py-2">{{ __('Amount') }}</th>
                                <th class="px-4 py-2">{{ __('Date') }}</th>
                                <th class="px-4 py-2">{{ __('Semester') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($application->disbursements->sortBy('seq_no') as $disbursement)
                                <tr>
                                    <td class="px-4 py-2">{{ $disbursement->seq_no }}</td>
                                    <td class="px-4 py-2">{{ number_format($disbursement->amount, 2) }}</td>
                                    <td class="px-4 py-2">{{ $disbursement->disbursement_date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2">{{ $disbursement->semester }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
                    <p class="text-sm text-gray-500">{{ __('Disbursements can only be recorded once the application is approved.') }}</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
