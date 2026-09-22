@php
    $myReview = $application->reviews->firstWhere('reviewer_id', auth()->id());
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('Application') }} #{{ $application->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-soft ring-1 ring-slate-900/5 rounded-xl p-6">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">{{ __('Student') }}</dt>
                        <dd>{{ $application->student->name }}</dd>
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
            </div>

            <x-card>
                <x-requirement-checklist :application="$application" />
            </x-card>

            <div class="bg-white overflow-hidden shadow-soft ring-1 ring-slate-900/5 rounded-xl p-6">
                <h3 class="text-lg font-medium mb-4">{{ __('Your Review') }}</h3>

                @if ($myReview && $myReview->id && auth()->user()->can('update', $myReview))
                    {{-- Revisable while the application is still under review; once the
                         coordinator decides, the score they decided on is locked. --}}
                    <form method="POST" action="{{ route('reviewer.reviews.update', $myReview) }}">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="score" :value="__('Score (0-100)')" />
                            <x-text-input id="score" name="score" type="number" min="0" max="100" class="mt-1 block w-full" :value="old('score', $myReview->score)" required />
                            <x-input-error :messages="$errors->get('score')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="comments" :value="__('Comments')" />
                            <textarea id="comments" name="comments" rows="4" class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">{{ old('comments', $myReview->comments) }}</textarea>
                            <x-input-error :messages="$errors->get('comments')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-3 mt-6">
                            <p class="mr-auto text-sm text-slate-500">{{ __('You can revise this until the coordinator decides.') }}</p>
                            <x-primary-button>{{ __('Update Review') }}</x-primary-button>
                        </div>
                    </form>
                @elseif ($myReview)
                    <p class="text-slate-700">{{ __('Score') }}: {{ $myReview->score }}</p>
                    @if ($myReview->comments)
                        <p class="text-slate-600 mt-1">{{ $myReview->comments }}</p>
                    @endif
                    <p class="text-sm text-slate-500 mt-2">{{ __('The coordinator has decided, so this review is now locked.') }}</p>
                @elseif ($application->status->value !== 'under_review')
                    <p class="text-slate-500">{{ __('This application is not currently under review.') }}</p>
                @else
                    <form method="POST" action="{{ route('reviewer.reviews.store', $application) }}">
                        @csrf

                        <div>
                            <x-input-label for="score" :value="__('Score (0-100)')" />
                            <x-text-input id="score" name="score" type="number" min="0" max="100" class="mt-1 block w-full" :value="old('score')" required />
                            <x-input-error :messages="$errors->get('score')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="comments" :value="__('Comments')" />
                            <textarea id="comments" name="comments" rows="4" class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">{{ old('comments') }}</textarea>
                            <x-input-error :messages="$errors->get('comments')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>{{ __('Submit Review') }}</x-primary-button>
                        </div>
                    </form>
                @endif
            </div>

            @if ($application->reviews->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-soft ring-1 ring-slate-900/5 rounded-xl p-6">
                    <h3 class="text-lg font-medium mb-4">{{ __('All Reviews') }}</h3>
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
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
