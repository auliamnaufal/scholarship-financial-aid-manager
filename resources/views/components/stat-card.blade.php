@props(['label', 'value', 'hint' => null, 'tone' => 'indigo'])

@php
    $tones = [
        'indigo' => 'bg-indigo-50 text-indigo-700',
        'amber' => 'bg-amber-50 text-amber-700',
        'emerald' => 'bg-emerald-50 text-emerald-700',
        'sky' => 'bg-sky-50 text-sky-700',
    ];
@endphp

{{-- One headline number at the top of a dashboard. The icon goes in the slot. --}}
<div class="rounded-xl bg-white p-5 shadow-soft ring-1 ring-slate-900/5">
    <div class="flex items-center gap-4">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] ?? $tones['indigo'] }}">
            {{ $slot }}
        </span>
        <div class="min-w-0">
            <p class="text-sm text-slate-500">{{ $label }}</p>
            <p class="font-display text-2xl font-bold tracking-tight text-slate-900">{{ $value }}</p>
            @if ($hint)
                <p class="text-xs text-slate-500">{{ $hint }}</p>
            @endif
        </div>
    </div>
</div>
