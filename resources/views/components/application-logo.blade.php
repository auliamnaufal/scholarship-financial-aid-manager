@php $uid = 'ef'.substr(md5(uniqid('', true)), 0, 8); @endphp
<svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="{{ config('app.name') }}" {{ $attributes->merge(['class' => 'shrink-0']) }}>
    <defs>
        <linearGradient id="{{ $uid }}-top" x1="2" y1="6" x2="46" y2="28" gradientUnits="userSpaceOnUse">
            <stop offset="0" stop-color="#5b7cfa" />
            <stop offset="1" stop-color="#38bdf8" />
        </linearGradient>
        <linearGradient id="{{ $uid }}-base" x1="12" y1="22" x2="36" y2="37" gradientUnits="userSpaceOnUse">
            <stop offset="0" stop-color="#3a63e0" />
            <stop offset="1" stop-color="#7c5cf0" />
        </linearGradient>
        <linearGradient id="{{ $uid }}-coin" x1="27" y1="27" x2="45" y2="45" gradientUnits="userSpaceOnUse">
            <stop offset="0" stop-color="#fde047" />
            <stop offset="1" stop-color="#f59e0b" />
        </linearGradient>
    </defs>
    <path d="M12 22v9c0 3.5 5.4 6 12 6s12-2.5 12-6v-9l-12 6-12-6Z" fill="url(#{{ $uid }}-base)" />
    <path d="M24 6 46 17 24 28 2 17 24 6Z" fill="url(#{{ $uid }}-top)" />
    <path d="M43 18.5V29" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" />
    <circle cx="43" cy="30.5" r="2.2" fill="#f59e0b" />
    <circle cx="35" cy="36" r="9.5" fill="url(#{{ $uid }}-coin)" />
    <circle cx="35" cy="36" r="6" stroke="#fff" stroke-opacity=".75" stroke-width="1.5" />
    <path d="M33 33.5c.6-.9 3.6-1.3 4 .1.4 1.5-3.6 1.2-3.8 2.8-.2 1.6 3.4 1.5 4.1.3" stroke="#fff" stroke-opacity=".9" stroke-width="1.3" stroke-linecap="round" />
</svg>
