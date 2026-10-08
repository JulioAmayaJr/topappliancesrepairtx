<?php
/**
 * Block Bindings source "top/business": lets core blocks (button, paragraph, heading)
 * read Business Info, so phone/address/hours are editable in one place only.
 *
 * Example in a pattern:
 *   <!-- wp:button {"metadata":{"bindings":{
 *      "text":{"source":"top/business","args":{"key":"call_label"}},
 *      "url":{"source":"top/business","args":{"key":"phone_href"}}}}} -->
 */

defined( 'ABSPATH' ) || exit;

function top_binding_values(): array {
	$phone = top_phone();
	return array(
		'name'           => top_biz( 'name' ),
		'phone'          => $phone,
		'phone_href'     => top_phone_href(),
		'call_label'     => $phone ? 'Call ' . $phone : 'Call us',
		'call_now'       => 'Call Now',
		'email'          => top_biz( 'email' ),
		'email_href'     => top_biz( 'email' ) ? 'mailto:' . top_biz( 'email' ) : '',
		'street'         => top_address_line(),
		'city_line'      => top_city_line(),
		'address'        => top_full_address(),
		'hours'          => top_hours_summary(),
		'booking_url'    => top_booking_url(),
		'years_label'    => top_years_label(),
		'warranty_label' => top_warranty_label(),
		'warranty_days'  => (string) top_biz( 'warranty_days' ),
		'years'          => (string) top_biz( 'years' ),
		'reviews_url'    => (string) top_biz( 'google_reviews_url' ),
		'faq_warranty'   => top_biz( 'warranty_days' )
			? sprintf( 'Every repair is covered by our %s-day warranty on parts and labor.', top_biz( 'warranty_days' ) )
			: 'Every repair is covered by our warranty on parts and labor.',
		'faq_hours'      => top_hours_sentence(),
	);
}

add_action( 'init', function () {
	if ( ! function_exists( 'register_block_bindings_source' ) ) {
		return;
	}
	register_block_bindings_source(
		'top/business',
		array(
			'label'              => 'Business Info',
			'get_value_callback' => function ( array $source_args ) {
				$key    = $source_args['key'] ?? '';
				$values = top_binding_values();
				return $values[ $key ] ?? null;
			},
		)
	);
} );
