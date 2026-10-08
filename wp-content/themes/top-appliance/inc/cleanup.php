<?php
/**
 * Remove what the site does not use: emoji, oEmbed, comments, unused core CSS/JS, head noise.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	// Emoji.
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'admin_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );

	// oEmbed discovery / host JS.
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action( 'rest_api_init', 'wp_oembed_register_route' );

	// Head noise.
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'feed_links_extra', 3 );

	// Core's JS-injected skip link (theme prints a real one in wp_body_open).
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_block_template_skip_link' );
	remove_action( 'wp_footer', 'the_block_template_skip_link' );
} );

/* Remove the emoji CDN DNS prefetch. */
add_filter( 'wp_resource_hints', function ( array $urls, string $type ) {
	if ( 'dns-prefetch' === $type ) {
		$urls = array_filter( $urls, fn( $u ) => ! str_contains( is_array( $u ) ? ( $u['href'] ?? '' ) : $u, 's.w.org' ) );
	}
	return $urls;
}, 10, 2 );

/* Unused core styles/scripts on the front end. */
add_action( 'wp_enqueue_scripts', function () {
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_deregister_script( 'wp-embed' );
	if ( ! is_user_logged_in() ) {
		wp_dequeue_style( 'dashicons' );
	}
}, 100 );

/* Load only the CSS of blocks actually rendered, inlined (no extra requests). */
add_filter( 'should_load_separate_core_block_assets', '__return_true' );
add_filter( 'styles_inline_size_limit', fn() => 60000 );

/* Comments: off everywhere. */
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'wp_headers', function ( array $h ) {
	unset( $h['X-Pingback'] );
	return $h;
} );
add_action( 'admin_menu', function () {
	remove_menu_page( 'edit-comments.php' );
} );
add_action( 'init', function () {
	foreach ( get_post_types() as $pt ) {
		if ( post_type_supports( $pt, 'comments' ) ) {
			remove_post_type_support( $pt, 'comments' );
			remove_post_type_support( $pt, 'trackbacks' );
		}
	}
}, 100 );
add_action( 'wp_before_admin_bar_render', function () {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu( 'comments' );
} );
