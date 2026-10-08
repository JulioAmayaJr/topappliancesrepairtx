<?php
/**
 * Images: AVIF sub-sizes, LCP hint for the hero image, lazy loading for everything else.
 */

defined( 'ABSPATH' ) || exit;

/* Generate AVIF sub-sizes from JPEG/PNG/WebP uploads when the server can encode AVIF. */
add_filter( 'image_editor_output_format', function ( array $formats ) {
	if ( wp_image_editor_supports( array( 'mime_type' => 'image/avif' ) ) ) {
		foreach ( array( 'image/jpeg', 'image/png', 'image/webp' ) as $mime ) {
			$formats[ $mime ] = 'image/avif';
		}
	}
	return $formats;
} );
add_filter( 'wp_editor_set_quality', fn( $q, $mime ) => 'image/avif' === $mime ? 62 : $q, 10, 2 );

/* Only the logo (explicitly eager) and the hero image may skip lazy loading. */
add_filter( 'wp_omit_loading_attr_threshold', fn() => 1 );

/*
 * core/image output:
 *  - class "ta-lcp" (hero): fetchpriority="high", never lazy
 *  - every other image: loading="lazy"
 *  - a `sizes` block attribute (set by the patterns) replaces WordPress' generic default
 */
add_filter( 'render_block_core/image', function ( string $html, array $block ) {
	$class = ' ' . ( $block['attrs']['className'] ?? '' ) . ' ';
	$p     = new WP_HTML_Tag_Processor( $html );
	if ( ! $p->next_tag( 'img' ) ) {
		return $html;
	}
	if ( str_contains( $class, ' ta-lcp ' ) ) {
		$p->remove_attribute( 'loading' );
		$p->set_attribute( 'fetchpriority', 'high' );
	} else {
		$p->set_attribute( 'loading', 'lazy' );
		$p->remove_attribute( 'fetchpriority' );
	}
	$p->set_attribute( 'decoding', 'async' );
	if ( ! empty( $block['attrs']['sizes'] ) ) {
		$p->set_attribute( 'sizes', $block['attrs']['sizes'] );
	}
	return $p->get_updated_html();
}, 10, 2 );

/* Never let core put fetchpriority=high on any other image once the hero claimed it. */
add_filter( 'wp_get_loading_optimization_attributes', function ( array $attr, string $tag, array $attrs ) {
	$class = ' ' . ( $attrs['class'] ?? '' ) . ' ';
	if ( str_contains( $class, ' ta-lcp ' ) ) {
		return array( 'fetchpriority' => 'high' );
	}
	unset( $attr['fetchpriority'] );
	return $attr;
}, 10, 3 );

/* Accurate `sizes` for blog cards (the card image is shown small next to the title). */
add_filter( 'render_block_core/post-featured-image', function ( string $html, array $block ) {
	$class = ' ' . ( $block['attrs']['className'] ?? '' ) . ' ';
	if ( ! str_contains( $class, ' ta-post__img ' ) ) {
		return $html;
	}
	$p = new WP_HTML_Tag_Processor( $html );
	if ( $p->next_tag( 'img' ) ) {
		$p->set_attribute( 'sizes', '(min-width: 600px) 168px, 112px' );
		$p->set_attribute( 'loading', 'lazy' );
		$html = $p->get_updated_html();
	}
	return $html;
}, 10, 2 );
