@props(['layout' => 'hero'])

@php
    // Flat "misprint" illustrations: a pastel fill with two outlines nudged off
    // register, in the site's blue and amber.
    $shapes = [
        'cap' => '<path d="M32 14 58 26 32 38 6 26 32 14Z"/><path d="M18 33v10c0 4 6 7 14 7s14-3 14-7V33L32 40 18 33Z"/><path d="M54 28v14"/><rect x="51.5" y="42" width="5" height="6" rx="1.5"/>',
        'book' => '<path d="M8 17c8-2 17-1 24 4v33c-7-5-16-6-24-4V17Z"/><path d="M56 17c-8-2-17-1-24 4v33c7-5 16-6 24-4V17Z"/>',
        'money' => '<rect x="5" y="17" width="54" height="30" rx="4"/><path d="M22 26v12M22 26h5a3.5 3.5 0 0 1 0 7h-5M27 33l4.5 5M35 30v11"/><circle cx="38.3" cy="33.5" r="3.3"/><circle cx="12.5" cy="32" r="1.6"/><circle cx="51.5" cy="32" r="1.6"/>',
        'trophy' => '<path d="M20 12h24v12c0 8-5 14-12 14s-12-6-12-14V12Z"/><path d="M20 16h-8c0 8 3 12 9 13M44 16h8c0 8-3 12-9 13"/><path d="M32 38v8"/><path d="M22 52h20v-6H22v6Z"/>',
    ];

    // Four icons per layout, placed by hand.
    // [icon, position, size px, tilt deg, delay s, duration s, visibility classes]
    $chips = $layout === 'hero'
        ? [
            ['cap', 'left: 47%; top: 8%', 72, 0, 0, 6, 'hidden lg:flex'],
            ['trophy', 'right: 3%; top: 6%', 56, 0, 1.2, 9, 'flex'],
            ['money', 'right: 2%; bottom: 12%', 72, 0, 0.6, 6, 'hidden sm:flex'],
            ['book', 'left: 44%; bottom: 8%', 64, 0, 2, 9, 'hidden lg:flex'],
        ]
        : [
            // Login and register panel: scattered so no two share a row or a
            // column, and none covers the text.
            ['cap', 'left: 70%; top: 9%', 72, -10, 0, 7, 'flex'],
            ['trophy', 'left: 13%; top: 26%', 54, 12, -1.2, 8, 'flex'],
            ['money', 'left: 81%; top: 47%', 62, 8, -0.6, 8.5, 'flex'],
            ['book', 'left: 50%; top: 77%', 66, -7, -1.8, 7.5, 'flex'],
        ];
@endphp

<div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
    @foreach ($chips as [$icon, $position, $size, $tilt, $delay, $duration, $visibility])
        <span class="absolute {{ $visibility }} animate-float items-center justify-center rounded-2xl bg-slate-100 shadow-xl shadow-slate-950/30"
              style="{{ $position }}; width: {{ $size }}px; height: {{ $size }}px; --r: {{ $tilt }}deg; transform: rotate({{ $tilt }}deg); animation-delay: {{ $delay }}s; animation-duration: {{ $duration }}s">
            <svg class="h-full w-full p-1.5" viewBox="0 0 64 64" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <g fill="#c7d2fe" stroke="none">{!! $shapes[$icon] !!}</g>
                <g stroke="#f59e0b" stroke-width="1.6" transform="translate(-1.6 -1.6)">{!! $shapes[$icon] !!}</g>
                <g stroke="#2a4cc2" stroke-width="1.6" transform="translate(1 1)">{!! $shapes[$icon] !!}</g>
                <path d="m9 9 1.6 3.3 3.6.5-2.6 2.5.6 3.6L9 17.2l-3.2 1.7.6-3.6L3.8 12.8l3.6-.5L9 9Z" fill="#fbbf24" stroke="none" transform="translate(2 -1)"/>
            </svg>
        </span>
    @endforeach
</div>
