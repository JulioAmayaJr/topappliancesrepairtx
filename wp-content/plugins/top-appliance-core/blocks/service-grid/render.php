<?php
/** Service tiles ("control-panel keys"), from the Services post type. */
defined( 'ABSPATH' ) || exit;
$items = top_nav_data()['service'] ?? array();
$limit = (int) ( $attributes['limit'] ?? 0 );
if ( $limit > 0 ) {
	$items = array_slice( $items, 0, $limit );
}
if ( ! $items ) {
	return;
}
$tag = ! empty( $attributes['showTagline'] );
?>
<ul <?php echo top_block_wrapper( 'ta-services' ); ?> data-stagger>
	<?php foreach ( $items as $s ) : ?>
		<li class="ta-service">
			<a class="ta-service__link" href="<?php echo esc_url( $s['url'] ); ?>">
				<span class="ta-service__icon"><?php echo top_icon( $s['icon'], '', 28 ); ?></span>
				<span class="ta-service__text">
					<span class="ta-service__name"><?php echo esc_html( $s['short'] ); ?> <span class="ta-service__suffix">repair</span></span>
					<?php if ( $tag && $s['tagline'] ) : ?>
						<span class="ta-service__tagline"><?php echo esc_html( $s['tagline'] ); ?></span>
					<?php endif; ?>
				</span>
				<span class="ta-service__led" aria-hidden="true"></span>
			</a>
		</li>
	<?php endforeach; ?>
	<?php if ( ! empty( $attributes['showAll'] ) ) : ?>
		<li class="ta-service ta-service--all">
			<a class="ta-service__link" href="<?php echo esc_url( get_post_type_archive_link( 'service' ) ); ?>">
				<span class="ta-service__icon"><?php echo top_icon( 'grid', '', 28 ); ?></span>
				<span class="ta-service__text">
					<span class="ta-service__name">All repair services</span>
					<?php if ( $tag ) : ?><span class="ta-service__tagline">Every appliance we fix</span><?php endif; ?>
				</span>
			</a>
		</li>
	<?php endif; ?>
</ul>
