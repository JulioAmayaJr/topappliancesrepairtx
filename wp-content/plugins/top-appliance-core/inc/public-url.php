<?php
/**
 * Public (canonical) URL + llms.txt.
 *
 * While the site is staged on an IP:port, `home_url()` must keep pointing at the staging
 * address so the site stays browsable, but everything a search engine or AI crawler reads
 * — canonical, og:url, sitemap <loc>, robots.txt Sitemap line, JSON-LD and llms.txt —
 * must already use the final domain. The `top_public_url` option (set by bin/setup.sh from
 * PUBLIC_URL in .env) holds that domain. Once the domain itself serves the site, option and
 * home_url() match and every filter below becomes a no-op.
 */

defined( 'ABSPATH' ) || exit;

/** Public base URL, no trailing slash. Falls back to home_url() when the option is empty. */
function top_public_base(): string {
	$base = untrailingslashit( trim( (string) get_option( 'top_public_url', '' ) ) );
	return $base ?: untrailingslashit( home_url() );
}

/** Swap the internal home URL for the public one inside any URL or text. */
function top_swap_home( string $text ): string {
	$home   = untrailingslashit( home_url() );
	$public = top_public_base();
	if ( ! $home || $home === $public ) {
		return $text;
	}
	return str_replace( $home, $public, $text );
}

/** A permalink (or any internal URL) expressed on the public domain. */
function top_public_url( string $url ): string {
	return top_swap_home( $url );
}

/* Canonical + Open Graph URL (og:url derives from the canonical, filtered as well for safety). */
add_filter( 'rank_math/frontend/canonical', 'top_swap_home', 20 );
add_filter( 'rank_math/opengraph/url', 'top_swap_home', 20 );

/* robots.txt: whatever WP/Rank Math emit (the Sitemap line uses home_url), always on the domain. */
add_filter( 'robots_txt', 'top_swap_home', 99 );

/*
 * Sitemap: Rank Math builds index and entry URLs from home_url() in several places,
 * so the reliable fix is to rewrite the whole XML response. The buffer callback runs
 * when Rank Math flushes its output and exits.
 */
add_action( 'parse_query', function ( $query ) {
	// Rank Math serves the sitemap from parse_query priority 1 and exits; buffer just before it.
	if ( $query->is_main_query() && ( '' !== (string) $query->get( 'sitemap' ) || '' !== (string) $query->get( 'xsl' ) ) ) {
		ob_start( 'top_swap_home' );
	}
}, 0 );

/* ------------------------------------------------------------------ llms.txt */
/*
 * Plain-text summary for generative engines (ChatGPT, Perplexity, Gemini, AI Overviews):
 * who the business is, the services, the cities and the key URLs, always on the
 * public domain. Served at /llms.txt.
 */
add_action( 'init', function () {
	add_rewrite_rule( '^llms\.txt$', 'index.php?top_llms=1', 'top' );
} );
add_filter( 'query_vars', function ( array $vars ) {
	$vars[] = 'top_llms';
	return $vars;
} );
/* No trailing-slash canonical redirect for /llms.txt. */
add_filter( 'redirect_canonical', function ( $redirect ) {
	return get_query_var( 'top_llms' ) ? false : $redirect;
} );
add_action( 'template_redirect', function () {
	if ( '1' !== get_query_var( 'top_llms' ) ) {
		return;
	}
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'X-Robots-Tag: noindex' );
	echo top_llms_txt(); // phpcs:ignore WordPress.Security.EscapeOutput
	exit;
} );

function top_llms_txt(): string {
	$base  = top_public_base();
	$data  = top_nav_data();
	$name  = top_biz( 'name' ) ?: 'Top Appliances Repair';
	$phone = top_phone();
	$addr  = trim( top_address_line() . ', ' . top_biz( 'city' ) . ', ' . top_biz( 'region' ) . ' ' . top_biz( 'postal_code' ), ', ' );
	$days  = (int) top_biz( 'warranty_days' );

	$lines   = array();
	$lines[] = '# ' . $name;
	$lines[] = '';
	$lines[] = '> ' . $name . ' is a local appliance repair company in Houston, TX. We repair refrigerators, washers, dryers, dishwashers, stoves, ovens, cooktops, microwaves, freezers, ice makers and garbage disposals for all major brands, including Sub-Zero, with same-day service, a free estimate before any work starts' . ( $days ? ' and a ' . $days . '-day warranty on parts and labor' : '' ) . '.';
	$lines[] = '';
	if ( $phone ) {
		$lines[] = '- Phone: ' . $phone;
	}
	if ( top_biz( 'email' ) ) {
		$lines[] = '- Email: ' . top_biz( 'email' );
	}
	if ( $addr ) {
		$lines[] = '- Address: ' . $addr;
	}
	$hours = array();
	foreach ( top_hours_groups( false ) as $g ) {
		$hours[] = implode( ', ', array_map( fn( $d ) => TOP_DAYS[ $d ][1], $g['days'] ) ) . ' ' . $g['open'] . '–' . $g['close'];
	}
	if ( $hours ) {
		$lines[] = '- Hours: ' . implode( '; ', $hours );
	}
	$lines[] = '- Website: ' . $base . '/';
	$lines[] = '';
	$lines[] = '## Services';
	$lines[] = '';
	foreach ( $data['service'] ?? array() as $s ) {
		$lines[] = '- [' . html_entity_decode( $s['title'], ENT_QUOTES, 'UTF-8' ) . '](' . top_public_url( $s['url'] ) . ')' . ( ! empty( $s['tagline'] ) ? ': ' . $s['tagline'] : '' );
	}
	$lines[] = '';
	$lines[] = '## Service areas';
	$lines[] = '';
	foreach ( $data['service_area'] ?? array() as $a ) {
		$lines[] = '- [' . $a['title'] . ', TX](' . top_public_url( $a['url'] ) . ')';
	}
	$lines[] = '';
	$lines[] = '## Brands we service';
	$lines[] = '';
	foreach ( $data['brand'] ?? array() as $b ) {
		$lines[] = '- [' . $b['title'] . '](' . top_public_url( $b['url'] ) . ')';
	}
	$lines[] = '';
	$lines[] = '## Key pages';
	$lines[] = '';
	$lines[] = '- [All services](' . $base . '/services/)';
	$lines[] = '- [Brands](' . $base . '/brands/)';
	$lines[] = '- [Service areas](' . $base . '/service-areas/)';
	$lines[] = '- [Appliance repair tips (blog)](' . $base . '/blog/)';
	$lines[] = '- [About](' . $base . '/about/)';
	$lines[] = '- [Contact / book service](' . $base . '/contact/)';
	$lines[] = '';
	return implode( "\n", $lines );
}
