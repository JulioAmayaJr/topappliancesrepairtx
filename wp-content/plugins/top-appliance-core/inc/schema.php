<?php
/**
 * JSON-LD graph: HomeAndConstructionBusiness, WebSite, WebPage, BreadcrumbList, FAQPage.
 * Rank Math's schema output is disabled (see integrations.php) so nothing is duplicated.
 * No aggregateRating / Review markup on purpose (self-serving reviews are not eligible).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', 'top_print_schema', 30 );

function top_print_schema(): void {
	if ( is_404() || is_search() || is_feed() ) {
		return;
	}
	$graph = array_values( array_filter( array(
		top_schema_business(),
		top_schema_website(),
		top_schema_webpage(),
		top_schema_breadcrumbs(),
		top_schema_faq(),
	) ) );
	if ( ! $graph ) {
		return;
	}
	$json = wp_json_encode(
		array( '@context' => 'https://schema.org', '@graph' => $graph ),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | ( WP_DEBUG ? JSON_PRETTY_PRINT : 0 )
	);
	echo "<script type=\"application/ld+json\" class=\"top-schema\">$json</script>\n"; // phpcs:ignore
}

function top_schema_id( string $frag ): string {
	return home_url( '/#' . $frag );
}

function top_attachment_url( int $id, string $size = 'full' ): string {
	return $id ? (string) wp_get_attachment_image_url( $id, $size ) : '';
}

function top_schema_business(): array {
	$name = top_biz( 'name' );
	if ( ! $name ) {
		return array();
	}
	$data = top_nav_data();
	$biz  = array(
		'@type'     => 'HomeAndConstructionBusiness',
		'@id'       => top_schema_id( 'business' ),
		'name'      => $name,
		'url'       => home_url( '/' ),
		'telephone' => top_phone_schema(),
		'email'     => top_biz( 'email' ),
		'address'   => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => top_address_line(),
			'addressLocality' => top_biz( 'city' ),
			'addressRegion'   => top_biz( 'region' ),
			'postalCode'      => top_biz( 'postal_code' ),
			'addressCountry'  => top_biz( 'country', 'US' ),
		),
	);
	if ( top_biz( 'lat' ) && top_biz( 'lng' ) ) {
		$biz['geo'] = array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => (float) top_biz( 'lat' ),
			'longitude' => (float) top_biz( 'lng' ),
		);
	}
	$hours = array();
	foreach ( top_hours_groups( false ) as $g ) {
		$hours[] = array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => array_map( fn( $d ) => 'https://schema.org/' . TOP_DAYS[ $d ][1], $g['days'] ),
			'opens'     => $g['open'],
			'closes'    => $g['close'],
		);
	}
	if ( $hours ) {
		$biz['openingHoursSpecification'] = $hours;
	}
	$areas = array_map( fn( $a ) => array( '@type' => 'City', 'name' => $a['title'] . ', ' . top_biz( 'region' ) ), $data['service_area'] ?? array() );
	if ( $areas ) {
		$biz['areaServed'] = $areas;
	}
	$same = array_values( array_filter( array( top_biz( 'facebook' ), top_biz( 'instagram' ), top_biz( 'youtube' ), top_biz( 'google_business' ) ) ) );
	if ( $same ) {
		$biz['sameAs'] = $same;
	}
	$logo = top_attachment_url( (int) top_biz( 'logo_full_id' ) );
	if ( $logo ) {
		$biz['logo'] = array( '@type' => 'ImageObject', 'url' => $logo );
	}
	$image = top_attachment_url( (int) top_biz( 'image_id' ) ) ?: $logo;
	if ( $image ) {
		$biz['image'] = $image;
	}
	if ( top_biz( 'price_range' ) ) {
		$biz['priceRange'] = top_biz( 'price_range' );
	}
	$services = $data['service'] ?? array();
	if ( $services ) {
		$biz['hasOfferCatalog'] = array(
			'@type'           => 'OfferCatalog',
			'name'            => 'Appliance repair services',
			'itemListElement' => array_map(
				fn( $s ) => array(
					'@type'         => 'Offer',
					'itemOffered'   => array(
						'@type'      => 'Service',
						'name'       => $s['title'],
						'url'        => $s['url'],
						'serviceType' => 'Appliance repair',
						'provider'   => array( '@id' => top_schema_id( 'business' ) ),
						'areaServed' => array( '@type' => 'City', 'name' => top_biz( 'city' ) . ', ' . top_biz( 'region' ) ),
					),
				),
				$services
			),
		);
	}
	return array_filter( $biz );
}

function top_schema_website(): array {
	return array(
		'@type'           => 'WebSite',
		'@id'             => top_schema_id( 'website' ),
		'url'             => home_url( '/' ),
		'name'            => top_biz( 'name' ) ?: get_bloginfo( 'name' ),
		'inLanguage'      => 'en-US',
		'publisher'       => array( '@id' => top_schema_id( 'business' ) ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array( '@type' => 'EntryPoint', 'urlTemplate' => home_url( '/?s={search_term_string}' ) ),
			'query-input' => 'required name=search_term_string',
		),
	);
}

function top_current_url(): string {
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_singular() ) {
		return (string) get_permalink();
	}
	if ( is_post_type_archive() ) {
		return (string) get_post_type_archive_link( get_query_var( 'post_type' ) ?: get_post_type() );
	}
	if ( is_home() ) {
		return (string) get_permalink( get_option( 'page_for_posts' ) );
	}
	if ( is_tax() || is_category() || is_tag() ) {
		return (string) get_term_link( get_queried_object() );
	}
	global $wp;
	return home_url( trailingslashit( $wp->request ?? '' ) );
}

function top_schema_webpage(): array {
	$url = top_current_url();
	return array(
		'@type'      => 'WebPage',
		'@id'        => $url . '#webpage',
		'url'        => $url,
		'name'       => wp_get_document_title(),
		'isPartOf'   => array( '@id' => top_schema_id( 'website' ) ),
		'about'      => array( '@id' => top_schema_id( 'business' ) ),
		'breadcrumb' => array( '@id' => $url . '#breadcrumb' ),
		'inLanguage' => 'en-US',
	);
}

/** Breadcrumb trail: [ [name, url], ... ] — also usable by templates. */
function top_breadcrumb_trail(): array {
	$trail = array( array( 'Home', home_url( '/' ) ) );
	if ( is_front_page() ) {
		return $trail;
	}
	if ( is_singular() ) {
		$post = get_queried_object();
		$pt   = get_post_type_object( $post->post_type );
		if ( 'post' === $post->post_type && get_option( 'page_for_posts' ) ) {
			$trail[] = array( get_the_title( get_option( 'page_for_posts' ) ), get_permalink( get_option( 'page_for_posts' ) ) );
		} elseif ( $pt && $pt->has_archive ) {
			$trail[] = array( $pt->labels->name, get_post_type_archive_link( $post->post_type ) );
			if ( 'error_code' === $post->post_type ) {
				$terms = get_the_terms( $post, 'appliance' );
				if ( $terms && ! is_wp_error( $terms ) ) {
					$trail[] = array( $terms[0]->name . ' error codes', get_term_link( $terms[0] ) );
				}
			}
		} elseif ( 'page' === $post->post_type ) {
			foreach ( array_reverse( get_post_ancestors( $post ) ) as $anc ) {
				$trail[] = array( get_the_title( $anc ), get_permalink( $anc ) );
			}
		}
		$trail[] = array( get_the_title( $post ), get_permalink( $post ) );
	} elseif ( is_post_type_archive() ) {
		$pt      = get_post_type_object( get_query_var( 'post_type' ) );
		$trail[] = array( $pt ? $pt->labels->name : '', top_current_url() );
	} elseif ( is_tax( 'appliance' ) ) {
		$trail[] = array( 'Error Codes', get_post_type_archive_link( 'error_code' ) );
		$trail[] = array( single_term_title( '', false ) . ' error codes', top_current_url() );
	} elseif ( is_home() ) {
		$trail[] = array( get_the_title( get_option( 'page_for_posts' ) ), top_current_url() );
	} elseif ( is_archive() ) {
		$trail[] = array( wp_strip_all_tags( get_the_archive_title() ), top_current_url() );
	}
	return $trail;
}

