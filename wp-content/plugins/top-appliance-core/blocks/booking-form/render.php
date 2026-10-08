<?php
/** Fluent Forms booking form rendered server-side (shortcodes are not processed in block templates). */
defined( 'ABSPATH' ) || exit;
$form_id = (int) ( $attributes['formId'] ?? 0 ) ?: (int) get_option( 'top_booking_form_id', 0 );
if ( ! $form_id || ! shortcode_exists( 'fluentform' ) ) {
	return;
}
printf( '<div %s>%s</div>', top_block_wrapper( 'ta-booking' ), do_shortcode( sprintf( '[fluentform id="%d"]', $form_id ) ) ); // phpcs:ignore
