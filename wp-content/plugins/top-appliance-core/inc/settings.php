<?php
/**
 * Settings → Business Info. Single source of truth for NAP, hours, socials, ZIPs and review links.
 */

defined( 'ABSPATH' ) || exit;

function top_settings_sections(): array {
	return array(
		'business' => array(
			'title'  => 'Business',
			'fields' => array(
				'name'          => array( 'Business name', 'text' ),
				'phone'         => array( 'Phone', 'text', 'Shown exactly as typed everywhere (NAP). Example: 832-514-1594' ),
				'email'         => array( 'Email', 'email' ),
				'years'         => array( 'Years of experience', 'number', 'Displayed as "10+ years".' ),
				'warranty_days' => array( 'Warranty (days)', 'number', 'Parts and labor warranty, e.g. 90.' ),
				'price_range'   => array( 'Price range (schema)', 'text', 'Optional, e.g. $$. Leave empty if unsure.' ),
			),
		),
		'address'  => array(
			'title'  => 'Address & location',
			'fields' => array(
				'street'      => array( 'Street address', 'text' ),
				'city'        => array( 'City', 'text' ),
				'region'      => array( 'State', 'text', 'Two-letter code, e.g. TX' ),
				'postal_code' => array( 'ZIP code', 'text' ),
				'country'     => array( 'Country code', 'text', 'ISO code, e.g. US' ),
				'lat'         => array( 'Latitude', 'text', 'Decimal degrees. Verify against the Google Business Profile pin.' ),
				'lng'         => array( 'Longitude', 'text' ),
			),
		),
		'hours'    => array(
			'title'  => 'Opening hours',
			'fields' => array( 'hours' => array( 'Hours', 'hours', 'Leave both empty for closed days.' ) ),
		),
		'social'   => array(
			'title'  => 'Social & Google',
			'fields' => array(
				'facebook'             => array( 'Facebook URL', 'url' ),
				'instagram'            => array( 'Instagram URL', 'url' ),
				'youtube'              => array( 'YouTube URL', 'url' ),
				'google_business'      => array( 'Google Business Profile URL', 'url', 'Added to schema sameAs.' ),
				'google_reviews_url'   => array( 'Google reviews URL', 'url', 'Where "Read our reviews on Google" links to.' ),
				'google_rating'        => array( 'Google rating', 'text', 'Optional, e.g. 4.9. Only shown when filled. Never added to schema.' ),
				'google_review_count'  => array( 'Google review count', 'number', 'Optional. Only shown when filled.' ),
				'google_reviews_embed' => array( 'Google reviews widget embed', 'code', 'Paste the widget HTML/script here. Rendered in the reviews section.' ),
			),
		),
		'service'  => array(
			'title'  => 'Service area, booking & map',
			'fields' => array(
				'zips'          => array( 'Service ZIP codes', 'textarea', 'Separate with spaces, commas or new lines. Powers the ZIP checker.' ),
				'booking_url'   => array( 'Booking page', 'text', 'Path or URL, e.g. /contact/ (the Home page always uses its own form).' ),
				'map_embed_url' => array( 'Google Maps embed URL', 'url', 'src of the Google Maps iframe. Loaded only after a click.' ),
				'map_link_url'  => array( 'Google Maps link', 'url', 'Fallback link to the map (opens Google Maps).' ),
			),
		),
		'media'    => array(
			'title'  => 'Logos & images',
			'fields' => array(
				'logo_id'       => array( 'Header logo mark (attachment ID)', 'media' ),
				'logo_full_id'  => array( 'Full logo (attachment ID)', 'media', 'Used in schema "logo".' ),
				'logo_light_id' => array( 'Footer logo, light (attachment ID)', 'media' ),
				'image_id'      => array( 'Business photo (attachment ID)', 'media', 'Used in schema "image".' ),
			),
		),
	);
}

add_action( 'admin_menu', function () {
	add_options_page( 'Business Info', 'Business Info', 'manage_options', 'top-business-info', 'top_render_settings_page' );
} );

add_action( 'admin_init', function () {
	register_setting(
		'top_business_group',
		'top_business',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'top_sanitize_business',
			'default'           => array(),
			'show_in_rest'      => false,
		)
	);
	foreach ( top_settings_sections() as $section_id => $section ) {
		add_settings_section( "top_$section_id", $section['title'], '__return_null', 'top-business-info' );
		foreach ( $section['fields'] as $key => $field ) {
			add_settings_field(
				"top_$key",
				esc_html( $field[0] ),
				'top_render_field',
				'top-business-info',
				"top_$section_id",
				array( 'key' => $key, 'type' => $field[1], 'help' => $field[2] ?? '', 'label_for' => "top_$key" )
			);
		}
	}
} );

