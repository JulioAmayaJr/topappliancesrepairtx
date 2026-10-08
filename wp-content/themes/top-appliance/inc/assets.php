<?php
/**
 * Front-end assets:
 *  - critical.css inlined in <head> (above-the-fold: tokens, header, hero, trust bar, mobile bar)
 *  - main.css loaded without blocking render
 *  - main.js as a deferred ES module; motion effects are a lazy chunk (skipped with reduced motion)
 *  - the self-hosted font is preloaded
 */

defined( 'ABSPATH' ) || exit;

function top_asset_ver( string $rel ): string {
	$file = TOP_THEME_DIR . '/' . $rel;
	return file_exists( $file ) ? (string) filemtime( $file ) : TOP_THEME_VERSION;
}

add_action( 'wp_head', function () {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( TOP_THEME_URI . '/assets/fonts/archivo-var.woff2' )
	);
}, 1 );

add_action( 'wp_enqueue_scripts', function () {
	$critical = TOP_THEME_DIR . '/assets/css/critical.css';
	if ( file_exists( $critical ) ) {
		wp_register_style( 'top-critical', false, array( 'global-styles' ), null ); // phpcs:ignore
		wp_enqueue_style( 'top-critical' );
		wp_add_inline_style( 'top-critical', (string) file_get_contents( $critical ) ); // phpcs:ignore
	}
	wp_enqueue_script_module( 'top-main', TOP_THEME_URI . '/assets/js/main.js', array(), top_asset_ver( 'assets/js/main.js' ) );
}, 20 );

/* Non-blocking main.css (print-media swap) + noscript fallback. */
add_action( 'wp_head', function () {
	$href = esc_url( TOP_THEME_URI . '/assets/css/main.css?ver=' . top_asset_ver( 'assets/css/main.css' ) );
	echo "<link rel=\"stylesheet\" id=\"top-main-css\" href=\"$href\" media=\"print\" onload=\"this.media='all'\">\n";
	echo "<noscript><link rel=\"stylesheet\" href=\"$href\"></noscript>\n";
}, 8 );
