<?php
/** Top bar: hours, phone, socials. */
defined( 'ABSPATH' ) || exit;
$hours = top_hours_summary();
?>
<div <?php echo top_block_wrapper( 'ta-topbar' ); ?>>
	<div class="ta-topbar__inner">
		<?php if ( $hours ) : ?>
			<p class="ta-topbar__hours"><?php echo top_icon( 'clock' ); ?><span><?php echo esc_html( $hours ); ?></span></p>
		<?php endif; ?>
		<p class="ta-topbar__note"><?php echo top_icon( 'map-pin' ); ?><span><?php echo esc_html( sprintf( 'Same-day appliance repair in %s and nearby', top_biz( 'city' ) ) ); ?></span></p>
		<?php if ( top_phone() ) : ?>
			<a class="ta-topbar__phone" href="<?php echo esc_attr( top_phone_href() ); ?>"><?php echo top_icon( 'phone' ); ?><span><?php echo esc_html( top_phone() ); ?></span></a>
		<?php endif; ?>
		<?php echo top_render_socials( 'ta-social--topbar' ); ?>
	</div>
</div>
