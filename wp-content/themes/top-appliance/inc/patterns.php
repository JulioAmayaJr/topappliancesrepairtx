<?php
/**
 * Pattern categories + media helper used by the PHP patterns in /patterns.
 * Every Home section is a standalone pattern so the same sections can be reused on the
 * service, brand, area and error-code templates.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_block_pattern_category( 'top-sections', array( 'label' => 'Top Appliances: Sections', 'description' => 'Reusable page sections (hero, services, FAQ, CTA…).' ) );
	register_block_pattern_category( 'top-chrome', array( 'label' => 'Top Appliances: Header & footer' ) );
} );

/**
 * Media slot used by a pattern. Placeholders are imported into the Media Library by bin/seed.php
 * and mapped in the `top_media` option, so WordPress can output srcset/sizes/width/height.
 * Returns ['id'=>int,'url'=>string,'alt'=>string,'width'=>int,'height'=>int].
 */
function top_media( string $slot ): array {
	$map = (array) get_option( 'top_media', array() );
	$id  = (int) ( $map[ $slot ] ?? 0 );
	if ( $id && ( $src = wp_get_attachment_image_src( $id, 'large' ) ) ) {
		return array(
			'id'     => $id,
			'url'    => $src[0],
			'alt'    => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
			'width'  => (int) $src[1],
			'height' => (int) $src[2],
		);
	}
	return array( 'id' => 0, 'url' => '', 'alt' => '', 'width' => 0, 'height' => 0 );
}

/** core/image block markup for a media slot. */
function top_image_block( string $slot, string $class = '', string $sizes = '' ): string {
	$m = top_media( $slot );
	if ( ! $m['url'] ) {
		return '';
	}
	$attrs = array(
		'id'              => $m['id'],
		'sizeSlug'        => 'large',
		'linkDestination' => 'none',
		'className'       => $class,
	);
	if ( $sizes ) {
		$attrs['sizes'] = $sizes;
	}
	return sprintf(
		'<!-- wp:image %1$s --><figure class="wp-block-image size-large %2$s"><img src="%3$s" alt="%4$s" class="wp-image-%5$d"/></figure><!-- /wp:image -->',
		wp_json_encode( $attrs ),
		esc_attr( $class ),
		esc_url( $m['url'] ),
		esc_attr( $m['alt'] ),
		$m['id']
	);
}
