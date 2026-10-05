@props(['active' => false])

@php
$classes = ($active ?? false)
    ? 'group relative flex items-center gap-3 rounded-xl bg-white/10 px-3 py-2.5 text-sm font-medium text-white transition-all duration-200 before:absolute before:bottom-2 before:left-0 before:top-2 before:w-1 before:rounded-full before:bg-amber-400 [&>svg]:text-amber-300'
    : 'group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-300 transition-all duration-200 hover:translate-x-1 hover:bg-white/5 hover:text-white before:absolute before:left-0 before:top-1/2 before:h-0 before:w-1 before:-translate-y-1/2 before:rounded-full before:bg-amber-400 before:transition-all hover:before:h-5 [&>svg]:transition-transform group-hover:[&>svg]:scale-110';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
