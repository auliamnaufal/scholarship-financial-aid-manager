// A soft glow that trails the pointer, and a spotlight on any .spotlight card
// under it. The colour drifts from blue through violet and pink to amber as the pointer
// moves across the window, so the glow always matches where it is.

const palette = [
    [58, 99, 224], // indigo
    [139, 92, 246], // violet
    [244, 114, 182], // pink
    [251, 191, 36], // amber
];

const lerp = (a, b, t) => a + (b - a) * t;

function colourAt(ratio) {
    const scaled = Math.min(Math.max(ratio, 0), 1) * (palette.length - 1);
    const index = Math.min(Math.floor(scaled), palette.length - 2);
    const t = scaled - index;
    const [from, to] = [palette[index], palette[index + 1]];

    return from.map((channel, i) => Math.round(lerp(channel, to[i], t))).join(' ');
}

function init() {
    const canFollow = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    const calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!canFollow || calm) {
        return;
    }

    const root = document.documentElement;
    const glow = document.createElement('div');
    glow.className = 'cursor-glow';
    glow.setAttribute('aria-hidden', 'true');
    document.body.appendChild(glow);

    let targetX = window.innerWidth / 2;
    let targetY = window.innerHeight / 2;
    let x = targetX;
    let y = targetY;
    let running = false;

    const frame = () => {
        x = lerp(x, targetX, 0.14);
        y = lerp(y, targetY, 0.14);
        glow.style.transform = `translate3d(${x}px, ${y}px, 0)`;

        if (Math.abs(targetX - x) > 0.3 || Math.abs(targetY - y) > 0.3) {
            requestAnimationFrame(frame);
        } else {
            running = false;
        }
    };

    document.addEventListener(
        'pointermove',
        (event) => {
            targetX = event.clientX;
            targetY = event.clientY;
            glow.classList.add('is-active');
            root.style.setProperty('--glow-rgb', colourAt(event.clientX / Math.max(window.innerWidth, 1)));

            const card = event.target instanceof Element ? event.target.closest('.spotlight') : null;

            if (card) {
                const rect = card.getBoundingClientRect();
                card.style.setProperty('--mx', `${event.clientX - rect.left}px`);
                card.style.setProperty('--my', `${event.clientY - rect.top}px`);
            }

            if (!running) {
                running = true;
                requestAnimationFrame(frame);
            }
        },
        { passive: true },
    );

    document.addEventListener('pointerleave', () => glow.classList.remove('is-active'));
    document.documentElement.addEventListener('mouseleave', () => glow.classList.remove('is-active'));
}

init();
