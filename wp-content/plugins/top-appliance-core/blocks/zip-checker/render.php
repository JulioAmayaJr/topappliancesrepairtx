<?php
/**
 * ZIP checker. Works without JS (GET ?zip= → server message); the theme JS answers instantly.
 */
defined( 'ABSPATH' ) || exit;

$zips = array();
foreach ( top_zip_list() as $z ) {
	$zips[ $z ] = '';
}
foreach ( top_nav_data()['service_area'] ?? array() as $area ) {
	foreach ( $area['zips'] as $z ) {
		$zips[ $z ] = empty( $zips[ $z ] ) ? $area['title'] : $zips[ $z ] . ', ' . $area['title'];
	}
}
if ( ! $zips ) {
	return;
}
$uid     = wp_unique_id( 'ta-zip-' );
$phone   = top_phone();
$tel     = top_phone_href();
$book    = top_booking_url();
$current = isset( $_GET['zip'] ) ? preg_replace( '/\D/', '', wp_unslash( (string) $_GET['zip'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$message = '';
$state   = '';
if ( '' !== $current ) {
	if ( strlen( $current ) !== 5 ) {
		$state   = 'error';
		$message = esc_html( 'Enter a 5-digit ZIP code, for example ' . top_biz( 'postal_code' ) . '.' );
	} elseif ( array_key_exists( $current, $zips ) ) {
		$state   = 'yes';
		$place   = $zips[ $current ] ? ' (' . $zips[ $current ] . ')' : '';
		$message = sprintf( 'Yes, we cover %s%s. <a href="%s">Call %s</a> or <a href="%s">book online</a>.', esc_html( $current ), esc_html( $place ), esc_attr( $tel ), esc_html( $phone ), esc_url( $book ) );
	} else {
		$state   = 'no';
		$message = sprintf( '%s is outside our usual route. <a href="%s">Call %s</a> and we will tell you if a technician can reach you.', esc_html( $current ), esc_attr( $tel ), esc_html( $phone ) );
	}
}
$config = array(
	'zips'  => $zips,
	'phone' => $phone,
	'tel'   => $tel,
	'book'  => $book,
	'ex'    => top_biz( 'postal_code' ),
);
?>
<form <?php echo top_block_wrapper( 'ta-zip' ); ?> action="<?php echo esc_url( strtok( (string) ( $_SERVER['REQUEST_URI'] ?? '/' ), '?' ) . '#service-areas' ); ?>" method="get" novalidate data-zip-checker>
	<label class="ta-zip__label" for="<?php echo esc_attr( $uid ); ?>"><?php echo esc_html( $attributes['label'] ?? 'Check your ZIP code' ); ?></label>
	<div class="ta-zip__row">
		<input class="ta-zip__input" id="<?php echo esc_attr( $uid ); ?>" name="zip" type="text" inputmode="numeric" autocomplete="postal-code" pattern="[0-9]{5}" maxlength="5" value="<?php echo esc_attr( $current ); ?>" aria-describedby="<?php echo esc_attr( $uid ); ?>-msg">
		<button class="ta-btn ta-btn--blue" type="submit"><?php echo top_icon( 'search' ); ?><span>Check ZIP</span></button>
	</div>
	<p class="ta-zip__msg" id="<?php echo esc_attr( $uid ); ?>-msg" role="status" aria-live="polite"<?php echo $state ? ' data-state="' . esc_attr( $state ) . '"' : ''; ?>><?php echo $message; // phpcs:ignore — escaped above ?></p>
	<script type="application/json" class="ta-zip__data"><?php echo wp_json_encode( $config ); ?></script>
</form>
