<?php
/** Logo. Header: mark + wordmark lockup. Footer: full light logo. */
defined( 'ABSPATH' ) || exit;
$variant = $attributes['variant'] ?? 'header';
$name    = top_biz( 'name' );
$home    = home_url( '/' );
if ( 'footer' === $variant ) {
	$img = top_logo_img( 'logo_light_id', array( 'alt' => $name . ' logo', 'loading' => 'lazy', 'class' => 'ta-brand__full' ) );
	printf( '<a %s href="%s">%s</a>', top_block_wrapper( 'ta-brand ta-brand--footer' ), esc_url( $home ), $img ?: esc_html( $name ) );
	return;
}
$mark = top_logo_img( 'logo_id', array( 'alt' => $name . ' logo', 'loading' => 'eager', 'fetchpriority' => 'auto', 'class' => 'ta-brand__mark', 'sizes' => '48px' ) );
$words = explode( ' ', $name );
$last  = array_pop( $words );
?>
<a <?php echo top_block_wrapper( 'ta-brand' ); ?> href="<?php echo esc_url( $home ); ?>" rel="home"<?php echo is_front_page() ? ' aria-current="page"' : ''; ?>>
	<?php echo $mark; // phpcs:ignore ?>
	<span class="ta-brand__name" aria-hidden="true"><span><?php echo esc_html( implode( ' ', $words ) ); ?></span> <span><?php echo esc_html( $last ); ?></span></span>
</a>
