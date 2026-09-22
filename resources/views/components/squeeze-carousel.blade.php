@props([
    /**
     * The panels, in the order they are read. Each is an array with:
     *   title       the dark opening line under the panels
     *   description the grey sentence that runs on from the title
     *   image       picture for the panel; it crops from the middle as the panel narrows
     *   imageAlt    alt text for that picture
     *   background  any CSS background, shown under the picture and if it fails to load
     *   overlay     short text for the corner of the open panel
     *   action      text on the button; no text, no button
     *   href        where the button goes
     *   target      opens the link in a new tab
     */
    'slides' => [],
    /** Which panel starts open. */
    'defaultIndex' => 0,
    /** Height of the row. */
    'height' => '320px',
    /** Width of a slat in the tail. */
    'slatWidth' => '8px',
    /** Space between slats. */
    'slatGap' => '8px',
    /** Space between the four columns. */
    'gap' => '16px',
    /** Corner rounding on a panel. */
    'radius' => '14px',
    /** Milliseconds the slide takes. */
    'duration' => 1000,
    /** Widen the panel under the pointer. */
    'hoverGrow' => true,
    /** Step on by itself. */
    'autoplay' => false,
    /** Milliseconds a panel stays open under autoplay. */
    'interval' => 6000,
    /** Show the two arrow buttons. */
    'controls' => true,
    /** What a screen reader calls the carousel. */
    'label' => 'Featured',
])

@php
    $slides = array_values($slides);
    $count = count($slides);

    // Four columns plus a tail of slats. Fewer slides, shorter tail — and with
    // fewer slides than columns the row is cut short rather than repeating one.
    // These have to match the same two lines in the Alpine component.
    $columns = min(4, $count);
    $slats = $count > $columns ? max(1, min(3, $count - 4)) : 0;

    $id = 'sq-'.substr(md5(uniqid('', true)), 0, 8);

    // The visual half of each slide is read by Alpine, because which slide sits
    // in which panel is only known at runtime. The copy below the row is
    // rendered by Blade instead, one block per slide, cross-faded on `open`.
    $panels = array_map(fn ($slide) => [
        'image' => $slide['image'] ?? null,
        'imageAlt' => $slide['imageAlt'] ?? '',
        'background' => $slide['background'] ?? null,
        'overlay' => $slide['overlay'] ?? null,
        'title' => $slide['title'] ?? '',
    ], $slides);
@endphp

