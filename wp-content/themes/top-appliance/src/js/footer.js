/** Footer link lists render open (usable without JS); on phones they start collapsed. */
export function initFooter() {
	if (!window.matchMedia('(max-width: 767px)').matches) return;
	document.querySelectorAll('.ta-footer details.ta-fold[open]').forEach((d) => d.removeAttribute('open'));
}
