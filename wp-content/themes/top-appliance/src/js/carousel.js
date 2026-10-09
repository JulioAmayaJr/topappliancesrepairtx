/** Reviews carousel: native scroll-snap track (swipe), prev/next buttons, arrow keys, "Read more". */
export function initCarousels() {
	document.querySelectorAll('[data-carousel]').forEach((root) => {
		const track = root.querySelector('.ta-rv__track');
		const [prev, next] = root.querySelectorAll('.ta-rv__btn');
		if (!track) return;
		const step = () => (track.querySelector('.ta-rv__card')?.getBoundingClientRect().width || track.clientWidth) + 12;
		const go = (dir) => track.scrollBy({ left: dir * step(), behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
		const update = () => {
			const max = track.scrollWidth - track.clientWidth - 2;
			if (prev) prev.disabled = track.scrollLeft <= 2;
			if (next) next.disabled = track.scrollLeft >= max;
			root.querySelector('.ta-rv__nav')?.toggleAttribute('hidden', max <= 0);
		};
		prev?.addEventListener('click', () => go(-1));
		next?.addEventListener('click', () => go(1));
		track.addEventListener('keydown', (e) => {
			if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
				e.preventDefault();
				go(e.key === 'ArrowRight' ? 1 : -1);
			}
		});
		track.addEventListener('scroll', () => requestAnimationFrame(update), { passive: true });
		window.addEventListener('resize', update, { passive: true });

		// "Read more" only where the 4-line clamp actually hides text.
		root.querySelectorAll('.ta-rv__card').forEach((card) => {
			const text = card.querySelector('.ta-rv__text');
			const more = card.querySelector('.ta-rv__more');
			if (!text || !more) return;
			if (text.scrollHeight > text.clientHeight + 2) more.hidden = false;
			more.addEventListener('click', () => {
				const open = card.classList.toggle('is-open');
				more.setAttribute('aria-expanded', String(open));
				more.textContent = open ? 'Show less' : 'Read more';
			});
		});
		update();
	});
}
