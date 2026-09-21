@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'block w-full rounded-lg border-slate-300 text-slate-900 placeholder:text-slate-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-slate-50 disabled:text-slate-500']) }}>
