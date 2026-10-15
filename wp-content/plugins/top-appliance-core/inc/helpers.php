<?php
/**
 * Business data accessors. Every phone number, address, hour and social link on the
 * site comes from the "Business Info" settings (option `top_business`) through these helpers.
 */

defined( 'ABSPATH' ) || exit;

const TOP_DAYS = array(
	'mon' => array( 'Mon', 'Monday' ),
	'tue' => array( 'Tue', 'Tuesday' ),
	'wed' => array( 'Wed', 'Wednesday' ),
	'thu' => array( 'Thu', 'Thursday' ),
	'fri' => array( 'Fri', 'Friday' ),
	'sat' => array( 'Sat', 'Saturday' ),
	'sun' => array( 'Sun', 'Sunday' ),
);

/** Field map of the Business Info option. Values are empty: real data is saved in wp-admin. */
function top_biz_fields(): array {
	return array(
		'name'                 => '',
		'phone'                => '',
		'email'                => '',
		'street'               => '',
		'city'                 => '',
		'region'               => '',
		'postal_code'          => '',
		'country'              => 'US',
		'lat'                  => '',
		'lng'                  => '',
		'hours'                => array(),
		'years'                => '',
		'warranty_days'        => '',
		'facebook'             => '',
		'instagram'            => '',
		'youtube'              => '',
		'google_business'      => '',
		'google_reviews_url'   => '',
		'google_rating'        => '',
		'google_review_count'  => '',
		'google_reviews_embed' => '',
		'review_pinned'        => array(),
		'reviews'              => array(),
		'booking_url'          => '',
		'map_embed_url'        => '',
		'map_link_url'         => '',
		'zips'                 => '',
		'price_range'          => '',
		'logo_id'              => 0,
		'logo_full_id'         => 0,
		'logo_light_id'        => 0,
		'image_id'             => 0,
	);
}

function top_biz_all(): array {
	static $cache = null;
	if ( null === $cache || doing_action( 'update_option_top_business' ) ) {
		$saved = get_option( 'top_business', array() );
		$cache = array_merge( top_biz_fields(), is_array( $saved ) ? $saved : array() );
	}
	return $cache;
}

add_action( 'update_option_top_business', function () {
	top_biz_all(); // refresh static cache
	delete_transient( 'top_nav_data' );
} );

/** Raw business field. */
function top_biz( string $key, $fallback = '' ) {
	$all = top_biz_all();
	$val = $all[ $key ] ?? $fallback;
	return ( '' === $val || null === $val ) ? $fallback : $val;
}

/** Phone formatted exactly as entered (NAP must be identical everywhere). */
function top_phone(): string {
	return (string) top_biz( 'phone' );
}

/** tel: URI in E.164 (assumes US number when 10 digits). */
function top_phone_href(): string {
	$digits = preg_replace( '/\D+/', '', top_phone() );
	if ( strlen( $digits ) === 10 ) {
		$digits = '1' . $digits;
	}
	return $digits ? 'tel:+' . $digits : '';
}

/** Phone for schema, e.g. +1-832-514-1594. */
function top_phone_schema(): string {
	$digits = preg_replace( '/\D+/', '', top_phone() );
	if ( strlen( $digits ) === 10 ) {
		return sprintf( '+1-%s-%s-%s', substr( $digits, 0, 3 ), substr( $digits, 3, 3 ), substr( $digits, 6 ) );
	}
	return top_phone();
}

function top_address_line(): string {
	return trim( (string) top_biz( 'street' ) );
}

function top_city_line(): string {
	$city   = top_biz( 'city' );
	$region = top_biz( 'region' );
	$zip    = top_biz( 'postal_code' );
	return trim( sprintf( '%s, %s %s', $city, $region, $zip ), ', ' );
}

function top_full_address(): string {
	return trim( top_address_line() . ', ' . top_city_line(), ', ' );
}

/** Booking URL. On the front page it jumps to the on-page form. */
function top_booking_url(): string {
	if ( is_front_page() ) {
		return '#book';
	}
	if ( is_singular( 'service' ) ) {
		return '#book-form'; // the booking form in the service hero
	}
	$url = (string) top_biz( 'booking_url', '/contact/' );
	return str_starts_with( $url, 'http' ) ? $url : home_url( $url );
}

function top_years_label(): string {
	$years = top_biz( 'years' );
	return $years ? sprintf( '%s+ years', $years ) : '';
}

function top_warranty_label(): string {
	$days = top_biz( 'warranty_days' );
	return $days ? sprintf( '%s-day warranty', $days ) : '';
}

