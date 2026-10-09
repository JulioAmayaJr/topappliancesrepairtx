/**
 * Motion layer (Motion "animate" + "inView" only). Rules:
 *  - never touches the hero / LCP element or anything already on screen at load (no flash, no CLS)
 *  - opacity/transform only (compositor-friendly, zero layout shift)
 *  - this chunk is not even downloaded with prefers-reduced-motion
 */
import { animate, inView } from 'motion';

const EASE = [0.22, 0.8, 0.24, 1];
const REVEAL = '.ta-section__head, .ta-why__media, .ta-rv, .ta-zip, .ta-map, .ta-codes, .ta-latest, .ta-cta__form, .ta-ctaband__card';
const STAGGER = '.ta-services, .ta-brandwall, .ta-steps, .ta-checklist, .ta-links--chips, .ta-faq';
const VIEW = { margin: '0px 0px -12% 0px' };

const belowFold = (el) => el.getBoundingClientRect().top > window.innerHeight * 0.95;
const hide = (el, y) => {
	el.style.opacity = '0';
	el.style.transform = `translateY(${y}px)`;
};

export function initFx() {
	// Read layout once (all rects), then write styles: no forced reflow.
	const reveal = [...document.querySelectorAll(REVEAL)].filter((el) => !el.closest('.ta-hero') && belowFold(el));
	const stagger = [...document.querySelectorAll(STAGGER)].filter((el) => !el.closest('.ta-hero') && belowFold(el));

	reveal.forEach((el) => {
		hide(el, 18);
		inView(
			el,
			() => {
				animate(el, { opacity: [0, 1], transform: ['translateY(18px)', 'translateY(0px)'] }, { duration: 0.6, ease: EASE });
			},
			VIEW
		);
	});

	stagger.forEach((list) => {
		const items = [...list.children];
		items.forEach((i) => hide(i, 12));
		inView(
			list,
			() => {
				animate(items, { opacity: [0, 1], transform: ['translateY(12px)', 'translateY(0px)'] }, { duration: 0.45, ease: EASE, delay: (i) => i * 0.045 });
			},
			VIEW
		);
	});

	// Count-up for the trust bar numbers. Width is locked first so digits never shift the line.
	document.querySelectorAll('.ta-count[data-count]').forEach((el) => {
		const to = parseInt(el.dataset.count, 10);
		if (!to) return;
		el.style.minWidth = `${String(to).length}ch`;
		inView(
			el,
			() => {
				animate(0, to, {
					duration: 1.4,
					ease: EASE,
					onUpdate: (v) => {
						el.textContent = String(Math.round(v));
					},
				});
			},
			{ amount: 'all' }
		);
	});
}
