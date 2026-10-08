<?php
/** Trust highlights. Numbers come from Business Info and animate (count up) in the theme JS. */
defined( 'ABSPATH' ) || exit;
$days  = (int) top_biz( 'warranty_days' );
$years = (int) top_biz( 'years' );
$items = array(
	array( 'clock', 'Same-day service', sprintf( 'Across %s and nearby', top_biz( 'city' ) ) ),
);
if ( $days ) {
	$items[] = array( 'shield', sprintf( '<span class="ta-count" data-count="%1$d">%1$d</span>-day warranty', $days ), 'On parts and labor' );
}
if ( $years ) {
	$items[] = array( 'medal', sprintf( '<span class="ta-count" data-count="%1$d">%1$d</span>+ years', $years ), 'Repairing Houston appliances' );
}
$items[] = array( 'clipboard', 'Free estimate', 'Know the cost before work starts' );
?>
<ul <?php echo top_block_wrapper( 'ta-trust' ); ?>>
	<?php foreach ( $items as $i ) : ?>
		<li class="ta-trust__item">
			<span class="ta-trust__icon"><?php echo top_icon( $i[0], '', 28 ); ?></span>
			<span class="ta-trust__text">
				<strong class="ta-trust__title"><?php echo wp_kses( $i[1], array( 'span' => array( 'class' => true, 'data-count' => true ) ) ); ?></strong>
				<span class="ta-trust__sub"><?php echo esc_html( $i[2] ); ?></span>
			</span>
		</li>
	<?php endforeach; ?>
</ul>