/** Social profiles that are filled in, keyed by network. */
function top_socials(): array {
	$out = array();
	foreach ( array( 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube' ) as $key => $label ) {
		$url = top_biz( $key );
		if ( $url ) {
			$out[ $key ] = array( 'label' => $label, 'url' => $url );
		}
	}
	return $out;
}

/** "9:00" → "9am", "17:30" → "5:30pm". */
function top_time_label( string $hhmm ): string {
	if ( ! preg_match( '/^(\d{1,2}):(\d{2})$/', $hhmm, $m ) ) {
		return '';
	}
	$h      = (int) $m[1];
	$suffix = $h >= 12 ? 'pm' : 'am';
	$h12    = $h % 12 ?: 12;
	return $h12 . ( '00' === $m[2] ? '' : ':' . $m[2] ) . $suffix;
}

/**
 * Consecutive days with identical hours, grouped.
 * Returns [ ['days'=>['mon',...], 'label'=>'Mon–Fri', 'long'=>'Monday–Friday', 'open'=>'09:00', 'close'=>'17:00', 'text'=>'9am–5pm'], ... ]
 */
function top_hours_groups( bool $include_closed = true ): array {
	$hours  = (array) top_biz( 'hours', array() );
	$groups = array();
	foreach ( TOP_DAYS as $key => $names ) {
		$open  = $hours[ $key ]['open'] ?? '';
		$close = $hours[ $key ]['close'] ?? '';
		$sig   = $open && $close ? "$open-$close" : 'closed';
		$last  = count( $groups ) - 1;
		if ( $last >= 0 && $groups[ $last ]['sig'] === $sig ) {
			$groups[ $last ]['days'][] = $key;
		} else {
			$groups[] = array( 'sig' => $sig, 'days' => array( $key ), 'open' => $open, 'close' => $close );
		}
	}
	$out = array();
	foreach ( $groups as $g ) {
		if ( 'closed' === $g['sig'] && ! $include_closed ) {
			continue;
		}
		$first  = reset( $g['days'] );
		$lastd  = end( $g['days'] );
		$label  = TOP_DAYS[ $first ][0] . ( $first !== $lastd ? '–' . TOP_DAYS[ $lastd ][0] : '' );
		$long   = TOP_DAYS[ $first ][1] . ( $first !== $lastd ? '–' . TOP_DAYS[ $lastd ][1] : '' );
		$out[]  = array(
			'days'  => $g['days'],
			'label' => $label,
			'long'  => $long,
			'open'  => $g['open'],
			'close' => $g['close'],
			'text'  => 'closed' === $g['sig'] ? 'Closed' : top_time_label( $g['open'] ) . '–' . top_time_label( $g['close'] ),
		);
	}
	return $out;
}

/** "Mon–Fri 9am–5pm, Sat 9am–3pm" */
function top_hours_summary(): string {
	$parts = array();
	foreach ( top_hours_groups( false ) as $g ) {
		$parts[] = $g['label'] . ' ' . $g['text'];
	}
	return implode( ', ', $parts );
}

/** Service ZIP codes as an array of 5-digit strings. */
function top_zip_list(): array {
	preg_match_all( '/\b\d{5}\b/', (string) top_biz( 'zips' ), $m );
	return array_values( array_unique( $m[0] ) );
}

/** Attachment helpers for logos stored in Business Info. */
function top_logo_img( string $key, array $attr = array() ): string {
	$id = (int) top_biz( $key, 0 );
	if ( ! $id ) {
		return '';
	}
	$attr = array_merge( array( 'alt' => top_biz( 'name' ) . ' logo', 'decoding' => 'async' ), $attr );
	return wp_get_attachment_image( $id, 'full', false, $attr );
}

/** Escape helper for inline SVG markup produced by top_icon(). */
function top_kses_svg(): array {
	$common = array( 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'd' => true, 'transform' => true, 'opacity' => true, 'class' => true );
	return array(
		'svg'      => array( 'xmlns' => true, 'viewbox' => true, 'width' => true, 'height' => true, 'aria-hidden' => true, 'focusable' => true, 'class' => true, 'role' => true, 'fill' => true ),
		'path'     => $common,
		'circle'   => $common + array( 'cx' => true, 'cy' => true, 'r' => true ),
		'rect'     => $common + array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true ),
		'line'     => $common + array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true ),
		'polyline' => $common + array( 'points' => true ),
		'ellipse'  => $common + array( 'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true ),
		'g'        => $common,
	);
}

