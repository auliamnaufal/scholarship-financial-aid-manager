/**
 * Squeeze carousel — Alpine port of the React component.
 *
 * One panel gets the room; the rest are squeezed into slats down the right-hand
 * side. Opening a slat widens it and slides the row along, and the copy under
 * the row cross-fades to match.
 *
 * The row is four columns and a tail of slats, and it is a strip that slides
 * rather than a ring that turns. Four columns share out whatever is left once
 * the open card, the slats and the gaps are paid for. The open card starts from
 * a 16:9 block and then gives a little back — hence the negative first share.
 * Column -1 and anything past column 3 is a slat, so a card leaving the front
 * simply narrows to a slat and carries on out of the left edge.
 */

const clamp = (value, low, high) => Math.max(low, Math.min(high, value));

const SHARES = [-0.06, 0.61, 0.3, 0.15];

/** The hovered column takes more room. */
const STRETCHED = [0, 0.71, 0.4, 0.25];

/** Its neighbours give a little up to pay for it. */
const SQUEEZED = [-0.12, 0.59, 0.28, 0.13];

/**
 * The four shares add up to one, so the columns spend the room exactly. With
 * fewer slides than columns there is nothing to put in the last ones — repeating
 * a slide would show the open card twice — so the row is cut short and what the
 * dropped columns would have taken is handed back to the ones that remain.
 */
const fit = (shares, columns) => {
    const kept = shares.slice(0, columns);

    if (columns >= shares.length) return kept;

    const rest = kept.slice(1).reduce((sum, share) => sum + share, 0);
    const scale = rest ? (1 - kept[0]) / rest : 0;

    return [kept[0], ...kept.slice(1).map((share) => share * scale)];
};

