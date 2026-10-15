<?php
/**
 * Service-specific sections for single-service pages. Each renders its own heading so the
 * template stays generic: brands | areas | faq | related | costs.
 */
defined( 'ABSPATH' ) || exit;
$post_id = (int) ( $block->context['postId'] ?? get_the_ID() );
$section = $attributes['section'] ?? 'brands';
if ( ! $post_id ) {
	return;
}
$short = get_post_meta( $post_id, 'top_short_name', true ) ?: get_the_title( $post_id );
$lower = strtolower( $short );
$data  = top_nav_data();

$open = function ( string $class, string $eyebrow, string $h2, string $lede = '', bool $center = true ) {
	printf(
		'<section %s><div class="ta-wrap"><div class="ta-section__head%s"><p class="ta-eyebrow">%s</p><h2 class="wp-block-heading">%s</h2>%s</div>',
		top_block_wrapper( 'ta-section ' . $class ), // phpcs:ignore
		$center ? ' ta-section__head--center' : '',
		esc_html( $eyebrow ),
		esc_html( $h2 ),
		$lede ? '<p class="ta-section__lede">' . esc_html( $lede ) . '</p>' : ''
	);
};
$close = fn() => print( '</div></section>' );

switch ( $section ) {
	case 'brands':
		$slugs = (array) get_post_meta( $post_id, 'top_brands', true );
		$items = array_values( array_filter( $data['brand'] ?? array(), fn( $b ) => in_array( basename( untrailingslashit( $b['url'] ) ), $slugs, true ) ) );
		if ( ! $items ) {
			$items = $data['brand'] ?? array();
		}
		$open( 'ta-section--tint ta-svc-brands', 'Brands we repair', sprintf( '%s brands we repair', $short ), sprintf( 'Everyday and high-end %s models, including Sub-Zero where it applies. Pick your brand for details.', $lower ) );
		echo '<ul class="ta-brandwall">';
		foreach ( $items as $b ) {
			printf(
				'<li><a class="ta-brandcard" href="%s"><span class="ta-brandcard__logo">%s</span><span class="ta-brandcard__name" aria-hidden="true">%s</span></a></li>',
				esc_url( $b['url'] ),
				$b['logo'] ? wp_get_attachment_image( $b['logo'], 'full', false, array( 'alt' => sprintf( '%s %s repair', $b['title'], $lower ), 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '160px' ) ) : esc_html( $b['title'] ), // phpcs:ignore
				esc_html( $b['title'] )
			);
		}
		echo '</ul><p class="ta-more ta-more--center"><a href="' . esc_url( get_post_type_archive_link( 'brand' ) ) . '">View all brands we service →</a></p>';
		$close();
		break;

	case 'areas':
		$areas = $data['service_area'] ?? array();
		if ( ! $areas ) {
			return;
		}
		$open( 'ta-section--tint ta-svc-areas', 'Service areas', sprintf( '%s repair across Greater Houston', $short ), sprintf( 'Same-day %s repair in Houston and these nearby cities. Choose yours to see local details.', $lower ) );
		echo '<ul class="ta-links ta-links--chips ta-links--center">';
		foreach ( $areas as $a ) {
			printf( '<li><a href="%s">%s%s</a></li>', esc_url( $a['url'] ), top_icon( 'map-pin', '', 16 ), esc_html( $a['title'] ) ); // phpcs:ignore
		}
		echo '</ul>';
		$close();
		break;

	case 'faq':
		$faqs = array_filter( (array) get_post_meta( $post_id, 'top_faqs', true ), fn( $f ) => ! empty( $f['q'] ) && ! empty( $f['a'] ) );
		if ( ! $faqs ) {
			return;
		}
		printf( '<section %s><div class="ta-wrap ta-faq-sec__inner"><div class="ta-faq-sec__head"><p class="ta-eyebrow">FAQ</p><h2 class="wp-block-heading">%s</h2><p class="ta-section__lede">%s</p></div><div class="ta-faq">', top_block_wrapper( 'ta-section ta-faq-sec ta-svc-faq' ), esc_html( sprintf( '%s repair questions', $short ) ), esc_html( sprintf( 'Answers to what Houston homeowners ask us about %s repair.', $lower ) ) ); // phpcs:ignore
		foreach ( $faqs as $f ) {
			printf( '<details class="wp-block-details"><summary>%s</summary><p>%s</p></details>', esc_html( $f['q'] ), wp_kses( $f['a'], array( 'a' => array( 'href' => true ), 'strong' => array() ) ) );
		}
		echo '</div></div></section>';
		break;

	case 'related':
		$others = array_values( array_filter( $data['service'] ?? array(), fn( $s ) => (int) $s['id'] !== $post_id ) );
		if ( ! $others ) {
			return;
		}
		$open( 'ta-section--tint ta-svc-related', 'Other services', 'Other appliances we repair', 'Same-day service, a free estimate and a warranty on parts and labor for every appliance below.' );
		echo '<ul class="ta-services ta-services--compact">';
		foreach ( $others as $s ) {
			printf(
				'<li class="ta-service"><a class="ta-service__link" href="%s"><span class="ta-service__icon">%s</span><span class="ta-service__text"><span class="ta-service__name">%s <span class="ta-service__suffix">repair</span></span></span><span class="ta-service__led" aria-hidden="true"></span></a></li>',
				esc_url( $s['url'] ),
				top_icon( $s['icon'], '', 26 ), // phpcs:ignore
				esc_html( $s['short'] )
			);
		}
		echo '</ul>';
		$close();
		break;

	case 'costs':
		// Shown only when approved prices exist for this service (none today: no invented prices).
		$rows = array_filter( (array) get_post_meta( $post_id, 'top_costs', true ), fn( $r ) => ! empty( $r['repair'] ) && ! empty( $r['range'] ) );
		if ( ! $rows ) {
			return;
		}
		$open( 'ta-svc-costs', 'Repair costs', sprintf( 'Typical %s repair costs', $lower ), 'Every visit starts with a free estimate, so you know the exact price before any work begins.' );
		echo '<div class="ta-costs"><table><caption class="screen-reader-text">' . esc_html( sprintf( 'Typical %s repair costs', $lower ) ) . '</caption><thead><tr><th scope="col">Repair</th><th scope="col">Typical range</th><th scope="col">Notes</th></tr></thead><tbody>';
		foreach ( $rows as $r ) {
			printf( '<tr><th scope="row">%s</th><td>%s</td><td>%s</td></tr>', esc_html( $r['repair'] ), esc_html( $r['range'] ), esc_html( $r['note'] ?? '' ) );
		}
		echo '</tbody></table></div>';
		$close();
		break;
}
