@props([
    /** The application whose submitted documents are being listed. */
    'application',
    /** Show the download links and full essay text. False hides the content. */
    'readable' => true,
])

@php
    $requirements = $application->program->requirements
        ->sortBy(fn ($requirement) => [$requirement->is_required ? 0 : 1, $requirement->requirementType->name]);

    $supplied = $application->documents->keyBy('requirement_type_id');
@endphp

<div {{ $attributes }}>
    <h3 class="text-lg font-medium text-slate-900">{{ __('Supporting documents') }}</h3>

    @if ($requirements->isEmpty())
        <p class="mt-2 text-sm text-slate-500">{{ __('This scholarship asks for no supporting documents.') }}</p>
    @else
        <ul class="mt-3 divide-y divide-slate-100 border-y border-slate-100">
            @foreach ($requirements as $requirement)
                @php
                    $type = $requirement->requirementType;
                    $document = $supplied->get($type->id);
                    $filled = $document?->isFilled() ?? false;
                @endphp

                <li class="py-3">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <span class="font-medium text-slate-900">{{ $type->name }}</span>
                            @unless ($requirement->is_required)
                                <span class="ml-1 text-xs text-slate-500">({{ __('optional') }})</span>
                            @endunless

                            @if ($filled && $document->file_path && $readable)
                                <a href="{{ route('documents.show', $document) }}" class="ml-2 text-sm font-medium text-indigo-600 hover:underline">
                                    {{ $document->original_name ?? __('Download') }}
                                </a>
                                @if ($document->readableSize())
                                    <span class="text-xs text-slate-400">{{ $document->readableSize() }}</span>
                                @endif
                            @elseif ($filled && $document->original_name)
                                {{-- Seeded rows carry the metadata without a stored file. --}}
                                <span class="ml-2 text-sm text-slate-500">{{ $document->original_name }}</span>
                            @endif
                        </div>

                        @if ($filled)
                            <span class="shrink-0 inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">{{ __('Supplied') }}</span>
                        @elseif ($requirement->is_required)
                            <span class="shrink-0 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">{{ __('Missing') }}</span>
                        @else
                            <span class="shrink-0 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ __('Not supplied') }}</span>
                        @endif
                    </div>

                    @if ($filled && $document->body && $readable)
                        <p class="mt-2 whitespace-pre-line rounded-lg bg-slate-50 p-3 text-sm leading-relaxed text-slate-700">{{ $document->body }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
