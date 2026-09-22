<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('Students') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ session('status') }}</div>
            @endif

            <x-input-error :messages="$errors->get('student')" class="mb-4" />

            <x-card>
                <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                    <h3 class="text-lg font-medium">{{ __('All students') }}</h3>
                    <a href="{{ route('coordinator.students.create') }}" class="rounded-xl btn-gradient px-4 py-2 text-sm font-medium text-white shadow-sm transition">
                        {{ __('New Student') }}
                    </a>
                </div>

                @if ($students->isEmpty())
                    <p class="text-slate-500">{{ __('No student accounts yet.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2">{{ __('Student') }}</th>
                                    <th class="px-3 py-2">{{ __('Programme') }}</th>
                                    <th class="px-3 py-2">{{ __('GPA') }}</th>
                                    <th class="px-3 py-2">{{ __('Applications') }}</th>
                                    <th class="px-3 py-2">{{ __('Biodata') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $student)
                                    @php
                                        $archived = $student->trashed();
                                        $profile = $student->studentProfile;
                                        $missing = $profile?->missingFields() ?? [];
                                    @endphp
                                    <tr class="{{ $archived ? 'opacity-60' : '' }}">
                                        <td class="px-3 py-3">
                                            <span class="font-medium text-slate-900">{{ $student->name }}</span>
                                            @if ($archived)
                                                <span class="ml-2 inline-flex items-center rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-600">{{ __('Archived') }}</span>
                                            @endif
                                            <span class="block text-xs text-slate-500">{{ $student->email }}{{ $profile?->nim ? ' · '.$profile->nim : '' }}</span>
                                        </td>
                                        <td class="px-3 py-3">
                                            {{ $profile?->study_program ?? '—' }}
                                            <span class="block text-xs text-slate-500">{{ $profile?->faculty }}</span>
                                        </td>
                                        <td class="px-3 py-3">{{ $profile?->gpa ?? '—' }}</td>
                                        <td class="px-3 py-3">{{ $student->applications_count }}</td>
                                        <td class="px-3 py-3">
                                            @if (! $profile)
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">{{ __('No profile') }}</span>
                                            @elseif ($missing)
                                                <span
                                                    class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800"
                                                    title="{{ implode(', ', $missing) }}"
                                                >
                                                    {{ trans_choice('1 field missing|:count fields missing', count($missing), ['count' => count($missing)]) }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">{{ __('Complete') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="flex justify-end items-center gap-3">
                                                @if ($archived)
                                                    <form method="POST" action="{{ route('coordinator.students.restore', $student->id) }}">
                                                        @csrf
                                                        <button type="submit" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Restore') }}</button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('coordinator.students.edit', $student) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                                    <form
                                                        method="POST"
                                                        action="{{ route('coordinator.students.destroy', $student) }}"
                                                        onsubmit="return confirm('{{ __('Archive this student? They can no longer sign in. Their applications, reviews and payment records are kept.') }}')"
                                                    >
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-sm font-medium text-red-600 hover:underline">{{ __('Archive') }}</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>
