<?php
/** Service hero: H1 + subtitle + CTAs + badges (left) and the booking form, appliance preselected (right). */
defined( 'ABSPATH' ) || exit;
$post_id = (int) ( $block->context['postId'] ?? get_the_ID() );
if ( ! $post_id ) {
	return;
}
$h1       = get_post_meta( $post_id, 'top_h1', true ) ?: get_the_title( $post_id ) . ' in ' . top_biz( 'city' ) . ', ' . top_biz( 'region' );
$subtitle = get_post_meta( $post_id, 'top_subtitle', true ) ?: get_the_excerpt( $post_id );
$value    = get_post_meta( $post_id, 'top_form_value', true ) ?: get_post_meta( $post_id, 'top_short_name', true );
$days     = (int) top_biz( 'warranty_days' );
?>
<section <?php echo top_block_wrapper( 'ta-shero' ); ?>>
	<div class="ta-wrap ta-shero__inner">
		<div class="ta-shero__copy">
			<h1 class="ta-shero__title"><?php echo esc_html( $h1 ); ?></h1>
			<?php if ( $subtitle ) : ?>
				<p class="ta-shero__lede"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>
			<div class="ta-shero__ctas">
				<?php if ( top_phone() ) : ?>
					<a class="ta-btn ta-btn--call ta-btn--lgx" href="<?php echo esc_attr( top_phone_href() ); ?>"><?php echo top_icon( 'phone' ); ?><span><?php echo esc_html( 'Call ' . top_phone() ); ?></span></a>
				<?php endif; ?>
				<a class="ta-btn ta-btn--book ta-btn--lgx" href="#book-form"><?php echo top_icon( 'calendar' ); ?><span>Book Service</span></a>
			</div>
			<ul class="ta-shero__badges">
				<li><?php echo top_icon( 'clock', '', 22 ); ?><span><strong>Same-day service</strong> across <?php echo esc_html( top_biz( 'city' ) ); ?></span></li>
				<?php if ( $days ) : ?>
					<li><?php echo top_icon( 'shield', '', 22 ); ?><span><strong><?php echo esc_html( $days . '-day warranty' ); ?></strong> on parts and labor</span></li>
				<?php endif; ?>
				<li><?php echo top_icon( 'clipboard', '', 22 ); ?><span><strong>Free estimate</strong> before any work</span></li>
			</ul>
		</div>
		<div class="ta-shero__form" id="book-form">
			<h2 class="ta-shero__form-title"><?php echo esc_html( sprintf( 'Book %s', strtolower( get_the_title( $post_id ) ) ) ); ?></h2>
			<p class="ta-shero__form-note">We will contact you to confirm a same-day visit.</p>
			<?php echo render_block( array( 'blockName' => 'top/booking-form', 'attrs' => array( 'appliance' => $value ), 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array() ) ); // phpcs:ignore ?>
		</div>
	</div>
</section>
