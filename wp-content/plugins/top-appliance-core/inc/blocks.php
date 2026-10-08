<?php
/**
 * Dynamic blocks (server-rendered). Markup only — presentation lives in the theme.
 * Each block folder: blocks/<name>/block.json + render.php.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'block_categories_all', function ( array $cats ) {
	array_unshift( $cats, array( 'slug' => 'top-appliance', 'title' => 'Top Appliances' ) );
	return $cats;
} );

add_action( 'init', function () {
	wp_register_script(
		'top-core-editor',
		TOP_CORE_URL . 'assets/editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render' ),
		TOP_CORE_VERSION,
		true
	);
	$metas = array();
	foreach ( glob( TOP_CORE_DIR . 'blocks/*/block.json' ) as $file ) {
		$type = register_block_type( dirname( $file ), array( 'editor_script_handles' => array( 'top-core-editor' ) ) );
		if ( $type ) {
			$metas[] = array( 'name' => $type->name, 'attributes' => $type->attributes );
		}
	}
	wp_add_inline_script( 'top-core-editor', 'window.topCoreBlocks=' . wp_json_encode( $metas ) . ';', 'before' );
	if ( is_admin() ) {
		wp_add_inline_script( 'top-core-editor', 'window.topCoreBusiness=' . wp_json_encode( top_binding_values() ) . ';', 'before' );
	}
} );

/** Wrapper attributes helper usable from render.php files. */
function top_block_wrapper( string $class, array $extra = array() ): string {
	return get_block_wrapper_attributes( array_merge( array( 'class' => $class ), $extra ) );
}

/** Gear outline path (used by the warranty seal). */
function top_gear_path( float $cx, float $cy, float $r_out, float $r_in, int $teeth ): string {
	$pts  = array();
	$step = 2 * M_PI / $teeth;
	for ( $i = 0; $i < $teeth; $i++ ) {
		$a = $i * $step - M_PI / 2;
		foreach ( array( array( -0.30, $r_in ), array( -0.18, $r_out ), array( 0.18, $r_out ), array( 0.30, $r_in ) ) as $p ) {
			$ang   = $a + $p[0] * $step * 1.0;
			$pts[] = round( $cx + cos( $ang ) * $p[1], 2 ) . ' ' . round( $cy + sin( $ang ) * $p[1], 2 );
		}
		// Arc along the root circle to the next tooth.
		$next  = $a + 0.70 * $step;
		$pts[] = 'A' . $r_in . ' ' . $r_in . ' 0 0 1 ' . round( $cx + cos( $next ) * $r_in, 2 ) . ' ' . round( $cy + sin( $next ) * $r_in, 2 );
	}
	$d = 'M' . array_shift( $pts );
	foreach ( $pts as $p ) {
		$d .= str_starts_with( $p, 'A' ) ? $p : 'L' . $p;
	}
	return $d . 'Z';
}
