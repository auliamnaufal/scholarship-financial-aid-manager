import './bootstrap';

import Alpine from 'alpinejs';
import squeezeCarousel from './squeeze-carousel';
import './cursor-glow';
import './confirm-dialog';

window.Alpine = Alpine;

Alpine.data('squeezeCarousel', squeezeCarousel);

Alpine.start();

// Reveal data-reveal elements as they scroll into view.
const revealTargets = document.querySelectorAll('[data-reveal]');

if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
    );

    revealTargets.forEach((el) => observer.observe(el));
} else {
    revealTargets.forEach((el) => el.classList.add('is-visible'));
}