@if ($count)
    {{-- The widths inside read the width this carousel is given, not the width
         of the window, so the wrapper is the query container. --}}
    <div style="container-type: inline-size" {{ $attributes->merge(['class' => 'w-full']) }}>
    <div
        x-data="squeezeCarousel(@js([
            'slides' => $panels,
            'defaultIndex' => $defaultIndex,
            'duration' => (int) $duration,
            'hoverGrow' => (bool) $hoverGrow,
            'autoplay' => (bool) $autoplay,
            'interval' => (int) $interval,
        ]))"
        @mouseenter="paused = true"
        @mouseleave="paused = false; hover = -1"
        @focusin="paused = true"
        @focusout="paused = false"
        :style="{ '--sq-ms': ms + 'ms' }"
        style="
            --sq-h-in: {{ $height }};
            --sq-gap-in: {{ $gap }};
            --sq-slat-in: {{ $slatWidth }};
            --sq-slat-gap-in: {{ $slatGap }};
            --sq-slats: {{ $slats }};
            --sq-gaps: {{ max(0, $columns - 1) }};
            --sq-radius: {{ $radius }};
            --sq-ease: cubic-bezier(0.16, 1, 0.3, 1);
        "
        class="sq-carousel flex w-full flex-col"
    >
        @if ($controls && $count > 1)
            <div class="mb-4 flex justify-end gap-2">
                <button
                    type="button"
                    aria-label="{{ __('Previous') }}"
                    @click="step(-1)"
                    class="grid size-9 place-items-center rounded-xl btn-gradient text-white shadow-glow transition hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                >
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M9.6 2.6 5.1 7.1h9.1v1.8H5.1l4.5 4.5-1.2 1.2-6-6L1.8 8l.6-.6 6-6 1.2 1.2Z" />
                    </svg>
                </button>
                <button
                    type="button"
                    aria-label="{{ __('Next') }}"
                    @click="step(1)"
                    class="grid size-9 place-items-center rounded-xl btn-gradient text-white shadow-glow transition hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                >
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M6.4 2.6l4.5 4.5H1.8v1.8h9.1l-4.5 4.5 1.2 1.2 6-6 .6-.6-.6-.6-6-6-1.2 1.2Z" />
                    </svg>
                </button>
            </div>
        @endif

        <div class="w-full overflow-hidden" style="height: var(--sq-h)">
            <div
                role="tablist"
                aria-label="{{ $label }}"
                aria-orientation="horizontal"
                @keydown="onKeyDown($event)"
                :style="stripStyle"
                class="flex h-full w-max"
            >
                <template x-for="(card, place) in cards" :key="card.key">
                    <button
                        type="button"
                        role="tab"
                        :id="'{{ $id }}-tab-' + card.key"
                        :aria-selected="colOf(place) === 0"
                        aria-controls="{{ $id }}-panel"
                        :aria-label="slides[card.slide].title"
                        :tabindex="colOf(place) === 0 ? 0 : -1"
                        @mousemove="hoverGrow && (hover = colOf(place))"
                        @click="colOf(place) > 0 && step(colOf(place))"
                        :style="panelStyle(place)"
                        class="relative isolate h-full shrink-0 cursor-pointer overflow-hidden bg-slate-200 p-0 ring-1 ring-inset ring-slate-900/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                    >
                        {{-- Drawn at a fixed 16:9 block and centred, never at the width of its
                             card. Left to itself `object-fit: cover` reads whichever edge binds —
                             height while the card is a slat, width once it opens — so the picture
                             would rescale mid-slide and be resampled every frame. One block means
                             one scale: the card only ever changes how much of it you can see. --}}
                        <span
                            aria-hidden="true"
                            class="absolute inset-y-0 left-1/2 -translate-x-1/2"
                            style="width: var(--sq-hero); min-width: 100%"
                            :style="{ background: slides[card.slide].background }"
                        ></span>

                        <template x-if="slides[card.slide].image">
                            <img
                                :src="slides[card.slide].image"
                                :alt="slides[card.slide].imageAlt"
                                draggable="false"
                                loading="lazy"
                                x-on:error="$el.style.display = 'none'"
                                class="absolute inset-y-0 left-1/2 h-full max-w-none -translate-x-1/2 object-cover"
                                style="width: var(--sq-hero); min-width: 100%"
                            />
                        </template>

                        <template x-if="slides[card.slide].overlay">
                            <span
                                aria-hidden="true"
                                class="pointer-events-none absolute inset-x-0 bottom-0 flex items-end p-4 pt-16 sm:p-6 sm:pt-20"
                                style="background-image: linear-gradient(to top, rgb(15 23 42 / 0.65), transparent)"
                                :style="{
                                    opacity: colOf(place) === 0 ? 1 : 0,
                                    transition: 'opacity var(--sq-ms) var(--sq-ease)',
                                }"
                            >
                                <span
                                    class="font-display text-sm font-semibold tracking-tight text-white drop-shadow"
                                    x-text="slides[card.slide].overlay"
                                ></span>
                            </span>
                        </template>
                    </button>
                </template>
            </div>
        </div>

        <div id="{{ $id }}-panel" role="tabpanel" aria-live="polite" class="mt-6 grid lg:mt-7">
            @foreach ($slides as $i => $slide)
                <div
                    :aria-hidden="open !== {{ $i }}"
                    :style="{
                        opacity: open === {{ $i }} ? 1 : 0,
                        visibility: open === {{ $i }} ? 'visible' : 'hidden',
                        pointerEvents: open === {{ $i }} ? 'auto' : 'none',
                        transition: 'opacity var(--sq-ms) var(--sq-ease), visibility var(--sq-ms)',
                    }"
                    class="col-start-1 row-start-1 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between lg:gap-10"
                >
                    <p class="max-w-[46rem] text-[15px] leading-relaxed text-pretty lg:text-[17px]">
                        <span class="font-medium text-slate-900">{{ $slide['title'] ?? '' }}</span>
                        @isset($slide['description'])
                            <span class="text-slate-500">{{ $slide['description'] }}</span>
                        @endisset
                    </p>

                    @isset($slide['action'])
                        <a
                            href="{{ $slide['href'] ?? '#' }}"
                            @isset($slide['target']) target="{{ $slide['target'] }}" rel="noreferrer" @endisset
                            :tabindex="open === {{ $i }} ? 0 : -1"
                            class="group inline-flex shrink-0 items-center gap-2 rounded-xl btn-gradient px-4 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                        >
                            {{ $slide['action'] }}
                            <svg width="6" height="9" viewBox="0 0 6 9" fill="none" aria-hidden="true" class="transition-transform duration-200 group-hover:translate-x-0.5">
                                <path d="M1.2 1 4.7 4.5 1.2 8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                    @endisset
                </div>
            @endforeach
        </div>
    </div>
    </div>
@endif
