<?php
/**
 * Reviews widget: Google-style header + accessible carousel (scroll-snap track, prev/next buttons,
 * swipe, keyboard). Data: top_reviews_data() — Business Info today, Google Places later.
 */
defined( 'ABSPATH' ) || exit;
$data = top_reviews_data();
if ( ! $data['reviews'] ) {
	return;
}
$uid   = wp_unique_id( 'ta-reviews-' );
$stars = fn( int $n ) => '<span class="ta-stars" role="img" aria-label="' . esc_attr( sprintf( '%d out of 5 stars', $n ) ) . '">' . str_repeat( top_icon( 'star', '', 16 ), $n ) . '</span>';

// Header numbers: real values from Business Info; in preview, computed from the samples (labelled).
$rating = $data['rating'];
$count  = $data['count'];
if ( $data['preview'] && ! $rating ) {
	$rating = array_sum( array_column( $data['reviews'], 'rating' ) ) / count( $data['reviews'] );
	$count  = count( $data['reviews'] );
}
?>
<div <?php echo top_block_wrapper( 'ta-rv' ); ?> data-carousel>
	<div class="ta-rv__head">
		<?php echo top_icon( 'google', 'ta-rv__g', 40 ); ?>
		<div class="ta-rv__summary">
			<p class="ta-rv__label">Google reviews</p>
			<?php if ( $rating ) : ?>
				<p class="ta-rv__score"><strong><?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?></strong><?php echo $stars( (int) round( $rating ) ); // phpcs:ignore ?><?php if ( $count ) : ?><span><?php echo esc_html( sprintf( _n( '%s review', '%s reviews', $count ), number_format_i18n( $count ) ) ); ?></span><?php endif; ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $data['url'] ) : ?>
			<a class="ta-rv__all" href="<?php echo esc_url( $data['url'] ); ?>" rel="noopener" target="_blank">See all reviews on Google →<span class="screen-reader-text"> (opens in a new tab)</span></a>
		<?php endif; ?>
	</div>
	<?php if ( $data['preview'] ) : ?>
		<p class="ta-rv__preview">Preview — sample reviews. Only visible on local/development sites or to admins; add real reviews in Settings → Business Info.</p>
	<?php endif; ?>
	<div class="ta-rv__viewport" role="region" aria-roledescription="carousel" aria-label="Customer reviews">
		<ul class="ta-rv__track" id="<?php echo esc_attr( $uid ); ?>" tabindex="0">
			<?php foreach ( $data['reviews'] as $i => $r ) :
				$rel = top_relative_date( $r['date'] ?? '' );
				?>
				<li class="ta-rv__card<?php echo 'sample' === $r['source'] ? ' is-sample' : ''; ?>" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( '%d of %d', $i + 1, count( $data['reviews'] ) ) ); ?>">
					<div class="ta-rv__who">
						<span class="ta-rv__avatar" aria-hidden="true"><?php echo esc_html( mb_strtoupper( mb_substr( $r['name'], 0, 1 ) ) ); ?></span>
						<span class="ta-rv__meta">
							<span class="ta-rv__name"><?php echo esc_html( $r['name'] ); ?></span>
							<?php if ( $rel ) : ?><time class="ta-rv__date" datetime="<?php echo esc_attr( $r['date'] ); ?>"><?php echo esc_html( $rel ); ?></time><?php endif; ?>
						</span>
					</div>
					<?php echo $stars( (int) $r['rating'] ); // phpcs:ignore ?>
					<p class="ta-rv__text" id="<?php echo esc_attr( $uid . '-t' . $i ); ?>"><?php echo esc_html( $r['text'] ); ?></p>
					<button class="ta-rv__more" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $uid . '-t' . $i ); ?>" hidden>Read more</button>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div class="ta-rv__nav">
		<button class="ta-rv__btn" type="button" data-dir="-1" aria-controls="<?php echo esc_attr( $uid ); ?>" aria-label="Previous reviews"><?php echo top_icon( 'chevron-down', 'ta-rv__prev' ); ?></button>
		<button class="ta-rv__btn" type="button" data-dir="1" aria-controls="<?php echo esc_attr( $uid ); ?>" aria-label="Next reviews"><?php echo top_icon( 'chevron-down', 'ta-rv__next' ); ?></button>
	</div>
</div>
