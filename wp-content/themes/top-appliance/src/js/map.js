/** Map facade: swap the static map for the Google Maps iframe only after a click. */
export function initMap() {
	document.querySelectorAll('[data-map-facade] .ta-map__load').forEach((link) => {
		link.addEventListener('click', (e) => {
			const src = link.dataset.embed;
			if (!src) return; // no embed configured: follow the link to Google Maps
			e.preventDefault();
			const frame = link.closest('[data-map-facade]').querySelector('.ta-map__frame');
			const iframe = document.createElement('iframe');
			iframe.src = src;
			iframe.title = link.dataset.title || 'Google map';
			iframe.allowFullscreen = true;
			iframe.referrerPolicy = 'no-referrer-when-downgrade';
			frame.appendChild(iframe);
			frame.classList.add('is-live');
			link.remove();
			iframe.focus();
		});
	});
}
