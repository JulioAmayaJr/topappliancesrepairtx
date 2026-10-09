<?php
/** Brand cards: logo + name, linking to the brand page. */
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
<ul <?php echo top_block_wrapper( 'ta-brandwall' ); ?>>
	<?php foreach ( $items as $b ) : ?>
		<li>
			<a class="ta-brandcard" href="<?php echo esc_url( $b['url'] ); ?>">
				<span class="ta-brandcard__logo">
					<?php
					echo $b['logo']
						? wp_get_attachment_image( $b['logo'], 'full', false, array( 'alt' => $b['title'] . ' appliance repair', 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '160px' ) )
						: '<span class="ta-brandcard__fallback" aria-hidden="true">' . esc_html( $b['title'] ) . '</span>';
					?>
				</span>
				<span class="ta-brandcard__name"<?php echo $b['logo'] ? ' aria-hidden="true"' : ''; ?>><?php echo esc_html( $b['title'] ); ?></span>
			</a>
		</li>
	<?php endforeach; ?>
	<li class="ta-brandwall__all"><a class="ta-brandcard ta-brandcard--all" href="<?php echo esc_url( get_post_type_archive_link( 'brand' ) ); ?>">View all brands →</a></li>
</ul>
