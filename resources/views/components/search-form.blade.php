@props(['action', 'placeholder' => null])

{{-- GET form shared by the coordinator lists: a search box, any extra filters in the slot, and a reset link. --}}
<form method="GET" action="{{ $action }}" class="mb-5 flex flex-wrap items-center gap-3">
    <div class="relative w-full sm:w-72">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3.5-3.5" />
        </svg>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ $placeholder ?? __('Search') }}"
            class="block w-full rounded-lg border-slate-300 pl-9 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    {{ $slot }}

    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700">{{ __('Search') }}</button>

    @if (request()->query())
        <a href="{{ $action }}" class="text-sm font-medium text-slate-600 hover:underline">{{ __('Reset') }}</a>
    @endif
</form>
