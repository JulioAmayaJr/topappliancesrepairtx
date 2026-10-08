<?php
defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	remove_theme_support( 'core-block-patterns' );
	add_editor_style( array( 'assets/css/critical.css', 'assets/css/main.css' ) );
	set_post_thumbnail_size( 1200, 675, true );
	add_image_size( 'card', 640, 360, true );
	add_image_size( 'card-sm', 336, 189, true );
	add_image_size( 'w480', 480, 0 );
	add_image_size( 'w640', 640, 0 );
} );

/* Skip link: first focusable element on every page (core's JS-injected one is removed in cleanup.php). */
add_action( 'wp_body_open', function () {
	echo '<a class="skip-link screen-reader-text" href="#main">Skip to content</a>';
}, 1 );

/* Theme-owned business data never lives here: see the "Business Info" settings (top-appliance-core). */
if ( ! function_exists( 'top_biz' ) ) {
	function top_biz( string $key, $fallback = '' ) {
		return $fallback;
	}
}

/* Favicons as PNG/ICO from the theme (the Media Library would serve AVIF, which Safari can't use as an icon). */
remove_action( 'wp_head', 'wp_site_icon', 99 );
add_action( 'wp_head', function () {
	$base = TOP_THEME_URI . '/assets/images/';
	printf( '<link rel="icon" href="%s" sizes="32x32" type="image/png">' . "\n", esc_url( $base . 'favicon-32.png' ) );
	printf( '<link rel="icon" href="%s" sizes="192x192" type="image/png">' . "\n", esc_url( $base . 'favicon-192.png' ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $base . 'apple-touch-icon.png' ) );
	echo '<meta name="theme-color" content="#01274F">' . "\n";
}, 99 );
add_filter( 'feed_links_show_comments_feed', '__return_false' );
add_action( 'do_faviconico', function () {
	wp_safe_redirect( TOP_THEME_URI . '/assets/images/favicon.ico', 301 );
	exit;
} );
