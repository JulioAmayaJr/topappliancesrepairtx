<?php
defined( 'ABSPATH' ) || exit;
$svg = top_icon( (string) ( $attributes['name'] ?? 'tools' ), '', max( 16, (int) ( $attributes['size'] ?? 28 ) ) );
if ( $svg ) {
	printf( '<span %s>%s</span>', top_block_wrapper( 'ta-ico' ), $svg ); // phpcs:ignore
}
