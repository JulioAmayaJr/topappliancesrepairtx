<?php
/**
 * Warranty seal: the logo's gear in the logo gradient, with the cyan power ring.
 * The graphic is decorative; the caption carries the information.
 */
defined( 'ABSPATH' ) || exit;
$days = (int) top_biz( 'warranty_days' );
if ( ! $days ) {
	return;
}
$size = max( 56, (int) ( $attributes['size'] ?? 76 ) );
$gid  = wp_unique_id( 'ta-seal-g' );
?>
<div <?php echo top_block_wrapper( 'ta-seal' ); ?> style="--seal:<?php echo (int) $size; ?>px">
	<svg class="ta-seal__mark" viewBox="0 0 120 120" width="<?php echo (int) $size; ?>" height="<?php echo (int) $size; ?>" aria-hidden="true" focusable="false">
		<defs><linearGradient id="<?php echo esc_attr( $gid ); ?>" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#054199"/><stop offset="1" stop-color="#055C99"/></linearGradient></defs>
		<path d="<?php echo esc_attr( top_gear_path( 60, 60, 59, 50, 10 ) ); ?>" fill="url(#<?php echo esc_attr( $gid ); ?>)"/>
		<circle cx="60" cy="60" r="41" fill="#fff"/>
		<circle cx="60" cy="60" r="36" fill="none" stroke="#29ABE2" stroke-width="3.5" stroke-linecap="round" stroke-dasharray="196 30" transform="rotate(-64 60 60)"/>
		<text x="60" y="66" text-anchor="middle" class="ta-seal__num"><?php echo (int) $days; ?></text>
		<text x="60" y="83" text-anchor="middle" class="ta-seal__unit">DAYS</text>
	</svg>
	<span class="ta-seal__caption"><strong><?php echo esc_html( sprintf( '%d-day warranty', $days ) ); ?></strong><span>On parts and labor</span></span>
</div>
