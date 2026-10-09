<?php
/**
 * Third-party integration glue: Rank Math (no duplicate schema), Fluent Forms (lean assets).
 */

defined( 'ABSPATH' ) || exit;

/* Rank Math: our plugin owns JSON-LD. Belt and braces on top of the disabled schema module. */
add_filter( 'rank_math/json_ld', '__return_empty_array', 999 );
add_filter( 'rank_math/frontend/remove_credit_notice', '__return_true' );

/* Rank Math sitemap: keep the CPTs, drop author archives. */
add_filter( 'rank_math/sitemap/exclude_post_type', function ( $exclude, $type ) {
	return in_array( $type, array( 'service', 'brand', 'service_area', 'error_code', 'post', 'page' ), true ) ? false : $exclude;
}, 10, 2 );

/* Fluent Forms: theme styles the form; drop the plugin's front-end CSS. */
add_action( 'wp_enqueue_scripts', function () {
	foreach ( array( 'fluent-form-styles', 'fluentform-public-default' ) as $h ) {
		wp_dequeue_style( $h );
	}
}, 100 );
add_action( 'wp_print_styles', function () {
	foreach ( array( 'fluent-form-styles', 'fluentform-public-default' ) as $h ) {
		wp_dequeue_style( $h );
	}
}, 100 );

/* Defer Fluent Forms and its jQuery dependency (never render-blocking, always in footer). */
add_filter( 'script_loader_tag', function ( string $tag, string $handle ) {
	$defer = array( 'jquery-core', 'jquery-migrate', 'jquery', 'fluent-form-submission', 'fluentform-advanced', 'fluent-form-validation' );
	if ( in_array( $handle, $defer, true ) && ! str_contains( $tag, ' defer' ) && ! is_admin() ) {
		$tag = str_replace( ' src=', ' defer src=', $tag );
	}
	return $tag;
}, 10, 2 );

/* Front end: jQuery (needed only by Fluent Forms) without jquery-migrate. */
add_action( 'wp_default_scripts', function ( WP_Scripts $scripts ) {
	if ( is_admin() || empty( $scripts->registered['jquery'] ) ) {
		return;
	}
	$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
} );

/* "Content pending" notes (class ta-todo) are editor notes: never shown to the public. */
add_filter( 'render_block_core/paragraph', function ( string $html, array $block ) {
	if ( str_contains( ' ' . ( $block['attrs']['className'] ?? '' ) . ' ', ' ta-todo ' ) && ! current_user_can( 'edit_posts' ) ) {
		return '';
	}
	return $html;
}, 10, 2 );