export default function squeezeCarousel(options = {}) {
    const {
        slides = [],
        defaultIndex = 0,
        duration = 1000,
        hoverGrow = true,
        autoplay = false,
        interval = 6000,
    } = options;

    const count = slides.length;

    /** Never more columns than there are slides to put in them. */
    const columns = Math.min(4, count);

    const shares = fit(SHARES, columns);
    const stretched = fit(STRETCHED, columns);
    const squeezed = fit(SQUEEZED, columns);

    return {
        slides,
        count,
        columns,
        duration,
        hoverGrow,
        autoplay,
        interval,

        /** Fewer slides, shorter tail — and none at all once the row is cut short. */
        slats: count > columns ? clamp(count - 4, 1, 3) : 0,

        reduced: false,
        seed: 0,

        /** The strip: one entry per card on show, plus whatever a step added. */
        cards: [],

        /**
         * Which column each card sits in: its place in the strip plus this.
         * Stepping on pushes it down, so the card that was column 0 becomes
         * column -1 — a slat, on its way out of the left edge.
         */
        column: 0,

        /**
         * How far the strip is slid, counted in slats. Normally the same as
         * `column`; it parts company for the frame after a trim or before a
         * step back, where the strip has to move without being seen to.
         */
        slid: 0,

        /** True while transitions are off, so a reset cannot animate. */
        still: false,

        hover: -1,
        forward: true,
        paused: false,
        timers: [],
        autoTimer: null,

        get visible() {
            return this.columns + this.slats;
        },

        get ms() {
            return this.reduced ? 0 : this.duration;
        },

        /** The slide currently in column 0. */
        get open() {
            const card = this.cards[-this.column];

            return card ? card.slide : defaultIndex;
        },

        get stripStyle() {
            return {
                transform: `translateX(calc(${this.slid} * (var(--sq-slat) + var(--sq-gap))))`,
                transition: this.still ? 'none' : 'transform var(--sq-ms) var(--sq-ease)',
            };
        },

        wrap(i) {
            return ((i % this.count) + this.count) % this.count;
        },

        /** Runs `fn` once the DOM has caught up and the browser has painted. */
        afterPaint(fn) {
            this.$nextTick(() => requestAnimationFrame(fn));
        },

        init() {
            if (!this.count) return;

            const query = window.matchMedia('(prefers-reduced-motion: reduce)');
            this.reduced = query.matches;
            query.addEventListener('change', (event) => {
                this.reduced = event.matches;
            });

            this.cards = Array.from({ length: this.visible }, (_, place) => ({
                key: this.seed++,
                slide: this.wrap(defaultIndex + place),
            }));

            this.schedule();

            this.$watch('open', () => this.schedule());
            this.$watch('paused', () => this.schedule());
        },

        destroy() {
            this.timers.forEach(clearTimeout);
            clearTimeout(this.autoTimer);
        },

        /* --- autoplay ----------------------------------------------------- */

        schedule() {
            clearTimeout(this.autoTimer);

            if (!this.autoplay || this.paused || this.reduced || this.count < 2) return;

            this.autoTimer = setTimeout(() => this.step(1), this.interval);
        },

        /* --- movement ----------------------------------------------------- */

        /**
         * A step leaves the strip longer than it needs to be. Once the movement
         * has finished, cut it back to the cards on show and put the numbers
         * back to zero — the same picture, so nothing may animate on the way.
         *
         * Stepping on appends to the tail and stepping back prepends to the
         * head, so the cards on show are simply the last or the first of the
         * strip. Counting in from a column number instead would mean trusting a
         * figure taken before the step it belongs to had been applied — which
         * rapid clicking breaks.
         */
        settle() {
            // The card that was at the front is one of the ones about to go, so
            // a keyboard reader standing on it would be left with nothing
            // focused and no way to press on. Put them on the new front card.
            const keyboard =
                document.activeElement instanceof HTMLElement &&
                document.activeElement.matches('[role="tab"]') &&
                this.$root.contains(document.activeElement);

            this.cards = this.forward
                ? this.cards.slice(-this.visible)
                : this.cards.slice(0, this.visible);

            this.column = 0;
            this.slid = 0;
            this.still = true;
            this.afterPaint(() => {
                this.still = false;

                if (keyboard) {
                    this.$root
                        .querySelector('[role="tab"][tabindex="0"]')
                        ?.focus({ preventScroll: true });
                }
            });
        },

        step(by) {
            if (this.count < 2 || by === 0) return;

            this.timers.forEach(clearTimeout);
            this.timers = [];
            this.forward = by > 0;

            if (by > 0) {
                // The incoming slat joins the tail at full size before anything
                // moves, so the end of the row is never a slat short.
                const last = this.cards[this.cards.length - 1].slide;

                for (let k = 0; k < by; k++) {
                    this.cards.push({ key: this.seed++, slide: this.wrap(last + 1 + k) });
                }

                this.column -= by;
                this.slid -= by;
            } else {
                // Going back, the strip has to grow at the front, which shoves
                // everything right. Slide it left by the same amount with no
                // transition, then let it ease home.
                const first = this.cards[0].slide;
                const head = [];

                for (let k = 0; k < -by; k++) {
                    head.push({ key: this.seed++, slide: this.wrap(first - (-by - k)) });
                }

                this.cards = [...head, ...this.cards];
                this.still = true;
                this.slid += by;

                this.afterPaint(() => {
                    this.still = false;
                    this.afterPaint(() => {
                        this.slid = 0;
                    });
                });
            }

            this.timers.push(setTimeout(() => this.settle(), this.ms + 80));
        },

        /** Open a given slide by the shorter way round. */
        go(to) {
            const here = this.open;
            if (to === here) return;

            const ahead = this.wrap(to - here);
            this.step(ahead <= this.count / 2 ? ahead : ahead - this.count);
        },

        onKeyDown(event) {
            const by = { ArrowRight: 1, ArrowLeft: -1 }[event.key];
            if (by === undefined) return;

            event.preventDefault();
            this.step(by);
        },

        /* --- geometry ------------------------------------------------------ */

        /** The share a column takes, once the pointer has had its say. */
        shareOf(col) {
            const stretching =
                this.hoverGrow && this.hover >= 0 && this.hover < this.columns && !this.reduced;

            if (!stretching) return shares[col];

            return this.hover === col ? stretched[col] : squeezed[col];
        },

        /** A column's width, worked out in CSS so nothing needs measuring. */
        widthOf(col) {
            if (col < 0 || col >= this.columns) return 'var(--sq-slat)';
            if (col === 0) return `calc(var(--sq-hero) + var(--sq-room) * ${this.shareOf(0)})`;

            return `calc(var(--sq-room) * ${this.shareOf(col)})`;
        },

        colOf(place) {
            return place + this.column;
        },

        panelStyle(place) {
            const col = this.colOf(place);
            const width = this.widthOf(col);

            return {
                width,
                marginLeft:
                    place === 0
                        ? '0px'
                        : col < this.columns
                          ? 'var(--sq-gap)'
                          : 'var(--sq-slat-gap)',
                borderRadius: `min(var(--sq-radius), calc(${width} / 2))`,
                transitionProperty: 'width, margin-left',
                transitionDuration: this.still ? '0s' : 'var(--sq-ms)',
                transitionTimingFunction: 'var(--sq-ease)',
            };
        },
    };
}
