/**
 * Front-end entry (ES module, deferred by nature). Interaction first; the motion layer is a
 * lazy chunk that never loads for visitors who prefer reduced motion.
 */
import { initNav } from './nav.js';
import { initZip } from './zip.js';
import { initMap } from './map.js';

document.documentElement.classList.add('js');

initNav();
initZip();
initMap();

const reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
if (!reduce.matches) {
	const start = () => import('./fx.js').then((m) => m.initFx());
	if ('requestIdleCallback' in window) {
		window.requestIdleCallback(start, { timeout: 1200 });
	} else {
		setTimeout(start, 150);
	}
}
