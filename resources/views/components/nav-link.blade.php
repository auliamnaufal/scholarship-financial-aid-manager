@props(['active' => false])

@php
$classes = ($active ?? false)
    ? 'flex items-center gap-3 rounded-xl bg-white/10 px-3 py-2.5 text-sm font-medium text-white transition'
    : 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