function top_render_field( array $args ): void {
	$key   = $args['key'];
	$value = top_biz( $key, '' );
	$name  = 'top_business[' . $key . ']';
	$id    = 'top_' . $key;
	switch ( $args['type'] ) {
		case 'textarea':
		case 'code':
			printf( '<textarea id="%s" name="%s" rows="%d" class="large-text%s">%s</textarea>', esc_attr( $id ), esc_attr( $name ), 'code' === $args['type'] ? 6 : 4, 'code' === $args['type'] ? ' code' : '', esc_textarea( (string) $value ) );
			break;
		case 'hours':
			$hours = is_array( $value ) ? $value : array();
			echo '<table class="widefat striped" style="max-width:420px"><tbody>';
			foreach ( TOP_DAYS as $day => $names ) {
				printf(
					'<tr><th scope="row">%1$s</th><td><label class="screen-reader-text" for="top_h_%2$s_o">%1$s opens</label><input type="time" id="top_h_%2$s_o" name="top_business[hours][%2$s][open]" value="%3$s"> – <label class="screen-reader-text" for="top_h_%2$s_c">%1$s closes</label><input type="time" id="top_h_%2$s_c" name="top_business[hours][%2$s][close]" value="%4$s"></td></tr>',
					esc_html( $names[1] ),
					esc_attr( $day ),
					esc_attr( $hours[ $day ]['open'] ?? '' ),
					esc_attr( $hours[ $day ]['close'] ?? '' )
				);
			}
			echo '</tbody></table>';
			break;
		case 'media':
			printf( '<input type="number" min="0" id="%s" name="%s" value="%s" class="small-text">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) (int) $value ) );
			if ( $value ) {
				echo ' ' . wp_get_attachment_image( (int) $value, array( 60, 60 ), false, array( 'style' => 'vertical-align:middle;max-height:60px;width:auto;background:#01274F;padding:4px' ) );
			}
			break;
		default:
			printf( '<input type="%s" id="%s" name="%s" value="%s" class="regular-text">', esc_attr( $args['type'] ), esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
	}
	if ( $args['help'] ) {
		printf( '<p class="description">%s</p>', esc_html( $args['help'] ) );
	}
}

function top_sanitize_business( $input ): array {
	$input = is_array( $input ) ? $input : array();
	$out   = top_biz_all();
	foreach ( top_settings_sections() as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			if ( ! array_key_exists( $key, $input ) ) {
				continue;
			}
			$raw = $input[ $key ];
			switch ( $field[1] ) {
				case 'email':
					$out[ $key ] = sanitize_email( $raw );
					break;
				case 'url':
					$out[ $key ] = esc_url_raw( trim( (string) $raw ) );
					break;
				case 'number':
				case 'media':
					$out[ $key ] = '' === $raw ? '' : absint( $raw );
					break;
				case 'textarea':
					$out[ $key ] = sanitize_textarea_field( $raw );
					break;
				case 'code':
					$out[ $key ] = current_user_can( 'unfiltered_html' ) ? (string) $raw : wp_kses_post( $raw );
					break;
				case 'hours':
					$hours = array();
					foreach ( array_keys( TOP_DAYS ) as $day ) {
						foreach ( array( 'open', 'close' ) as $edge ) {
							$t                     = (string) ( $raw[ $day ][ $edge ] ?? '' );
							$hours[ $day ][ $edge ] = preg_match( '/^\d{2}:\d{2}$/', $t ) ? $t : '';
						}
					}
					$out[ $key ] = $hours;
					break;
				default:
					$out[ $key ] = sanitize_text_field( $raw );
			}
		}
	}
	return $out;
}

function top_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	echo '<div class="wrap"><h1>Business Info</h1>';
	echo '<p>These details feed the header, footer, calls to action, ZIP checker and structured data across the whole site. Keep them identical to the Google Business Profile.</p>';
	echo '<form method="post" action="options.php">';
	settings_fields( 'top_business_group' );
	do_settings_sections( 'top-business-info' );
	submit_button();
	echo '</form></div>';
}