/** Social icon list. */
function top_render_socials( string $extra_class = '' ): string {
	$items = top_socials();
	if ( ! $items ) {
		return '';
	}
	$name = top_biz( 'name' );
	$out  = '<ul class="ta-social ' . esc_attr( $extra_class ) . '">';
	foreach ( $items as $key => $s ) {
		$out .= sprintf(
			'<li><a href="%s" rel="noopener me" target="_blank" aria-label="%s">%s</a></li>',
			esc_url( $s['url'] ),
			esc_attr( sprintf( '%s on %s (opens in a new tab)', $name, $s['label'] ) ),
			top_icon( $key )
		);
	}
	return $out . '</ul>';
}

/** "We are open Mon–Fri 9am–5pm, Sat 9am–3pm. Closed Sunday." */
function top_hours_sentence(): string {
	$open = top_hours_summary();
	if ( ! $open ) {
		return '';
	}
	$closed = array_column( array_filter( top_hours_groups(), fn( $g ) => 'Closed' === $g['text'] ), 'long' );
	return sprintf( 'We are open %s.%s', $open, $closed ? ' Closed ' . implode( ' and ', $closed ) . '.' : '' );
}

/**
 * Reviews for the reviews widget, normalised:
 * ['reviews'=>[ ['name','rating','date','text','source'] ], 'rating'=>?float, 'count'=>?int, 'url'=>string, 'preview'=>bool]
 *
 * Data source: Business Info (pinned review + list). To connect Google Places API later, hook
 * `top_reviews_source` and return the same shape (cache the API response in a transient); the
 * widget markup does not change.
 *
 * Preview: with no real reviews besides the pinned one, five clearly-labelled sample reviews are added
 * ONLY on local/development environments or for logged-in admins. They never reach production
 * visitors and never enter the JSON-LD (the schema does not output reviews at all).
 */
function top_reviews_data(): array {
	$data = array(
		'reviews' => array(),
		'rating'  => top_biz( 'google_rating' ) ? (float) top_biz( 'google_rating' ) : null,
		'count'   => top_biz( 'google_review_count' ) ? (int) top_biz( 'google_review_count' ) : null,
		'url'     => (string) top_biz( 'google_reviews_url' ),
		'preview' => false,
	);
	$pinned = top_biz( 'review_pinned', array() );
	if ( ! empty( $pinned['name'] ) && ! empty( $pinned['text'] ) ) {
		$data['reviews'][] = $pinned + array( 'source' => 'site' );
	}
	foreach ( (array) top_biz( 'reviews', array() ) as $r ) {
		if ( ! empty( $r['name'] ) && ! empty( $r['text'] ) ) {
			$data['reviews'][] = $r + array( 'source' => 'google' );
		}
	}
	$data = apply_filters( 'top_reviews_source', $data );

	$real_beyond_pinned = count( $data['reviews'] ) - ( empty( $pinned['name'] ) ? 0 : 1 );
	$can_preview        = (bool) apply_filters( 'top_reviews_can_preview', in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) || current_user_can( 'manage_options' ) );
	if ( $real_beyond_pinned < 1 && $can_preview ) {
		$data['preview'] = true;
		foreach ( top_sample_reviews() as $sample ) {
			$data['reviews'][] = $sample + array( 'source' => 'sample' );
		}
	}
	return $data;
}

/** Generic sample reviews for the local preview only. */
function top_sample_reviews(): array {
	$texts = array(
		'The technician arrived on the scheduled day, explained what was wrong and gave us the price before starting. Everything works again.',
		'Quick to answer the phone and easy to book. The repair was done in one visit and the technician cleaned up afterwards.',
		'Clear communication from start to finish. We knew what the repair would cost before any work began, and there were no surprises.',
		'Friendly, on time and professional. Our appliance has been running without problems since the visit. Would call them again.',
		'They explained the options honestly, including when a repair made sense and when it did not. Good experience overall.',
	);
	$out = array();
	foreach ( $texts as $i => $text ) {
		$out[] = array(
			'name'   => 'Customer ' . chr( 65 + $i ),
			'rating' => 5,
			'date'   => wp_date( 'Y-m-d', strtotime( '-' . ( 9 + $i * 17 ) . ' days' ) ),
			'text'   => $text,
		);
	}
	return $out;
}

/** "3 weeks ago" from Y-m-d, or '' when unknown. */
function top_relative_date( string $ymd ): string {
	$ts = $ymd ? strtotime( $ymd . ' 12:00:00' ) : 0;
	return $ts ? sprintf( '%s ago', human_time_diff( $ts, time() ) ) : '';
}
