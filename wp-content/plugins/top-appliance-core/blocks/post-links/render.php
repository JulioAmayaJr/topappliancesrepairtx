<?php
/** Plain link list (footer columns, area chips, error-code appliances). */
defined( 'ABSPATH' ) || exit;
$source = $attributes['source'] ?? 'service';
$items  = top_nav_data()[ $source ] ?? array();
$limit  = (int) ( $attributes['limit'] ?? 0 );
if ( $limit > 0 ) {
	$items = array_slice( $items, 0, $limit );
}
if ( ! $items ) {
	return;
}
$variant = $attributes['variant'] ?? 'list';
$suffix  = array( 'service' => '', 'service_area' => '', 'brand' => '', 'appliance' => ' error codes' )[ $source ] ?? '';
$label   = trim( (string) ( $attributes['label'] ?? '' ) );
?>
<ul <?php echo top_block_wrapper( 'ta-links ta-links--' . sanitize_html_class( $variant ), $label ? array( 'aria-label' => $label ) : array() ); ?><?php echo 'chips' === $variant ? ' data-stagger' : ''; ?>>
	<?php foreach ( $items as $item ) : ?>
		<li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo 'chips' === $variant ? top_icon( 'map-pin', '', 16 ) : ''; ?><?php echo esc_html( ( 'service' === $source ? $item['short'] . ' Repair' : $item['title'] ) . $suffix ); ?></a></li>
	<?php endforeach; ?>
</ul>
