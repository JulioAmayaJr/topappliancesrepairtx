<?php
/** Fixed bottom bar, small screens only (CSS). */
defined( 'ABSPATH' ) || exit;
?>
<div <?php echo top_block_wrapper( 'ta-mobilebar' ); ?>>
	<?php if ( top_phone() ) : ?>
		<a class="ta-btn ta-btn--call" href="<?php echo esc_attr( top_phone_href() ); ?>" aria-label="<?php echo esc_attr( 'Call ' . top_phone() ); ?>"><?php echo top_icon( 'phone' ); ?><span>Call</span></a>
	<?php endif; ?>
	<a class="ta-btn ta-btn--book" href="<?php echo esc_url( top_booking_url() ); ?>"><?php echo top_icon( 'calendar' ); ?><span>Book Service</span></a>
</div>
