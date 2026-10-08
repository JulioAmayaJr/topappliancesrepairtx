/** Instant ZIP check (the same form also works without JS through ?zip=). */
const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

export function initZip() {
	document.querySelectorAll('[data-zip-checker]').forEach((form) => {
		let data;
		try {
			data = JSON.parse(form.querySelector('.ta-zip__data')?.textContent || '{}');
		} catch {
			return;
		}
		const input = form.querySelector('input[name="zip"]');
		const msg = form.querySelector('.ta-zip__msg');
		if (!input || !msg || !data.zips) return;

		input.addEventListener('input', () => {
			input.value = input.value.replace(/\D/g, '').slice(0, 5);
		});

		form.addEventListener('submit', (e) => {
			e.preventDefault();
			const zip = input.value.replace(/\D/g, '');
			const call = `<a href="${esc(data.tel)}">Call ${esc(data.phone)}</a>`;
			let state;
			let html;
			if (zip.length !== 5) {
				state = 'error';
				html = `Enter a 5-digit ZIP code, for example ${esc(data.ex)}.`;
			} else if (Object.prototype.hasOwnProperty.call(data.zips, zip)) {
				state = 'yes';
				const place = data.zips[zip] ? ` (${esc(data.zips[zip])})` : '';
				html = `Yes, we cover ${zip}${place}. ${call} or <a href="${esc(data.book)}">book online</a>.`;
			} else {
				state = 'no';
				html = `${zip} is outside our usual route. ${call} and we will tell you if a technician can reach you.`;
			}
			input.toggleAttribute('aria-invalid', state === 'error');
			if (state === 'error') input.setAttribute('aria-invalid', 'true');
			msg.dataset.state = state;
			msg.innerHTML = html;
			if (state === 'error') input.focus();
		});
	});
}
