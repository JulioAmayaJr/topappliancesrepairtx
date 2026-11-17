<?php
/**
 * Block markup builders for brand and service-area pages.
 *
 * Service pages are hand-written (seed/services/*.html) because each one is bespoke.
 * Brands and areas are structured: the facts live in seed/brands.json and seed/areas.json
 * and this file composes them into the same block vocabulary the rest of the site uses,
 * so one source of truth drives the page, the internal links and the FAQ schema.
 */

defined( 'ABSPATH' ) || exit;

/** Escape text for block markup (content is authored in the seed files, not by users). */
function top_b_text( string $t ): string {
	return esc_html( $t );
}

function top_b_paragraph( string $text, string $class = '' ): string {
	$attrs = $class ? ' {"className":"' . $class . '"}' : '';
	$cls   = $class ? ' class="' . esc_attr( $class ) . '"' : '';
	return "<!-- wp:paragraph$attrs -->\n<p$cls>" . $text . "</p>\n<!-- /wp:paragraph -->";
}

function top_b_heading( string $text, int $level = 2 ): string {
	$attrs = 2 === $level ? '' : ' {"level":' . $level . '}';
	return "<!-- wp:heading$attrs -->\n<h$level class=\"wp-block-heading\">" . top_b_text( $text ) . "</h$level>\n<!-- /wp:heading -->";
}

/** Unordered list of links: [ [url, label], ... ]. */
function top_b_link_list( array $links, string $class = 'ta-links ta-links--chips' ): string {
	$items = '';
	foreach ( $links as $l ) {
		$items .= '<!-- wp:list-item -->' . "\n" . '<li><a href="' . esc_url( $l[0] ) . '">' . top_b_text( $l[1] ) . '</a></li>' . "\n" . '<!-- /wp:list-item -->' . "\n\n";
	}
	return '<!-- wp:list {"className":"' . $class . '"} -->' . "\n" . '<ul class="wp-block-list ' . esc_attr( $class ) . '">' . rtrim( $items ) . '</ul>' . "\n" . '<!-- /wp:list -->';
}

/** Section wrapper. */
function top_b_section( string $inner, string $anchor, string $class ): string {
	return '<!-- wp:group {"anchor":"' . $anchor . '","tagName":"section","className":"ta-section ' . $class . '","layout":{"type":"default"}} -->' . "\n"
		. '<section id="' . esc_attr( $anchor ) . '" class="wp-block-group ta-section ' . esc_attr( $class ) . '"><!-- wp:group {"className":"ta-wrap","layout":{"type":"default"}} -->' . "\n"
		. '<div class="wp-block-group ta-wrap">' . $inner . '</div>' . "\n"
		. '<!-- /wp:group --></section>' . "\n" . '<!-- /wp:group -->';
}

/** Problem cards grid (same component as the service pages). */
function top_b_problems( array $problems, string $eyebrow, string $h2, string $lede ): string {
	$head = '<!-- wp:group {"className":"ta-section__head ta-section__head--center","layout":{"type":"default"}} -->' . "\n"
		. '<div class="wp-block-group ta-section__head ta-section__head--center">'
		. top_b_paragraph( top_b_text( $eyebrow ), 'ta-eyebrow' ) . "\n\n"
		. top_b_heading( $h2 ) . "\n\n"
		. top_b_paragraph( top_b_text( $lede ), 'ta-section__lede' )
		. '</div>' . "\n" . '<!-- /wp:group -->';
	$cards = '';
	foreach ( $problems as $p ) {
		$cards .= '<!-- wp:group {"className":"ta-problem","layout":{"type":"default"}} -->' . "\n"
			. '<div class="wp-block-group ta-problem">'
			. top_b_heading( $p['t'], 3 ) . "\n\n"
			. top_b_paragraph( top_b_text( $p['p'] ) )
			. '</div>' . "\n" . '<!-- /wp:group -->' . "\n\n";
	}
	$grid = '<!-- wp:group {"className":"ta-problems","layout":{"type":"default"}} -->' . "\n"
		. '<div class="wp-block-group ta-problems">' . rtrim( $cards ) . '</div>' . "\n" . '<!-- /wp:group -->';
	return $head . "\n\n" . $grid;
}

/** FAQ block bound to the post's top_faqs field. */
function top_b_faqs( string $heading, string $lede ): string {
	return '<!-- wp:top/faqs ' . wp_json_encode( array( 'heading' => $heading, 'lede' => $lede ) ) . ' /-->';
}

/* ------------------------------------------------------------------- Brands */

