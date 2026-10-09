<?php
/** NAP and/or opening hours. NAP is printed identically everywhere from Business Info. */
defined( 'ABSPATH' ) || exit;
$variant = $attributes['variant'] ?? 'nap-hours';
$email   = top_biz( 'email' );
if ( 'hours-line' === $variant ) {
	printf( '<p %s>%s<span>%s</span></p>', top_block_wrapper( 'ta-hoursline' ), top_icon( 'clock', '', 18 ), esc_html( top_hours_summary() ) ); // phpcs:ignore
	return;
}
if ( 'contact' === $variant ) {
	$map = top_biz( 'map_link_url' ) ?: 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( top_biz( 'name' ) . ', ' . top_full_address() );
	ob_start(); ?>
	<address class="ta-nap">
		<?php if ( top_phone() ) : ?>
			<a class="ta-nap__row ta-nap__phone" href="<?php echo esc_attr( top_phone_href() ); ?>"><?php echo top_icon( 'phone', '', 20 ); ?><span><?php echo esc_html( top_phone() ); ?></span></a>
		<?php endif; ?>
		<?php if ( $email ) : ?>
			<a class="ta-nap__row" href="<?php echo esc_attr( 'mailto:' . $email ); ?>"><?php echo top_icon( 'mail', '', 20 ); ?><span><?php echo esc_html( $email ); ?></span></a>
		<?php endif; ?>
		<a class="ta-nap__row" href="<?php echo esc_url( $map ); ?>" rel="noopener" target="_blank"><?php echo top_icon( 'map-pin', '', 20 ); ?><span><?php echo esc_html( top_address_line() ); ?><br><?php echo esc_html( top_city_line() ); ?><span class="screen-reader-text"> (opens Google Maps in a new tab)</span></span></a>
		<span class="ta-nap__row"><?php echo top_icon( 'clock', '', 20 ); ?><span><?php echo esc_html( top_hours_summary() ); ?></span></span>
	</address>
	<a class="ta-btn ta-btn--book ta-nap__book" href="<?php echo esc_url( top_booking_url() ); ?>"><?php echo top_icon( 'calendar' ); ?><span>Book Online — Same Day</span></a>
	<?php
	printf( '<div %s>%s</div>', top_block_wrapper( 'ta-business ta-business--contact' ), ob_get_clean() ); // phpcs:ignore
	return;
}
ob_start();
if ( 'hours' !== $variant ) : ?>
	<address class="ta-nap">
		<span class="ta-nap__row"><?php echo top_icon( 'map-pin', '', 20 ); ?><span><span class="ta-nap__name"><?php echo esc_html( top_biz( 'name' ) ); ?></span><br><?php echo esc_html( top_address_line() ); ?><br><?php echo esc_html( top_city_line() ); ?></span></span>
		<?php if ( top_phone() ) : ?>
			<a class="ta-nap__row" href="<?php echo esc_attr( top_phone_href() ); ?>"><?php echo top_icon( 'phone', '', 20 ); ?><span><?php echo esc_html( top_phone() ); ?></span></a>
		<?php endif; ?>
		<?php if ( $email ) : ?>
			<a class="ta-nap__row" href="<?php echo esc_attr( 'mailto:' . $email ); ?>"><?php echo top_icon( 'mail', '', 20 ); ?><span><?php echo esc_html( $email ); ?></span></a>
		<?php endif; ?>
	</address>
<?php endif;
if ( 'nap' !== $variant ) :
	$groups = top_hours_groups();
	if ( $groups ) : ?>
		<dl class="ta-hours">
			<?php foreach ( $groups as $g ) : ?>
				<div class="ta-hours__row"><dt><abbr title="<?php echo esc_attr( $g['long'] ); ?>"><?php echo esc_html( $g['label'] ); ?></abbr></dt><dd><?php echo esc_html( $g['text'] ); ?></dd></div>
			<?php endforeach; ?>
		</dl>
	<?php endif;
endif;
$html = ob_get_clean();
printf( '<div %s>%s</div>', top_block_wrapper( 'ta-business ta-business--' . sanitize_html_class( $variant ) ), $html ); // phpcs:ignore
