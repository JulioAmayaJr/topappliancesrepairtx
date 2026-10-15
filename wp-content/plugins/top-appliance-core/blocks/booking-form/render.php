<?php
/**
 * Fluent Forms booking form rendered server-side (shortcodes are not processed in block templates).
 * `appliance` preselects that option in the Appliance dropdown (no JS needed).
 */
defined( 'ABSPATH' ) || exit;
$form_id = (int) ( $attributes['formId'] ?? 0 ) ?: (int) get_option( 'top_booking_form_id', 0 );
if ( ! $form_id || ! shortcode_exists( 'fluentform' ) ) {
	return;
}
$appliance = trim( (string) ( $attributes['appliance'] ?? '' ) );
$preselect = function ( $data ) use ( $appliance ) {
	if ( 'appliance' === ( $data['attributes']['name'] ?? '' ) && $appliance ) {
		$data['attributes']['value'] = $appliance;
	}
	return $data;
};
add_filter( 'fluentform/rendering_field_data_select', $preselect );
$html = do_shortcode( sprintf( '[fluentform id="%d"]', $form_id ) );
remove_filter( 'fluentform/rendering_field_data_select', $preselect );
printf( '<div %s>%s</div>', top_block_wrapper( 'ta-booking' ), $html ); // phpcs:ignore