function top_build_brand_content( array $d, array $services ): string {
	$title = $d['title'];

	// 1. Direct answer + what we repair for this brand + brand-specific notes.
	$article  = top_b_paragraph( top_b_text( $d['lede'] ), 'ta-lede' ) . "\n\n";
	$article .= top_b_heading( sprintf( '%s appliances we repair', $title ) ) . "\n\n";
	$links    = array();
	foreach ( $d['appliances'] as $slug ) {
		if ( isset( $services[ $slug ] ) ) {
			$links[] = array( '/services/' . $slug . '/', $services[ $slug ] );
		}
	}
	$article .= top_b_link_list( $links ) . "\n\n";
	foreach ( $d['notes'] as $n ) {
		$article .= top_b_heading( $n['h'] ) . "\n\n" . top_b_paragraph( top_b_text( $n['p'] ) ) . "\n\n";
	}
	$article .= top_b_heading( 'Where we repair ' . $title . ' appliances' ) . "\n\n";
	$article .= top_b_paragraph(
		sprintf(
			'We cover %s and the surrounding cities, including %s. Same-day service, a free estimate before any work and a 90-day warranty on parts and labor apply everywhere we go.',
			'<a href="/service-areas/houston/">Houston</a>',
			'<a href="/service-areas/katy/">Katy</a>, <a href="/service-areas/sugar-land/">Sugar Land</a>, <a href="/service-areas/cypress/">Cypress</a>, <a href="/service-areas/the-woodlands/">The Woodlands</a> and <a href="/service-areas/">11 more</a>'
		)
	);

	$out  = top_b_section(
		'<!-- wp:group {"className":"ta-article","layout":{"type":"default"}} -->' . "\n"
		. '<div class="wp-block-group ta-article">' . $article . '</div>' . "\n" . '<!-- /wp:group -->',
		'about-the-brand',
		'ta-svc-article'
	);
	$out .= "\n\n" . top_b_section(
		top_b_problems(
			$d['problems'],
			'Common problems',
			sprintf( 'Common %s problems we fix', $title ),
			sprintf( 'The faults Houston homeowners call us about most on %s appliances, and what usually causes them.', $title )
		),
		'common-problems',
		'ta-section--tint ta-svc-problems'
	);
	$out .= "\n\n" . top_b_faqs(
		sprintf( '%s repair questions', $title ),
		sprintf( 'Answers to what Houston homeowners ask us about %s repair.', $title )
	);
	return $out;
}

/* -------------------------------------------------------------------- Areas */

function top_build_area_content( array $d, array $services, array $brands, array $area_titles, array $seed_area ): string {
	$title = $d['title'];

	$article  = top_b_paragraph( top_b_text( $d['lede'] ), 'ta-lede' ) . "\n\n";
	foreach ( $d['notes'] as $n ) {
		$article .= top_b_heading( $n['h'] ) . "\n\n" . top_b_paragraph( top_b_text( $n['p'] ) ) . "\n\n";
	}
	$article .= top_b_heading( sprintf( 'Appliances we repair in %s', $title ) ) . "\n\n";
	$links = array();
	foreach ( $services as $slug => $name ) {
		$links[] = array( '/services/' . $slug . '/', $name );
	}
	$article .= top_b_link_list( $links ) . "\n\n";

	$article .= top_b_heading( sprintf( 'Brands we service in %s', $title ) ) . "\n\n";
	$blinks = array();
	foreach ( $brands as $slug => $name ) {
		$blinks[] = array( '/brands/' . $slug . '/', $name );
	}
	$article .= top_b_link_list( $blinks ) . "\n\n";

	// ZIP codes are real data from seed/content.json: useful, specific and verifiable.
	$zips = array_slice( (array) ( $seed_area['zips'] ?? array() ), 0, 24 );
	if ( $zips ) {
		$article .= top_b_heading( sprintf( 'ZIP codes we cover in %s', $title ) ) . "\n\n";
		$article .= top_b_paragraph(
			sprintf(
				'%s is in %s County. We serve %s%s. Not sure if you are inside the area? Call us and we will tell you straight away.',
				top_b_text( $title ),
				top_b_text( (string) ( $seed_area['county'] ?? '' ) ),
				top_b_text( implode( ', ', $zips ) ),
				count( (array) $seed_area['zips'] ) > 24 ? ' and nearby ZIP codes' : ''
			)
		) . "\n\n";
	}

	$nearby = array();
	foreach ( $d['nearby'] as $slug ) {
		if ( isset( $area_titles[ $slug ] ) ) {
			$nearby[] = array( '/service-areas/' . $slug . '/', $area_titles[ $slug ] );
		}
	}
	if ( $nearby ) {
		$article .= top_b_heading( 'Nearby areas we also serve' ) . "\n\n" . top_b_link_list( $nearby );
	}

	$out  = top_b_section(
		'<!-- wp:group {"className":"ta-article","layout":{"type":"default"}} -->' . "\n"
		. '<div class="wp-block-group ta-article">' . $article . '</div>' . "\n" . '<!-- /wp:group -->',
		'about-the-area',
		'ta-svc-article'
	);
	$out .= "\n\n" . top_b_faqs(
		sprintf( 'Appliance repair in %s: common questions', $title ),
		sprintf( 'What %s homeowners ask us most before booking a repair.', $title )
	);
	return $out;
}

/* ------------------------------------------------------ Related reading */

/**
 * "Related reading" section linking a service or brand page to the blog article
 * written for it, so the link exists in both directions.
 */
function top_build_related_reading( array $items ): string {
	if ( ! $items ) {
		return '';
	}
	$links = array();
	foreach ( $items as $it ) {
		$links[] = array( '/' . $it[0] . '/', $it[1] );
	}
	$inner = top_b_heading( 'Related reading', 2 ) . "\n\n"
		. top_b_paragraph( 'Troubleshooting guides written by our technicians for the problems people ask about most.' ) . "\n\n"
		. top_b_link_list( $links, 'ta-links ta-links--chips' );
	return top_b_section( $inner, 'related-reading', 'ta-section--tint ta-svc-article' );
}
