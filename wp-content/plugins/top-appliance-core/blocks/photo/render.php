<?php
/** Photo from a `top_media` slot: real <img> with srcset/sizes, width/height, lazy loading. */
defined( 'ABSPATH' ) || exit;

$slot = (string) ( $attributes['slot'] ?? '' );
$id   = (int) ( ( (array) get_option( 'top_media', array() ) )[ $slot ] ?? 0 );
if ( ! $id ) {
	return;
}
$img_attr = array(
	'class'    => 'ta-photo__img',
	'loading'  => 'lazy',
	'decoding' => 'async',
);
if ( ! empty( $attributes['sizes'] ) ) {
	$img_attr['sizes'] = (string) $attributes['sizes'];
}
$img = wp_get_attachment_image( $id, 'large', false, $img_attr );
if ( ! $img ) {
	return;
}
$caption = trim( (string) ( $attributes['caption'] ?? '' ) );
?>
<figure <?php echo top_block_wrapper( 'ta-photo' ); ?>>
	<?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<?php if ( $caption ) : ?>
		<figcaption class="ta-photo__caption"><?php echo esc_html( $caption ); ?></figcaption>
	<?php endif; ?>
</figure>
