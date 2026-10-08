<?php
defined( 'ABSPATH' ) || exit;
if ( ! top_phone() ) {
	return;
}
printf(
	'<a %s href="%s">%s%s</a>',
	top_block_wrapper( 'ta-phone-link' ),
	esc_attr( top_phone_href() ),
	top_icon( 'phone' ),
	esc_html( trim( ( $attributes['prefix'] ?? '' ) . ' ' . top_phone() ) )
);
