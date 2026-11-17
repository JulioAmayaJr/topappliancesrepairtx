<?php
/** FAQ section from the post's `top_faqs` field (same source as the FAQPage JSON-LD). */
defined( 'ABSPATH' ) || exit;

$post_id = (int) ( $block->context['postId'] ?? get_the_ID() );
if ( ! $post_id ) {
	return;
}
$faqs = array_filter(
	(array) get_post_meta( $post_id, 'top_faqs', true ),
	fn( $f ) => ! empty( $f['q'] ) && ! empty( $f['a'] )
);
if ( ! $faqs ) {
	return;
}
$lede = (string) ( $attributes['lede'] ?? '' );
?>
<section <?php echo top_block_wrapper( 'ta-section ta-faq-sec' ); ?>>
	<div class="ta-wrap ta-faq-sec__inner">
		<div class="ta-faq-sec__head">
			<p class="ta-eyebrow"><?php echo esc_html( $attributes['eyebrow'] ?? 'FAQ' ); ?></p>
			<h2 class="wp-block-heading"><?php echo esc_html( $attributes['heading'] ?? 'Frequently asked questions' ); ?></h2>
			<?php if ( $lede ) : ?>
				<p class="ta-section__lede"><?php echo esc_html( $lede ); ?></p>
			<?php endif; ?>
		</div>
		<div class="ta-faq">
			<?php foreach ( $faqs as $f ) : ?>
				<details class="wp-block-details">
					<summary><?php echo esc_html( $f['q'] ); ?></summary>
					<p><?php echo wp_kses( $f['a'], array( 'a' => array( 'href' => true ), 'strong' => array() ) ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
