<?php
/**
 * Custom SVG icon set. 24×24 grid, 1.75 stroke, round caps — drawn for this site.
 * Usage: echo top_icon( 'refrigerator' );
 */

defined( 'ABSPATH' ) || exit;

function top_icon_paths(): array {
	return array(
		// Appliances.
		'refrigerator'     => '<rect x="5" y="2" width="14" height="20" rx="2.5"/><path d="M5 9.5h14M8.5 5v2M8.5 12.5v4"/>',
		'washer'           => '<rect x="4" y="2" width="16" height="20" rx="2.5"/><path d="M4 6.5h16M14.5 4.25h2.5"/><circle cx="7.5" cy="4.25" r=".9" fill="currentColor" stroke="none"/><circle cx="10.25" cy="4.25" r=".9" fill="currentColor" stroke="none"/><circle cx="12" cy="14" r="5"/><path d="M8.6 14.6c1.2-1 2.2-1 3.4 0s2.2 1 3.4 0"/>',
		'dryer'            => '<rect x="4" y="2" width="16" height="20" rx="2.5"/><path d="M4 6.5h16M7 4.25h4"/><circle cx="16" cy="4.25" r="1.1"/><circle cx="12" cy="14" r="5"/><circle cx="12" cy="14" r="1.75"/>',
		'dishwasher'       => '<rect x="4" y="2" width="16" height="20" rx="2.5"/><path d="M4 7h16M9.5 4.5h5M7.5 18.5h9M8.5 18.5V14a1.5 1.5 0 0 1 3 0v4.5M12.5 18.5V14a1.5 1.5 0 0 1 3 0v4.5"/>',
		'stove-oven'       => '<rect x="3.5" y="2.5" width="17" height="19" rx="2.5"/><path d="M3.5 9h17M15 5.75h2.5"/><circle cx="7.5" cy="5.75" r="1.1"/><circle cx="11" cy="5.75" r="1.1"/><rect x="7" y="12.5" width="10" height="5.5" rx="1"/>',
		'cooktop'          => '<rect x="2.5" y="5" width="19" height="14" rx="2.5"/><circle cx="8" cy="12" r="3.25"/><circle cx="8" cy="12" r="1"/><circle cx="16.5" cy="9.5" r="2"/><circle cx="16.5" cy="15.25" r="1.6"/>',
		'ice-maker'        => '<path d="M12 2.75 19.5 7v8.5L12 19.75 4.5 15.5V7z"/><path d="M4.5 7 12 11.25 19.5 7M12 11.25v8.5M7.6 10.2l1.9 1.1"/>',
		'freezer'          => '<rect x="5" y="2" width="14" height="20" rx="2.5"/><path d="M12 7.5v9M8.1 9.75l7.8 4.5M15.9 9.75l-7.8 4.5M10.6 7.9 12 9.3l1.4-1.4M10.6 16.1 12 14.7l1.4 1.4"/>',
		'microwave'        => '<rect x="2.5" y="4.5" width="19" height="15" rx="2.5"/><rect x="5.5" y="7.5" width="10" height="9" rx="1.25"/><path d="M18.5 8v.01M18.5 11v.01M18.5 14v2.5"/>',
		'garbage-disposal' => '<path d="M3 4.5h18M10 4.5V7M14 4.5V7"/><rect x="7" y="7" width="10" height="12.5" rx="3"/><path d="M17 12h2.5a1.5 1.5 0 0 1 1.5 1.5V16M9.75 13.25l4.5-1.5M9.75 16.25l4.5-1.5"/>',
		'dryer-vent'       => '<rect x="2.5" y="5" width="9" height="14" rx="1.5"/><path d="M5 9h4M5 12h4M5 15h4M14 9h4.5a2 2 0 1 0-2-2M14 12.5h7M14 16h3.5a2 2 0 1 1-2 2"/>',
		// UI.
		'phone'            => '<path d="M21 16.4v2.9a1.9 1.9 0 0 1-2.1 1.9 18.8 18.8 0 0 1-8.2-2.9 18.5 18.5 0 0 1-5.7-5.7A18.8 18.8 0 0 1 2.1 4.4 1.9 1.9 0 0 1 4 2.3h2.9a1.9 1.9 0 0 1 1.9 1.6c.1.9.4 1.8.7 2.7a1.9 1.9 0 0 1-.4 2L7.8 9.8a15.2 15.2 0 0 0 5.7 5.7l1.2-1.2a1.9 1.9 0 0 1 2-.4c.9.3 1.8.6 2.7.7a1.9 1.9 0 0 1 1.6 1.8z"/>',
		'calendar'         => '<rect x="3.5" y="4.5" width="17" height="16" rx="2.5"/><path d="M3.5 9.5h17M8 2.5v4M16 2.5v4M8.75 14.75l2 2 4.5-4.5"/>',
		'clock'            => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
		'shield'           => '<path d="M12 2.5 4.5 5.5v6c0 4.6 3.1 8.3 7.5 10 4.4-1.7 7.5-5.4 7.5-10v-6z"/><path d="m8.5 12 2.5 2.5 4.5-5"/>',
		'medal'            => '<circle cx="12" cy="9" r="6"/><path d="m8.6 13.9-1.6 7.6 5-2.6 5 2.6-1.6-7.6M12 6.5v5M10.5 7.5l1.5-1"/>',
		'clipboard'        => '<path d="M9 3.5H6.5A1.5 1.5 0 0 0 5 5v15a1.5 1.5 0 0 0 1.5 1.5h11A1.5 1.5 0 0 0 19 20V5a1.5 1.5 0 0 0-1.5-1.5H15"/><rect x="9" y="2" width="6" height="3.5" rx="1"/><path d="M8.5 11h7M8.5 14.5h7M8.5 18h4"/>',
		'star'             => '<path fill="currentColor" stroke="none" d="m12 2.8 2.8 5.7 6.3.9-4.6 4.4 1.1 6.3L12 17.1l-5.6 3 1.1-6.3L2.9 9.4l6.3-.9z"/>',
		'map-pin'          => '<path d="M12 21.5s-7-6.1-7-11.5a7 7 0 0 1 14 0c0 5.4-7 11.5-7 11.5z"/><circle cx="12" cy="10" r="2.5"/>',
		'check'            => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
		'chevron-down'     => '<path d="m6 9 6 6 6-6"/>',
		'menu'             => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'            => '<path d="M6 6l12 12M18 6 6 18"/>',
		'mail'             => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 6.5 8.5 6.5 8.5-6.5"/>',
		'search'           => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
		'power'            => '<path d="M12 3v8M7.05 6.05a7.5 7.5 0 1 0 9.9 0"/>',
		'book'             => '<path d="M4 5.5A1.5 1.5 0 0 1 5.5 4H10a2 2 0 0 1 2 2v14a1.5 1.5 0 0 0-1.5-1.5h-5A1 1 0 0 1 4 17.5zM20 5.5A1.5 1.5 0 0 0 18.5 4H14a2 2 0 0 0-2 2v14a1.5 1.5 0 0 1 1.5-1.5h5a1 1 0 0 0 1-1z"/>',
		'error-code'       => '<rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M10 9H7v6h3M7 12h2.5M13.5 12h3.5"/>',
		'tools'            => '<path d="M14.5 6.5a4 4 0 0 0 5 5l1.5-1.5-1-3-3-1zM14.5 6.5 4 17a2.1 2.1 0 0 0 3 3l10.5-10.5"/>',
		'grid'             => '<rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/>',
		'facebook'         => '<path fill="currentColor" stroke="none" d="M13.5 21.5v-7.6h2.6l.4-3h-3V9c0-.9.3-1.5 1.5-1.5h1.6V4.8a21 21 0 0 0-2.3-.1c-2.3 0-3.9 1.4-3.9 4v2.2H7.8v3h2.6v7.6z"/>',
		'instagram'        => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.3" cy="6.7" r="1" fill="currentColor" stroke="none"/>',
		'youtube'          => '<path fill="currentColor" stroke="none" fill-rule="evenodd" d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2 26 26 0 0 0 2 12a26 26 0 0 0 .4 4.8 2.5 2.5 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8A26 26 0 0 0 22 12a26 26 0 0 0-.4-4.8zM10 15V9l5.2 3z"/>',
		'google'           => '<path fill="#4285F4" stroke="none" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" stroke="none" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/><path fill="#FBBC05" stroke="none" d="M5.84 14.09A6.6 6.6 0 0 1 5.49 12c0-.73.13-1.43.35-2.09V7.07H2.18A11 11 0 0 0 1 12c0 1.78.43 3.45 1.18 4.93z"/><path fill="#EA4335" stroke="none" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15A10.6 10.6 0 0 0 12 1 11 11 0 0 0 2.18 7.07l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/>',
	);
}

/**
 * Inline SVG icon. Decorative by default (aria-hidden); the surrounding element carries the label.
 */
function top_icon( string $name, string $class = '', int $size = 24 ): string {
	$paths = top_icon_paths();
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="ta-icon%s" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%s</svg>',
		$class ? ' ' . esc_attr( $class ) : '',
		$size,
		$size,
		$paths[ $name ]
	);
}

/** Icon choices for the service "icon" field. */
function top_icon_choices(): array {
	return array(
		'refrigerator'     => 'Refrigerator',
		'washer'           => 'Washer',
		'dryer'            => 'Dryer',
		'dishwasher'       => 'Dishwasher',
		'stove-oven'       => 'Stove & oven',
		'cooktop'          => 'Cooktop',
		'ice-maker'        => 'Ice maker',
		'freezer'          => 'Freezer',
		'microwave'        => 'Microwave',
		'garbage-disposal' => 'Garbage disposal',
		'dryer-vent'       => 'Dryer vent',
		'tools'            => 'Generic (tools)',
	);
}
