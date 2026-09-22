import './bootstrap';

import Alpine from 'alpinejs';
import squeezeCarousel from './squeeze-carousel';

window.Alpine = Alpine;

Alpine.data('squeezeCarousel', squeezeCarousel);

Alpine.start();