function top_schema_breadcrumbs(): array {
	$items = array();
	foreach ( top_breadcrumb_trail() as $i => $crumb ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $crumb[0],
			'item'     => $crumb[1],
		);
	}
	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => top_current_url() . '#breadcrumb',
		'itemListElement' => $items,
	);
}

/**
 * FAQPage from the visible FAQ: every core/details block inside a block with class "ta-faq".
 * Theme patterns referenced via core/pattern are resolved, so schema always matches the page.
 */
function top_schema_faq(): array {
	if ( ! is_singular() ) {
		return array();
	}
	$post = get_queried_object();
	$qas  = array();
	top_collect_faq( parse_blocks( $post->post_content ), false, $qas );
	if ( ! $qas ) {
		return array();
	}
	return array(
		'@type'      => 'FAQPage',
		'@id'        => top_current_url() . '#faq',
		'mainEntity' => array_map(
			fn( $qa ) => array(
				'@type'          => 'Question',
				'name'           => $qa[0],
				'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $qa[1] ),
			),
			$qas
		),
	);
}

function top_collect_faq( array $blocks, bool $in_faq, array &$qas, int $depth = 0 ): void {
	if ( $depth > 8 ) {
		return;
	}
	foreach ( $blocks as $block ) {
		$name = $block['blockName'] ?? '';
		if ( 'core/pattern' === $name && ! empty( $block['attrs']['slug'] ) ) {
			$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( $block['attrs']['slug'] );
			if ( $pattern ) {
				top_collect_faq( parse_blocks( $pattern['content'] ), $in_faq, $qas, $depth + 1 );
			}
			continue;
		}
		$classes = ' ' . ( $block['attrs']['className'] ?? '' ) . ' ';
		$here    = $in_faq || str_contains( $classes, ' ta-faq ' );
		if ( $here && 'core/details' === $name ) {
			$question = trim( wp_strip_all_tags( $block['attrs']['summary'] ?? '' ) );
			$answer   = '';
			foreach ( $block['innerBlocks'] as $inner ) {
				$answer .= render_block( $inner );
			}
			$answer = trim( preg_replace( '/\s+/', ' ', wp_kses( $answer, array( 'a' => array( 'href' => true ), 'p' => array(), 'br' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(), 'strong' => array(), 'em' => array() ) ) ) );
			if ( $question && $answer ) {
				$qas[] = array( $question, $answer );
			}
			continue;
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			top_collect_faq( $block['innerBlocks'], $here, $qas, $depth + 1 );
		}
	}
}
