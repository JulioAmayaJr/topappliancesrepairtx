<?php
/**
 * Link lists (footer columns, area chips, error-code appliances).
 * collapsible=true → <details open> with the title as summary (footer accordion on phones; the theme
 * JS closes them below 768px, so without JS everything stays visible).
 */
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
$title   = trim( (string) ( $attributes['title'] ?? '' ) );
$all     = trim( (string) ( $attributes['allLabel'] ?? '' ) );
$archive = array( 'service' => 'service', 'service_area' => 'service_area', 'brand' => 'brand', 'appliance' => 'error_code' )[ $source ] ?? '';
$all_url = $all && $archive ? get_post_type_archive_link( $archive ) : '';

ob_start();
?>
<ul class="ta-links ta-links--<?php echo esc_attr( sanitize_html_class( $variant ) ); ?>"<?php echo $label && ! $title ? ' aria-label="' . esc_attr( $label ) . '"' : ''; ?>>
	<?php foreach ( $items as $item ) : ?>
		<li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo 'chips' === $variant ? top_icon( 'map-pin', '', 16 ) : ''; ?><?php echo esc_html( ( 'service' === $source ? $item['short'] . ' Repair' : $item['title'] ) . $suffix ); ?></a></li>
	<?php endforeach; ?>
	<?php if ( $all_url ) : ?>
		<li class="ta-links__all"><a href="<?php echo esc_url( $all_url ); ?>"><?php echo esc_html( $all ); ?></a></li>
	<?php endif; ?>
</ul>
<?php
$list = ob_get_clean();
if ( ! empty( $attributes['collapsible'] ) && $title ) {
	printf(
		'<details %s open><summary><h2 class="ta-fold__title">%s</h2>%s</summary>%s</details>',
		top_block_wrapper( 'ta-fold' ),
		esc_html( $title ),
		top_icon( 'chevron-down', 'ta-fold__chev', 18 ),
		$list // phpcs:ignore
	);
	return;
}
printf( '<div %s>%s</div>', top_block_wrapper( 'ta-links-wrap' ), $list ); // phpcs:ignore
