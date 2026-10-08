<?php
/** Brand wall: brand names as nameplates (logo placeholders until licensed logos are supplied). */
defined( 'ABSPATH' ) || exit;
$items = top_nav_data()['brand'] ?? array();
if ( ! empty( $attributes['featuredOnly'] ) ) {
	$items = array_values( array_filter( $items, fn( $b ) => $b['featured'] ) );
}
$limit = (int) ( $attributes['limit'] ?? 0 );
if ( $limit > 0 ) {
	$items = array_slice( $items, 0, $limit );
}
if ( ! $items ) {
	return;
}
?>
<ul <?php echo top_block_wrapper( 'ta-brandwall' ); ?> data-stagger>
	<?php foreach ( $items as $b ) : ?>
		<li><a class="ta-plate<?php echo $b['premium'] ? ' is-premium' : ''; ?>" href="<?php echo esc_url( $b['url'] ); ?>"><?php echo esc_html( $b['title'] ); ?><span class="screen-reader-text"> appliance repair</span></a></li>
	<?php endforeach; ?>
</ul>
