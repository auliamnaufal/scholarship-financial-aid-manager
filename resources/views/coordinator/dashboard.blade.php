<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('My Programs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ session('status') }}</div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-stat-card :label="__('Scholarships managed')" :value="$stats['programs']" tone="indigo">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 2 8l10 5 10-5-10-5ZM6 10.5V16c0 1.2 2.7 3 6 3s6-1.8 6-3v-5.5" /></svg>
                </x-stat-card>
                <x-stat-card :label="__('Awaiting your decision')" :value="$stats['awaiting']" :hint="__('Applications under review')" tone="amber">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" /></svg>
                </x-stat-card>
                <x-stat-card :label="__('Recipients approved')" :value="$stats['recipients']" tone="emerald">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="m8.5 12.5 2.5 2.5 4.5-5" /></svg>
                </x-stat-card>
                <x-stat-card :label="__('Funds disbursed')" :value="\App\Support\Money::compact($stats['disbursed'])" tone="sky">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><rect x="3" y="6" width="18" height="12" rx="2" /><circle cx="12" cy="12" r="2.5" /></svg>
                </x-stat-card>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-soft ring-1 ring-slate-900/5 rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">{{ __('Programs You Manage') }}</h3>
                    <a href="{{ route('coordinator.programs.create') }}" class="px-4 py-2 bg-indigo-700 text-white rounded-xl shadow-sm transition hover:bg-indigo-600 text-sm font-medium">
                        {{ __('New Program') }}
                    </a>
                </div>

                @if ($programs->isEmpty())
                    <p class="text-slate-500">{{ __("You haven't created any programs yet.") }}</p>
                @else
                    <div class="overflow-x-auto"><table class="data-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">{{ __('Name') }}</th>
                                <th class="px-4 py-2">{{ __('Type') }}</th>
                                <th class="px-4 py-2">{{ __('Deadline') }}</th>
                                <th class="px-4 py-2">{{ __('Applications') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($programs as $program)
                                <tr>
                                    <td class="px-4 py-2">{{ $program->name }}</td>
                                    <td class="px-4 py-2">{{ $program->type->label() }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $program->application_deadline->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-2">{{ $program->applications_count }}</td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('coordinator.programs.edit', $program) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
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
