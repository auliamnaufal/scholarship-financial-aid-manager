<x-public-layout>
    @php
        $isNeedBased = $program->type->value === 'need_based';
        $days = (int) now()->startOfDay()->diffInDays($program->application_deadline->copy()->startOfDay(), false);
    @endphp

    <x-slot name="header">
        <a href="{{ route('home') }}#beasiswa" class="inline-flex items-center gap-1 text-sm font-medium text-indigo-200 hover:text-white">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            {{ __('Back to listings') }}
        </a>
        <div class="mt-5 flex flex-wrap items-center gap-2">
            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $isNeedBased ? 'bg-indigo-500 text-white' : 'bg-amber-400 text-slate-900' }}">{{ $program->type->label() }}</span>
            @if ($program->isOpen())
                <span class="rounded-full bg-emerald-400/20 px-3 py-1 text-xs font-semibold text-emerald-200">Pendaftaran dibuka</span>
            @else
                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-slate-200">{{ __('Closed') }}</span>
            @endif
        </div>
        <h1 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-white sm:text-4xl">{{ $program->name }}</h1>
        <p class="mt-2 text-indigo-200">Didanai oleh {{ $program->funding_source }}</p>
    </x-slot>

    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-3 lg:px-8">
        <div class="space-y-8 lg:col-span-2">
            <section class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-slate-900/5 sm:p-8">
                <h2 class="font-display text-xl font-bold text-slate-900">Tentang beasiswa ini</h2>
                <p class="mt-3 leading-relaxed text-slate-600">
                    {{ $program->description ?: 'Belum ada deskripsi untuk program ini.' }}
                </p>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-slate-900/5 sm:p-8">
                <h2 class="font-display text-xl font-bold text-slate-900">Persyaratan peserta</h2>
                <ul class="mt-4 space-y-3 text-sm text-slate-700">
                    <li class="flex gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        Terdaftar sebagai mahasiswa aktif dan telah melengkapi biodata.
                    </li>
                    @if ($isNeedBased)
                        <li class="flex gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            Penghasilan keluarga maksimal {{ \App\Support\Money::rupiah($program->max_family_income) }} per bulan.
                        </li>
                    @else
                        <li class="flex gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            IPK minimal {{ number_format((float) $program->min_gpa, 2, ',', '.') }}.
                        </li>
                    @endif
                </ul>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-slate-900/5 sm:p-8">
                <h2 class="font-display text-xl font-bold text-slate-900">{{ __('What this scholarship asks for') }}</h2>
                @if ($program->requirements->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">{{ __('This scholarship asks for no supporting documents.') }}</p>
                @else
                    <ul class="mt-4 divide-y divide-slate-100">
                        @foreach ($program->requirements->sortByDesc('is_required') as $requirement)
                            <li class="flex items-start justify-between gap-4 py-3">
                                <div>
                                    <p class="font-medium text-slate-900">{{ $requirement->requirementType->name }}</p>
                                    <p class="mt-0.5 text-sm text-slate-500">{{ $requirement->instructions ?: $requirement->requirementType->description }}</p>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $requirement->is_required ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $requirement->is_required ? 'Wajib' : 'Opsional' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-4 text-xs text-slate-400">{{ __('Files must be PDF, DOC or DOCX, up to 5 MB each.') }}</p>
                @endif
            </section>
        </div>

        <aside class="lg:col-span-1">
            <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-slate-900/5 lg:sticky lg:top-24">
                <h2 class="font-display text-lg font-bold text-slate-900">Ringkasan program</h2>

                <dl class="mt-5 space-y-4 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">{{ __('Application deadline') }}</dt>
                        <dd class="text-right font-semibold text-slate-900">{{ $program->application_deadline->translatedFormat('d F Y') }}</dd>
                    </div>
                    @if ($program->isOpen())
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Sisa waktu</dt>
                            <dd class="font-semibold {{ $days <= 7 ? 'text-red-600' : 'text-slate-900' }}">{{ $days <= 0 ? 'Hari terakhir' : $days.' hari lagi' }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">{{ __('Total funding') }}</dt>
                        <dd class="font-semibold text-slate-900">{{ \App\Support\Money::rupiah($program->budget) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">{{ __('Funded by') }}</dt>
                        <dd class="text-right font-semibold text-slate-900">{{ $program->funding_source }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">{{ $isNeedBased ? __('Max family income') : __('Minimum GPA') }}</dt>
                        <dd class="text-right font-semibold text-slate-900">
                            {{ $isNeedBased ? \App\Support\Money::rupiah($program->max_family_income) : number_format((float) $program->min_gpa, 2, ',', '.') }}
                        </dd>
                    </div>
                </dl>

                @if ($program->isOpen())
                    <a href="{{ route('student.applications.create', $program) }}" class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-700 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-600">
                        {{ __('Apply now') }}
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                    @guest
                        <p class="mt-3 text-center text-xs text-slate-400">{{ __("You'll be asked to log in first.") }}</p>
                    @endguest
                @else
                    <div class="mt-6 rounded-xl bg-slate-100 px-4 py-3 text-center text-sm font-medium text-slate-600">Pendaftaran sudah ditutup</div>
                @endif
            </div>
        </aside>
    </div>
</x-public-layout>
