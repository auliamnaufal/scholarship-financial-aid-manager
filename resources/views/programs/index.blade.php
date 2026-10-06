<x-public-layout>
    @php
        $featured = $stats['featured'];
        $daysLeft = fn ($program) => (int) now()->startOfDay()->diffInDays($program->application_deadline->copy()->startOfDay(), false);
        $deadlineText = function ($program) use ($daysLeft) {
            $days = $daysLeft($program);

            return $days <= 0 ? 'Ditutup hari ini' : $days.' hari lagi';
        };
        $eligibility = fn ($program) => $program->type->value === 'need_based'
            ? 'Penghasilan ≤ '.\App\Support\Money::rupiah($program->max_family_income)
            : 'IPK ≥ '.number_format((float) $program->min_gpa, 2, ',', '.');

        $isStudent = $matches->isNotEmpty();
        $eligibleCount = $matches->where('state', 'eligible')->count();
        $badgeStyles = [
            'eligible' => 'bg-emerald-500 text-white',
            'ineligible' => 'bg-slate-800/80 text-slate-100',
            'incomplete' => 'bg-amber-400 text-slate-900',
            'applied' => 'bg-indigo-600 text-white',
            'blocked' => 'bg-slate-800/80 text-slate-100',
        ];
        $cards = $programs->map(fn ($program) => [
            'name' => $program->name,
            'type' => $program->type->value,
            'source' => $program->funding_source,
            'days' => $daysLeft($program),
            'state' => $matches[$program->id]['state'] ?? null,
        ])->values();
    @endphp

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-indigo-800">
        <div class="pointer-events-none absolute -left-24 -top-24 h-80 w-80 animate-blob rounded-full bg-white/10 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute bottom-0 right-0 h-96 w-96 translate-x-1/3 translate-y-1/3 animate-blob rounded-full bg-sky-400/20 blur-3xl" style="animation-delay: -6s" aria-hidden="true"></div>
        <x-floating-icons layout="hero" />

        <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-5 lg:px-8 lg:py-24">
            <div class="relative lg:col-span-3">
                <span class="inline-flex animate-fade-up items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-amber-300">
                    <span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-300 opacity-75"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-amber-300"></span></span>
                    Pendaftaran beasiswa dibuka
                </span>
                <h1 class="mt-6 animate-fade-up font-display text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl" style="animation-delay: 120ms">
                    Wujudkan kuliah<br>
                    <span class="text-amber-300">tanpa beban biaya.</span>
                </h1>
                <p class="mt-6 max-w-xl animate-fade-up text-lg leading-relaxed text-indigo-100" style="animation-delay: 240ms">
                    Temukan beasiswa dan bantuan keuangan yang sesuai dengan prestasi dan kebutuhan Anda. Ajukan berkas secara daring, pantau perkembangannya, dan terima hasilnya dalam satu platform.
                </p>

                <div class="mt-8 flex animate-fade-up flex-wrap gap-3" style="animation-delay: 360ms">
                    @guest
                        <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-indigo-900 shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-50 hover:shadow-lg">
                            Daftar Sekarang!
                            <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                        <a href="#beasiswa" class="group inline-flex items-center gap-2 rounded-xl border border-white/25 px-6 py-3 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-white/10">
                            Lihat Beasiswa
                            <svg class="h-4 w-4 transition group-hover:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" /></svg>
                        </a>
                    @else
                        <a href="#beasiswa" class="group inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-indigo-900 shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-50 hover:shadow-lg">
                            Lihat Beasiswa
                            <svg class="h-4 w-4 transition group-hover:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" /></svg>
                        </a>
                    @endguest
                </div>

                @if ($stats['count'] > 0)
                    <dl class="mt-12 grid max-w-xl animate-fade-up grid-cols-3 gap-4 border-t border-white/10 pt-8 sm:gap-6" style="animation-delay: 480ms">
                        <div>
                            <dd class="font-display text-2xl font-bold text-white sm:text-3xl">{{ $stats['count'] }}</dd>
                            <dt class="mt-1 text-xs text-indigo-200">Program dibuka</dt>
                        </div>
                        <div>
                            <dd class="font-display text-2xl font-bold text-white sm:text-3xl">{{ \App\Support\Money::compact($stats['budget']) }}</dd>
                            <dt class="mt-1 text-xs text-indigo-200">Total pendanaan</dt>
                        </div>
                        <div>
                            <dd class="font-display text-2xl font-bold text-white sm:text-3xl">{{ $stats['closing']->translatedFormat('d M') }}</dd>
                            <dt class="mt-1 text-xs text-indigo-200">Penutupan terdekat</dt>
                        </div>
                    </dl>
                @endif
            </div>

            @if ($featured)
                <div class="relative lg:col-span-2">
                    <div class="spotlight rounded-2xl border border-white/15 bg-white/10 p-6 shadow-2xl backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-wider text-amber-300">Segera ditutup</p>
                        <h2 class="mt-3 font-display text-2xl font-bold text-white">{{ $featured->name }}</h2>
                        <p class="mt-1 text-sm text-indigo-200">Didanai oleh {{ $featured->funding_source }}</p>

                        <dl class="mt-6 grid grid-cols-2 gap-4 text-sm">
                            <div class="rounded-xl bg-white/10 p-3">
                                <dt class="text-xs text-indigo-200">Batas pendaftaran</dt>
                                <dd class="mt-1 font-semibold text-white">{{ $featured->application_deadline->translatedFormat('d M Y') }}</dd>
                            </div>
                            <div class="rounded-xl bg-white/10 p-3">
                                <dt class="text-xs text-indigo-200">Sisa waktu</dt>
                                <dd class="mt-1 font-semibold text-white">{{ $deadlineText($featured) }}</dd>
                            </div>
                        </dl>

                        <p class="mt-4 text-sm text-indigo-100">{{ $eligibility($featured) }}</p>

                        <a href="{{ route('scholarships.show', $featured) }}" class="group mt-6 flex items-center justify-center gap-2 rounded-xl bg-amber-400 px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-amber-300">
                            Lihat Detail & Daftar
                            <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Daftar beasiswa --}}
    <section id="beasiswa" class="scroll-mt-16 py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
             x-data="{
                 cards: @js($cards),
                 q: '',
                 type: 'all',
                 source: '',
                 deadline: 'all',
                 matchOnly: false,
                 shown(card) {
                     if (this.type !== 'all' && this.type !== card.type) return false;
                     if (this.source !== '' && this.source !== card.source) return false;
                     if (this.deadline !== 'all' && card.days > Number(this.deadline)) return false;
                     if (this.matchOnly && card.state !== 'eligible') return false;
                     return card.name.toLowerCase().includes(this.q.toLowerCase());
                 },
                 get visible() { return this.cards.filter(card => this.shown(card)).length; },
                 reset() { this.q = ''; this.type = 'all'; this.source = ''; this.deadline = 'all'; this.matchOnly = false; },
             }">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div class="max-w-2xl" data-reveal>
                    <p class="text-sm font-semibold uppercase tracking-wider text-indigo-700">Program tersedia</p>
                    <h2 class="mt-2 font-display text-3xl font-bold tracking-tight text-slate-900">Pilih beasiswa yang sesuai untuk Anda</h2>
                    <p class="mt-3 text-slate-600">Setiap program memiliki persyaratan dan batas waktu yang berbeda. Baca detailnya sebelum mendaftar.</p>
                </div>
            </div>

            @if ($programs->isEmpty())
                <div class="mt-10 rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
                    Saat ini belum ada program beasiswa yang dibuka. Silakan cek kembali nanti.
                </div>
            @else
                @if ($hiddenCount === 0)
                <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="inline-flex rounded-xl bg-slate-200/70 p-1 text-sm font-medium">
                            <button type="button" @click="type = 'all'" :class="type === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'" class="rounded-lg px-4 py-2 transition">Semua</button>
                            <button type="button" @click="type = 'need_based'" :class="type === 'need_based' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'" class="rounded-lg px-4 py-2 transition">Berbasis kebutuhan</button>
                            <button type="button" @click="type = 'merit_based'" :class="type === 'merit_based' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'" class="rounded-lg px-4 py-2 transition">Berbasis prestasi</button>
                        </div>
                        <div class="relative sm:w-72">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" /></svg>
                            <input type="search" x-model="q" placeholder="Cari nama beasiswa" class="w-full rounded-xl border-slate-300 bg-white py-2.5 pl-9 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
    
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <select x-model="source" aria-label="Sumber dana" class="rounded-xl border-slate-300 bg-white py-2 pl-3 pr-9 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Semua sumber dana</option>
                            @foreach ($sources as $source)
                                <option value="{{ $source }}">{{ $source }}</option>
                            @endforeach
                        </select>
    
                        <select x-model="deadline" aria-label="Tenggat" class="rounded-xl border-slate-300 bg-white py-2 pl-3 pr-9 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="all">Semua tenggat</option>
                            <option value="7">Ditutup dalam 7 hari</option>
                            <option value="30">Ditutup dalam 30 hari</option>
                            <option value="60">Ditutup dalam 60 hari</option>
                        </select>
    
                        @if ($isStudent)
                            <button type="button" @click="matchOnly = ! matchOnly"
                                    :class="matchOnly ? 'bg-emerald-600 text-white ring-emerald-600' : 'bg-white text-slate-700 ring-slate-300 hover:bg-slate-50'"
                                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium shadow-sm ring-1 ring-inset transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                Cocok untuk saya ({{ $eligibleCount }})
                            </button>
                        @endif
    
                        <button type="button" x-show="q || type !== 'all' || source || deadline !== 'all' || matchOnly" x-cloak @click="reset()" class="text-sm font-medium text-indigo-700 hover:text-indigo-600">
                            Atur ulang
                        </button>
                    </div>
                @endif

                @if ($isStudent)
                    <p class="mt-4 text-sm text-slate-600">
                        <span class="font-semibold text-slate-900">{{ $eligibleCount }} dari {{ $programs->count() }}</span>
                        beasiswa cocok dengan biodata Anda.
                        @if ($matches->where('state', 'incomplete')->isNotEmpty())
                            <a href="{{ route('student.biodata.edit') }}" class="font-medium text-indigo-700 hover:underline">Lengkapi biodata</a>
                            agar penilaian lebih akurat.
                        @endif
                    </p>
                @endif

                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($programs as $program)
                        @php
                            $isNeedBased = $program->type->value === 'need_based';
                            $days = $daysLeft($program);
                        @endphp
                        <article data-reveal style="--d: {{ ($loop->index % 3) * 120 }}ms" x-show="shown(cards[{{ $loop->index }}])"
                                 class="spotlight group flex flex-col overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-900/5 transition hover:-translate-y-1 hover:shadow-xl">
                            <div class="relative h-40 overflow-hidden" style="background: {{ $program->coverGradient() }}">
                                <img src="{{ $program->coverImage() }}" alt="" loading="lazy" onerror="this.remove()"
                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent"></div>
                                <span class="absolute left-4 top-4 rounded-full px-3 py-1 text-xs font-semibold {{ $isNeedBased ? 'bg-indigo-600 text-white' : 'bg-amber-400 text-slate-900' }}">
                                    {{ $program->type->label() }}
                                </span>
                                @if ($match = $matches[$program->id] ?? null)
                                    <span class="absolute right-4 top-4 inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold shadow-sm {{ $badgeStyles[$match['state']] }}"
                                          title="{{ collect($match['checks'])->pluck('text')->implode(' ') }}">
                                        @if ($match['state'] === 'eligible')
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                        @endif
                                        {{ $match['label'] }}
                                    </span>
                                @endif
                                <span class="absolute bottom-3 right-4 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold {{ $days <= 7 ? 'text-red-600' : 'text-slate-700' }}">
                                    {{ $deadlineText($program) }}
                                </span>
                            </div>

                            <div class="flex flex-1 flex-col p-6">
                                <h3 class="font-display text-lg font-bold text-slate-900">{{ $program->name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">Didanai oleh {{ $program->funding_source }}</p>

                                @if ($program->description)
                                    <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-slate-600">{{ $program->description }}</p>
                                @endif

                                <dl class="mt-5 space-y-2 border-t border-slate-100 pt-4 text-sm">
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">Batas pendaftaran</dt>
                                        <dd class="font-medium text-slate-900">{{ $program->application_deadline->translatedFormat('d M Y') }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">Syarat utama</dt>
                                        <dd class="whitespace-nowrap text-right font-medium text-slate-900">{{ $eligibility($program) }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">Berkas diminta</dt>
                                        <dd class="font-medium text-slate-900">{{ $program->requirements_count }} berkas</dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">Beasiswa lain</dt>
                                        <dd class="font-medium {{ $program->allows_other_scholarships ? 'text-slate-900' : 'text-amber-700' }}">{{ $program->allows_other_scholarships ? 'Boleh bersamaan' : 'Tidak boleh bersamaan' }}</dd>
                                    </div>
                                </dl>

                                <a href="{{ route('scholarships.show', $program) }}" class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-600">
                                    Lihat Detail
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                                </a>
                            </div>
                        </article>
                    @endforeach

                    @if ($hiddenCount > 0)
                        <a href="{{ route('register') }}" data-reveal style="--d: {{ ($programs->count() % 3) * 120 }}ms"
                           class="group relative flex min-h-[22rem] flex-col items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950 to-indigo-800 p-8 text-center shadow-soft ring-1 ring-slate-900/5 transition hover:-translate-y-1 hover:shadow-xl">
                            <div class="pointer-events-none absolute -right-12 -top-12 h-44 w-44 rounded-full bg-sky-400/20 blur-3xl" aria-hidden="true"></div>
                            <p class="relative font-display text-2xl font-bold text-white">Lihat Lebih Banyak</p>
                            <span class="relative mt-6 flex h-14 w-14 items-center justify-center rounded-full bg-amber-400 text-slate-900 transition duration-300 group-hover:translate-x-1 group-hover:bg-amber-300">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                            </span>
                        </a>
                    @endif
                </div>

                <div x-show="visible === 0" x-cloak class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                    Tidak ada beasiswa yang sesuai dengan pilihan Anda.
                    <button type="button" @click="reset()" class="font-medium text-indigo-700 hover:underline">Atur ulang filter</button>
                </div>
            @endif
        </div>
    </section>

    {{-- Cara mendaftar --}}
    <section id="cara-daftar" class="scroll-mt-16 border-y border-slate-200 bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center" data-reveal>
                <p class="text-sm font-semibold uppercase tracking-wider text-indigo-700">Alur pendaftaran</p>
                <h2 class="mt-2 font-display text-3xl font-bold tracking-tight text-slate-900">Empat langkah menuju beasiswa</h2>
                <p class="mt-3 text-slate-600">Proses dibuat sederhana dan transparan, dari mendaftar sampai dana diterima.</p>
            </div>

            <ol class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Buat akun & lengkapi biodata', 'Daftar sebagai mahasiswa lalu isi data diri, data akademik, kondisi keluarga, dan rekening bank.'],
                    ['Pilih beasiswa & kirim berkas', 'Pilih program yang sesuai dan isi formulir lalu unggah dokumen yang diminta seperti CV dan transkrip nilai.'],
                    ['Penilaian oleh reviewer', 'Reviewer memeriksa dan memberi skor pada berkas Anda. Status pendaftaran dapat dipantau kapan saja.'],
                    ['Keputusan & pencairan dana', 'Koordinator menetapkan hasil akhir. Jika disetujui, dana dicairkan secara bertahap ke rekening Anda.'],
                ] as $i => [$title, $text])
                    <li data-reveal style="--d: {{ $i * 120 }}ms" class="spotlight group relative rounded-2xl border border-slate-200 bg-slate-50 p-6 transition hover:-translate-y-1 hover:border-indigo-200 hover:bg-white hover:shadow-lg">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-700 font-display text-lg font-bold text-white transition group-hover:rotate-6 group-hover:scale-110">{{ $i + 1 }}</span>
                        <h3 class="mt-4 font-display text-base font-bold text-slate-900">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Keunggulan --}}
    <section class="py-16 sm:py-20">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 md:grid-cols-3 lg:px-8">
            @foreach ([
                ['Transparan', 'Status pendaftaran, hasil penilaian, dan riwayat pencairan dana dapat Anda lihat langsung dari akun.', 'M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Zm9.75 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z'],
                ['Aman', 'Dokumen pribadi seperti transkrip dan surat rekomendasi disimpan secara privat dan hanya dapat dibuka oleh pihak yang berwenang.', 'M16.5 10.5V7.5a4.5 4.5 0 1 0-9 0v3M6.75 10.5h10.5a1.5 1.5 0 0 1 1.5 1.5v7.5a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5V12a1.5 1.5 0 0 1 1.5-1.5Z'],
                ['Tepat sasaran', 'Kelayakan IPK dan penghasilan keluarga diperiksa secara otomatis sehingga Anda hanya mendaftar program yang sesuai.', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ] as [$title, $text, $icon])
                <div data-reveal style="--d: {{ $loop->index * 120 }}ms" class="spotlight group rounded-2xl bg-white p-6 shadow-soft ring-1 ring-slate-900/5 transition hover:-translate-y-1 hover:shadow-lg">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 transition group-hover:bg-indigo-700 group-hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                    </span>
                    <h3 class="mt-4 font-display text-lg font-bold text-slate-900">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="scroll-mt-16 border-t border-slate-200 bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8" x-data="{ active: 0 }">
            <div class="text-center" data-reveal>
                <p class="text-sm font-semibold uppercase tracking-wider text-indigo-700">Tanya jawab</p>
                <h2 class="mt-2 font-display text-3xl font-bold tracking-tight text-slate-900">Pertanyaan yang sering diajukan</h2>
            </div>

            <div class="mt-10 divide-y divide-slate-200 rounded-2xl border border-slate-200" data-reveal>
                @foreach ([
                    ['Siapa yang dapat mendaftar?', 'Mahasiswa aktif yang memiliki akun dan telah melengkapi biodata. Setiap program memiliki syarat IPK minimal atau batas penghasilan keluarga yang diperiksa otomatis saat mendaftar.'],
                    ['Berkas apa saja yang harus disiapkan?', 'Berbeda-beda untuk setiap program. Umumnya berupa CV, transkrip nilai (KHS), dan esai. Sebagian program juga meminta surat rekomendasi atau bukti penghasilan keluarga. Berkas harus berformat PDF, DOC, atau DOCX dengan ukuran maksimal 5 MB.'],
                    ['Bisakah saya membatalkan pendaftaran?', 'Bisa, selama koordinator belum memutuskan. Anda dapat mendaftar kembali selama program masih dibuka.'],
                    ['Kapan dana beasiswa dicairkan?', 'Setelah pendaftaran disetujui, koordinator mencatat pencairan secara bertahap per semester ke rekening bank yang Anda isi pada biodata.'],
                    ['Apakah data saya aman?', 'Dokumen yang Anda unggah disimpan secara privat dan hanya dapat diakses oleh Anda, reviewer, dan koordinator program terkait.'],
                ] as $i => [$q, $a])
                    <div>
                        <button type="button" @click="active = active === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left">
                            <span class="font-semibold text-slate-900">{{ $q }}</span>
                            <svg class="h-5 w-5 shrink-0 text-slate-400 transition" :class="active === {{ $i }} ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div x-show="active === {{ $i }}" x-cloak x-transition class="px-6 pb-5 text-sm leading-relaxed text-slate-600">{{ $a }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Ajakan akhir --}}
    @guest
        <section class="bg-gradient-to-br from-slate-900 via-indigo-950 to-indigo-800">
            <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8" data-reveal>
                <h2 class="font-display text-3xl font-bold tracking-tight text-white">Siap mengajukan beasiswa?</h2>
                <p class="mx-auto mt-3 max-w-xl text-indigo-100">Buat akun dalam hitungan menit dan mulai mendaftar program yang sesuai dengan Anda.</p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('register') }}" class="rounded-xl bg-amber-400 px-6 py-3 text-sm font-semibold text-slate-900 transition hover:bg-amber-300">Buat Akun Sekarang</a>
                    <a href="{{ route('login') }}" class="rounded-xl border border-white/25 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">Sudah punya akun? Masuk</a>
                </div>
            </div>
        </section>
    @endguest
</x-public-layout>
