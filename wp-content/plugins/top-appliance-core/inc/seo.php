<?php
/**
 * Title and robots rules on top of Rank Math.
 *
 * WHY: a 2026-11-17 audit found 18 titles over 60 characters and two duplicate
 * pairs (an archive and its /page/2/). Rank Math's title templates are set in
 * bin/configure.php; this file enforces the length budget and makes paginated
 * archives distinct and non-indexable, which is where the duplicates came from.
 */

defined( 'ABSPATH' ) || exit;

const TOP_TITLE_MAX = 60;

/**
 * Keep every title at TOP_TITLE_MAX or less without ever cutting the part that
 * carries the keyword. The page-specific text always comes first, so we drop the
 * " | Top Appliances Repair" suffix before touching anything else, and only then
 * trim on a word boundary.
 */
function top_fit_title( string $title, int $budget = TOP_TITLE_MAX ): string {
	$title = trim( html_entity_decode( $title, ENT_QUOTES, 'UTF-8' ) );
	if ( top_title_len( $title ) <= $budget ) {
		return $title;
	}
	$sep   = ' ' . ( top_title_separator() ) . ' ';
	$parts = explode( $sep, $title );
	if ( count( $parts ) > 1 ) {
		array_pop( $parts );
		$title = implode( $sep, $parts );
	}
	if ( top_title_len( $title ) <= $budget ) {
		return $title;
	}
	// Still long: trim on a word boundary, measuring the escaped string so an
	// entity such as &amp; is counted the way SEO auditors count it.
	while ( top_title_len( $title ) > $budget && str_contains( $title, ' ' ) ) {
		$title = mb_substr( $title, 0, (int) mb_strrpos( $title, ' ' ) );
	}
	return rtrim( $title, " \t\n\r\0\x0B-–—,:;|" );
}

/** Title length as an auditor counts it: HTML-escaped, so "&" costs five characters. */
function top_title_len( string $title ): int {
	return mb_strlen( htmlspecialchars( $title, ENT_QUOTES, 'UTF-8' ) );
}

function top_title_separator(): string {
	$opts = (array) get_option( 'rank-math-options-titles', array() );
	return (string) ( $opts['title_separator'] ?? '|' );
}

/** Page number of the current request (1 when not paginated). */
function top_page_number(): int {
	return max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
}

add_filter( 'rank_math/frontend/title', function ( $title ) {
	$title = (string) $title;
	$page  = top_page_number();
	if ( $page > 1 ) {
		// Distinct title per page so an archive and its /page/2/ never collide.
		$suffix = sprintf( ' – Page %d', $page );
		return top_fit_title( $title, TOP_TITLE_MAX - mb_strlen( $suffix ) ) . $suffix;
	}
	return top_fit_title( $title );
}, 20 );

/*
 * Paginated archives are thin, near-duplicate listings: keep them crawlable so the
 * links are followed, but out of the index.
 */
add_filter( 'rank_math/frontend/robots', function ( $robots ) {
	if ( top_page_number() > 1 ) {
		$robots = array_diff( (array) $robots, array( 'index' ) );
		$robots['index']  = 'noindex';
		$robots['follow'] = 'follow';
	}
	return $robots;
}, 20 );

/* Same rule for the sitemap: paginated URLs must not be listed. */
add_filter( 'rank_math/sitemap/entry', function ( $url ) {
	if ( is_array( $url ) && ! empty( $url['loc'] ) && preg_match( '#/page/\d+/?$#', (string) $url['loc'] ) ) {
		return false;
	}
	return $url;
}, 10 );
