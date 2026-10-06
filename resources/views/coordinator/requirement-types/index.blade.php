<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-slate-900 tracking-tight">
            {{ __('Requirement Types') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ session('status') }}</div>
            @endif

            <x-input-error :messages="$errors->get('requirement_type')" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4" />

            <x-card>
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg font-medium">{{ __('What a scholarship can ask for') }}</h3>
                        <p class="text-sm text-slate-500">{{ __('Tick these on each scholarship to build its application form.') }}</p>
                    </div>
                    <a href="{{ route('coordinator.requirement-types.create') }}" class="px-4 py-2 bg-indigo-700 text-white rounded-xl shadow-sm transition hover:bg-indigo-600 text-sm font-medium">
                        {{ __('New Requirement') }}
                    </a>
                </div>

                @if ($types->isEmpty())
                    <p class="text-slate-500">{{ __('No requirement types yet.') }}</p>
                @else
                    <div class="overflow-x-auto"><table class="data-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">{{ __('Name') }}</th>
                                <th class="px-4 py-2">{{ __('How it is answered') }}</th>
                                <th class="px-4 py-2">{{ __('Used by') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($types as $type)
                                @php $inUse = $type->program_requirements_count > 0 || $type->application_documents_count > 0; @endphp
                                <tr>
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-slate-900">{{ $type->name }}</span>
                                        @if ($type->description)
                                            <span class="block text-xs text-slate-500">{{ $type->description }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $type->isFile() ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-800' }}">{{ $type->kind->label() }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">
                                        {{ $type->program_requirements_count }} {{ __('scholarships') }}
                                        <span class="block text-xs text-slate-500">{{ $type->application_documents_count }} {{ __('submitted answers') }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('coordinator.requirement-types.edit', $type) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                            @if ($inUse)
                                                <span class="text-sm text-slate-400" title="{{ __('Already in use, so it cannot be deleted.') }}">{{ __('Delete') }}</span>
                                            @else
                                                <form method="POST" action="{{ route('coordinator.requirement-types.destroy', $type) }}"
                                                      data-confirm-title="{{ __('Delete this requirement?') }}"
                                                      data-confirm-message="{{ __('":name" will be removed from the list. Nothing uses it yet.', ['name' => $type->name]) }}"
                                                      data-confirm-label="{{ __('Yes, delete') }}"
                                                      data-confirm-tone="danger">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-sm font-medium text-red-600 hover:underline">{{ __('Delete') }}</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>
