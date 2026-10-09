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
			),
		),
		'reviews'  => array(
			'title'  => 'Reviews',
			'fields' => array(
				'google_reviews_url'  => array( 'Google reviews URL', 'url', 'Where "See all reviews on Google" links to.' ),
				'google_rating'       => array( 'Average rating', 'text', 'e.g. 4.9 — copy it from Google. Shown only when filled. Never added to schema.' ),
				'google_review_count' => array( 'Total reviews', 'number', 'Copy it from Google. Shown only when filled.' ),
				'review_pinned'       => array( 'Pinned review (always shown first)', 'review' ),
				'reviews'             => array( 'Reviews', 'reviews', 'Real reviews only (copy from Google with the customer name as shown there). Empty rows are ignored. A Google Places API sync can fill this list later through the top_reviews_source filter.' ),
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
 		case 'review':
			top_render_review_row( $name, is_array( $value ) ? $value : array(), $id );
			break;
		case 'reviews':
			$rows   = is_array( $value ) ? array_values( $value ) : array();
			$rows[] = array();
			$rows[] = array();
			$rows[] = array();
			echo '<div id="' . esc_attr( $id ) . '">';
			foreach ( $rows as $i => $row ) {
				top_render_review_row( $name . '[' . $i . ']', $row, $id . '_' . $i );
			}
			echo '</div>';
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

function top_render_review_row( string $name, array $row, string $id ): void {
	printf(
		'<fieldset style="margin:0 0 12px;padding:10px;border:1px solid #ccd0d4;max-width:640px"><p style="margin:0 0 6px"><label for="%1$s_n">Name</label> <input id="%1$s_n" type="text" name="%2$s[name]" value="%3$s" class="regular-text"> <label for="%1$s_r">Rating</label> <input id="%1$s_r" type="number" min="1" max="5" name="%2$s[rating]" value="%4$s" class="small-text"> <label for="%1$s_d">Date</label> <input id="%1$s_d" type="date" name="%2$s[date]" value="%5$s"></p><p style="margin:0"><label class="screen-reader-text" for="%1$s_t">Review text</label><textarea id="%1$s_t" name="%2$s[text]" rows="3" class="large-text">%6$s</textarea></p></fieldset>',
		esc_attr( $id ),
		esc_attr( $name ),
		esc_attr( $row['name'] ?? '' ),
		esc_attr( (string) ( $row['rating'] ?? 5 ) ),
		esc_attr( $row['date'] ?? '' ),
		esc_textarea( $row['text'] ?? '' )
	);
}

function top_sanitize_review( $row ): ?array {
	if ( ! is_array( $row ) ) {
		return null;
	}
	$out = array(
		'name'   => sanitize_text_field( $row['name'] ?? '' ),
		'rating' => max( 1, min( 5, (int) ( $row['rating'] ?? 5 ) ) ),
		'date'   => preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) ( $row['date'] ?? '' ) ) ? $row['date'] : '',
		'text'   => sanitize_textarea_field( $row['text'] ?? '' ),
	);
	return ( $out['name'] && $out['text'] ) ? $out : null;
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
				case 'review':
					$out[ $key ] = top_sanitize_review( $raw ) ?? array();
					break;
				case 'reviews':
					$out[ $key ] = array_values( array_filter( array_map( 'top_sanitize_review', is_array( $raw ) ? $raw : array() ) ) );
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
