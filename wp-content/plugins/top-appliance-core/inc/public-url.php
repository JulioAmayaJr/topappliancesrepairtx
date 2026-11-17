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
	add_rewrite_rule( '^llms-full\.txt$', 'index.php?top_llms=full', 'top' );
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
	$which = (string) get_query_var( 'top_llms' );
	if ( '1' !== $which && 'full' !== $which ) {
		return;
	}
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'X-Robots-Tag: noindex' );
	echo 'full' === $which ? top_llms_full_txt() : top_llms_txt(); // phpcs:ignore WordPress.Security.EscapeOutput
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

/**
 * /llms-full.txt — the full text an AI assistant needs to answer about this business
 * without crawling every page: services, brands, service areas with their ZIP codes,
 * and every FAQ published on the site, in plain text.
 */
function top_llms_full_txt(): string {
	$base  = top_public_base();
	$data  = top_nav_data();
	$name  = top_biz( 'name' ) ?: 'Top Appliances Repair';
	$days  = (int) top_biz( 'warranty_days' );
	$dec   = fn( $t ) => html_entity_decode( (string) $t, ENT_QUOTES, 'UTF-8' );

	$l   = array();
	$l[] = '# ' . $name . ' — full reference';
	$l[] = '';
	$l[] = 'Appliance repair company in Houston, Texas. This file contains the complete public';
	$l[] = 'information about the business: contact details, services, brands, service areas and';
	$l[] = 'every frequently asked question published on the website.';
	$l[] = '';
	$l[] = '## Business';
	$l[] = '';
	$l[] = 'Name: ' . $name;
	if ( top_phone() ) {
		$l[] = 'Phone: ' . top_phone();
	}
	if ( top_biz( 'email' ) ) {
		$l[] = 'Email: ' . top_biz( 'email' );
	}
	$addr = trim( top_address_line() . ', ' . top_biz( 'city' ) . ', ' . top_biz( 'region' ) . ' ' . top_biz( 'postal_code' ), ', ' );
	if ( $addr ) {
		$l[] = 'Address: ' . $addr;
	}
	foreach ( top_hours_groups( false ) as $g ) {
		$l[] = 'Hours: ' . implode( ', ', array_map( fn( $d ) => TOP_DAYS[ $d ][1], $g['days'] ) ) . ' ' . $g['open'] . '–' . $g['close'];
	}
	$l[] = 'Website: ' . $base . '/';
	$l[] = 'Service model: in-home residential appliance repair, same-day visits, free estimate before any work'
		. ( $days ? ', ' . $days . '-day warranty on parts and labor' : '' ) . '.';
	$l[] = 'Independent repair company: not affiliated with or endorsed by any appliance manufacturer.';
	$l[] = '';

	$l[] = '## Services';
	$l[] = '';
	foreach ( $data['service'] ?? array() as $s ) {
		$l[] = '### ' . $dec( $s['title'] );
		$l[] = 'URL: ' . top_public_url( $s['url'] );
		$post = get_post( $s['id'] );
		if ( $post && $post->post_excerpt ) {
			$l[] = $dec( $post->post_excerpt );
		}
		$l[] = '';
	}

	$l[] = '## Brands we service';
	$l[] = '';
	foreach ( $data['brand'] ?? array() as $b ) {
		$post = get_post( $b['id'] );
		$l[]  = '- ' . $dec( $b['title'] ) . ' — ' . top_public_url( $b['url'] )
			. ( $post && $post->post_excerpt ? ' — ' . $dec( $post->post_excerpt ) : '' );
	}
	$l[] = '';

	$l[] = '## Service areas';
	$l[] = '';
	foreach ( $data['service_area'] ?? array() as $a ) {
		$county = (string) get_post_meta( $a['id'], 'top_county', true );
		$zips   = array_slice( (array) $a['zips'], 0, 30 );
		$l[]    = '### ' . $dec( $a['title'] ) . ', TX';
		$l[]    = 'URL: ' . top_public_url( $a['url'] );
		if ( $county ) {
			$l[] = 'County: ' . $county;
		}
		if ( $zips ) {
			$l[] = 'ZIP codes: ' . implode( ', ', $zips ) . ( count( (array) $a['zips'] ) > 30 ? ', and more' : '' );
		}
		$l[] = '';
	}

	$l[] = '## Frequently asked questions';
	$l[] = '';
	$ids = array();
	foreach ( array( 'service', 'brand', 'service_area' ) as $pt ) {
		foreach ( $data[ $pt ] ?? array() as $item ) {
			$ids[ $item['id'] ] = $item['title'];
		}
	}
	foreach ( get_posts( array( 'post_type' => array( 'page', 'post' ), 'posts_per_page' => 100, 'post_status' => 'publish', 'fields' => 'ids' ) ) as $id ) {
		$ids[ $id ] = get_the_title( $id );
	}
	foreach ( $ids as $id => $title ) {
		$faqs = array_filter( (array) get_post_meta( $id, 'top_faqs', true ), fn( $f ) => ! empty( $f['q'] ) && ! empty( $f['a'] ) );
		if ( ! $faqs ) {
			continue;
		}
		$l[] = '### ' . $dec( $title ) . ' (' . top_public_url( (string) get_permalink( $id ) ) . ')';
		foreach ( $faqs as $f ) {
			$l[] = 'Q: ' . $dec( wp_strip_all_tags( $f['q'] ) );
			$l[] = 'A: ' . $dec( wp_strip_all_tags( $f['a'] ) );
		}
		$l[] = '';
	}

	$l[] = '## Guides';
	$l[] = '';
	foreach ( get_posts( array( 'post_type' => 'post', 'posts_per_page' => 50, 'post_status' => 'publish' ) ) as $post ) {
		$l[] = '- ' . $dec( get_the_title( $post ) ) . ' — ' . top_public_url( (string) get_permalink( $post ) );
		if ( $post->post_excerpt ) {
			$l[] = '  ' . $dec( $post->post_excerpt );
		}
	}
	$l[] = '';
	return implode( "\n", $l );
}
