/** Mega menu disclosure behaviour + mobile drawer niceties. */
export function initNav() {
	const items = [...document.querySelectorAll('.ta-nav__item--panel')];

	const setOpen = (li, open) => {
		li.classList.toggle('is-open', open);
		li.querySelector('.ta-nav__trigger')?.setAttribute('aria-expanded', String(open));
	};
	const closeAll = (except) => items.forEach((li) => li !== except && setOpen(li, false));

	items.forEach((li) => {
		const btn = li.querySelector('.ta-nav__trigger');
		if (!btn) return;

		btn.addEventListener('click', () => {
			const open = !li.classList.contains('is-open');
			closeAll(li);
			setOpen(li, open);
			li.classList.toggle('is-suppressed', !open);
		});
		li.addEventListener('mouseenter', () => {
			li.classList.remove('is-suppressed');
			btn.setAttribute('aria-expanded', 'true');
		});
		li.addEventListener('mouseleave', () => {
			li.classList.remove('is-suppressed');
			if (!li.classList.contains('is-open')) btn.setAttribute('aria-expanded', 'false');
		});
		li.addEventListener('focusout', (e) => {
			if (!li.contains(e.relatedTarget)) setOpen(li, false);
		});
		li.addEventListener('keydown', (e) => {
			if (e.key !== 'Escape') return;
			setOpen(li, false);
			li.classList.add('is-suppressed');
			btn.focus();
		});
	});

	document.addEventListener('click', (e) => {
		if (!e.target.closest('.ta-nav')) closeAll();
	});

	const drawer = document.getElementById('ta-drawer');
	const menuBtn = document.querySelector('.ta-icon-btn--menu');
	if (drawer && menuBtn) {
		menuBtn.setAttribute('aria-expanded', 'false');
		drawer.addEventListener('toggle', (e) => menuBtn.setAttribute('aria-expanded', String(e.newState === 'open')));
		// Same-page links (e.g. #book) should close the drawer so the target is visible.
		drawer.addEventListener('click', (e) => {
			const a = e.target.closest('a[href^="#"]');
			if (a && typeof drawer.hidePopover === 'function') drawer.hidePopover();
		});
	}
}
