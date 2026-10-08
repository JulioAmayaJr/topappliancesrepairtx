<?php
/**
 * Google reviews. Rating/count appear ONLY when entered in Business Info (never invented,
 * never added to schema). "panel" prints the reviews widget embed when one is configured.
 */
defined( 'ABSPATH' ) || exit;
$variant = $attributes['variant'] ?? 'badge';
$url     = top_biz( 'google_reviews_url' );
$rating  = (float) top_biz( 'google_rating' );
$count   = (int) top_biz( 'google_review_count' );
$stars   = '';
if ( $rating > 0 ) {
	$stars = '<span class="ta-stars" aria-hidden="true">' . str_repeat( top_icon( 'star', '', 16 ), (int) round( $rating ) ) . '</span>';
}
$meta = $rating > 0
	? sprintf( '<span class="ta-greviews__score">%s</span>%s%s', esc_html( number_format_i18n( $rating, 1 ) ), $stars, $count ? '<span class="ta-greviews__count">' . esc_html( sprintf( '%s reviews', number_format_i18n( $count ) ) ) . '</span>' : '' )
	: '<span class="ta-greviews__cta">Read what customers say</span>';

if ( 'badge' === $variant ) {
	$tag   = $url ? 'a' : 'div';
	$href  = $url ? ' href="' . esc_url( $url ) . '" rel="noopener" target="_blank"' : '';
	$sr = $url ? '<span class="screen-reader-text"> (opens in a new tab)</span>' : '';
	if ( $rating > 0 ) {
		$sr = '<span class="screen-reader-text">' . esc_html( sprintf( ', rated %s out of 5', $rating ) ) . '</span>' . $sr;
	}
	printf(
		'<%1$s %2$s%3$s>%4$s<span class="ta-greviews__text"><span class="ta-greviews__title">Google reviews</span><span class="ta-greviews__meta">%5$s</span>%6$s</span></%1$s>',
		$tag,
		top_block_wrapper( 'ta-greviews' ),
		$href,
		top_icon( 'google', 'ta-greviews__logo', 28 ),
		$meta, // phpcs:ignore — escaped above
		$sr
	);
	return;
}

$embed = top_biz( 'google_reviews_embed' );
?>
<div <?php echo top_block_wrapper( 'ta-greviews-panel' ); ?> data-reviews-widget>
	<?php if ( $embed ) : ?>
		<?php echo $embed; // phpcs:ignore — admin-only field (unfiltered_html) ?>
	<?php else : ?>
		<div class="ta-greviews-panel__head">
			<?php echo top_icon( 'google', '', 36 ); ?>
			<p class="ta-greviews-panel__title">Reviews on Google</p>
		</div>
		<?php if ( $rating > 0 ) : ?>
			<p class="ta-greviews-panel__rating"><?php echo $meta; // phpcs:ignore ?></p>
		<?php endif; ?>
		<p>Read reviews from Houston customers on our Google Business Profile, or leave one after your repair.</p>
		<?php if ( $url ) : ?>
			<a class="ta-btn ta-btn--outline" href="<?php echo esc_url( $url ); ?>" rel="noopener" target="_blank">Read our Google reviews<span class="screen-reader-text"> (opens in a new tab)</span></a>
		<?php endif; ?>
		<?php if ( current_user_can( 'manage_options' ) ) : ?>
			<p class="ta-admin-note">Admin note: paste the Google reviews widget in Settings → Business Info to show live reviews here.</p>
		<?php endif; ?>
	<?php endif; ?>
</div>
