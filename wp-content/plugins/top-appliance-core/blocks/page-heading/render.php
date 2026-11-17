<?php
/**
 * H1 for brand and service-area pages.
 *
 * The post title stays short ("Samsung", "Katy") because menus, breadcrumbs and
 * card lists use it. The on-page H1 needs the full phrase, so it comes from the
 * `top_h1` field and falls back to the title.
 */
defined( 'ABSPATH' ) || exit;

$post_id = (int) ( $block->context['postId'] ?? get_the_ID() );
if ( ! $post_id ) {
	return;
}
$h1 = (string) get_post_meta( $post_id, 'top_h1', true );
if ( ! $h1 ) {
	$h1 = get_the_title( $post_id );
}
printf( '<h1 %s>%s</h1>', top_block_wrapper( 'ta-pagehead__title' ), esc_html( html_entity_decode( $h1, ENT_QUOTES, 'UTF-8' ) ) );
