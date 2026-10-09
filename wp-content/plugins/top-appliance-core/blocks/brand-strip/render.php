<?php
/** Brand logos strip (grayscale, colour on hover), each linking to the brand page. */
defined( 'ABSPATH' ) || exit;
$items = array_filter( top_nav_data()['brand'] ?? array(), fn( $b ) => ! empty( $b['logo'] ) );
if ( ! $items ) {
	return;
}
?>
<ul <?php echo top_block_wrapper( 'ta-strip' ); ?> aria-label="Brands we repair">
	<?php foreach ( $items as $b ) : ?>
		<li><a href="<?php echo esc_url( $b['url'] ); ?>"><?php echo wp_get_attachment_image( $b['logo'], 'full', false, array( 'alt' => $b['title'] . ' appliance repair', 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '120px' ) ); ?></a></li>
	<?php endforeach; ?>
</ul>
