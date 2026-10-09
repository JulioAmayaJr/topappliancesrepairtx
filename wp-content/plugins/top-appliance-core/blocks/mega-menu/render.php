<?php
/**
 * Mega menu. Desktop: disclosure buttons + panels (CSS hover/focus-within fallback, JS adds
 * aria-expanded/Escape). Mobile: call button + popover drawer with <details> groups (works without JS).
 */
defined( 'ABSPATH' ) || exit;

$data     = top_nav_data();
$services = $data['service'] ?? array();
$brands   = $data['brand'] ?? array();
$areas    = $data['service_area'] ?? array();
$apps     = $data['appliance'] ?? array();
$name     = top_biz( 'name' );
$phone    = top_phone();

$sections = array(
	'services'  => array(
		'label' => 'Services',
		'all'   => array( get_post_type_archive_link( 'service' ), 'All repair services' ),
		'items' => array_map( fn( $s ) => array( $s['url'], $s['short'] . ' repair', $s['icon'] ), $services ),
		'logos' => false,
		'aside' => array( 'Same-day appointments', sprintf( 'Free estimate and a %s on parts and labor.', top_warranty_label() ) ),
	),
	'brands'    => array(
		'label' => 'Brands',
		'all'   => array( get_post_type_archive_link( 'brand' ), 'All brands we service' ),
		'items' => array_map( fn( $b ) => array( $b['url'], $b['title'], '', $b['logo'] ?? 0 ), $brands ),
		'logos' => true,
		'aside' => array( 'Every major brand', 'From everyday Whirlpool and GE to built-in Sub-Zero and Thermador.' ),
	),
	'areas'     => array(
		'label' => 'Service Areas',
		'all'   => array( get_post_type_archive_link( 'service_area' ), 'All service areas' ),
		'items' => array_map( fn( $a ) => array( $a['url'], $a['title'], '' ), $areas ),
		'logos' => false,
		'aside' => array( 'Greater Houston', 'Technicians dispatched across Houston and the suburbs.' ),
	),
	'resources' => array(
		'label' => 'Resources',
		'all'   => array( get_post_type_archive_link( 'error_code' ), 'All appliance error codes' ),
		'items' => array_merge(
			array_map( fn( $t ) => array( $t['url'], $t['title'] . ' error codes', 'error-code' ), $apps ),
			array( array( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ), 'Repair tips blog', 'book' ) )
		),
		'logos' => false,
		'aside' => array( 'Before you call', 'Look up what a display code means and read our repair tips.' ),
	),
);
/** Brand card: lazy logo (alt="" — the visible name is inside the same link) + name. */
$brand_card = function ( array $item ): string {
	$img = $item[3] ? wp_get_attachment_image( (int) $item[3], 'full', false, array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async', 'class' => 'ta-mlogo__img', 'sizes' => '96px' ) ) : '';
	return sprintf( '<a class="ta-mlogo" href="%s">%s<span class="ta-mlogo__name">%s</span></a>', esc_url( $item[0] ), $img, esc_html( $item[1] ) );
};
$links = array(
	array( home_url( '/about/' ), 'About' ),
	array( home_url( '/contact/' ), 'Contact' ),
);
?>
<div <?php echo top_block_wrapper( 'ta-menu' ); ?>>
	<nav class="ta-nav" aria-label="Main">
		<ul class="ta-nav__list">
			<?php foreach ( $sections as $id => $sec ) : ?>
				<li class="ta-nav__item ta-nav__item--panel">
					<button class="ta-nav__trigger" type="button" aria-expanded="false" aria-controls="ta-panel-<?php echo esc_attr( $id ); ?>">
						<?php echo esc_html( $sec['label'] ); ?><?php echo top_icon( 'chevron-down', 'ta-nav__chev', 16 ); ?>
					</button>
					<div class="ta-mega ta-mega--<?php echo esc_attr( $id ); ?>" id="ta-panel-<?php echo esc_attr( $id ); ?>">
						<div class="ta-mega__inner">
							<ul class="ta-mega__list<?php echo 'services' === $id || 'resources' === $id ? ' ta-mega__list--icons' : ''; ?><?php echo $sec['logos'] ? ' ta-mega__list--logos' : ''; ?>">
								<?php foreach ( $sec['items'] as $item ) : ?>
									<?php if ( $sec['logos'] ) : ?>
										<li><?php echo $brand_card( $item ); // phpcs:ignore ?></li>
									<?php else : ?>
										<li><a href="<?php echo esc_url( $item[0] ); ?>"><?php echo $item[2] ? top_icon( $item[2], '', 22 ) : ''; ?><span><?php echo esc_html( $item[1] ); ?></span></a></li>
									<?php endif; ?>
								<?php endforeach; ?>
							</ul>
							<div class="ta-mega__aside">
								<p class="ta-mega__title"><?php echo esc_html( $sec['aside'][0] ); ?></p>
								<p><?php echo esc_html( $sec['aside'][1] ); ?></p>
								<?php if ( $sec['all'][0] ) : ?>
									<a class="ta-mega__all" href="<?php echo esc_url( $sec['all'][0] ); ?>"><?php echo esc_html( $sec['all'][1] ); ?></a>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
			<?php foreach ( $links as $l ) : ?>
				<li class="ta-nav__item"><a class="ta-nav__link" href="<?php echo esc_url( $l[0] ); ?>"><?php echo esc_html( $l[1] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</nav>

	<div class="ta-menu__mobile">
		<?php if ( $phone ) : ?>
			<a class="ta-icon-btn ta-icon-btn--call" href="<?php echo esc_attr( top_phone_href() ); ?>" aria-label="<?php echo esc_attr( 'Call ' . $phone ); ?>"><?php echo top_icon( 'phone' ); ?></a>
		<?php endif; ?>
		<button class="ta-icon-btn ta-icon-btn--menu" type="button" popovertarget="ta-drawer" aria-controls="ta-drawer" aria-label="Open menu"><?php echo top_icon( 'menu' ); ?></button>
	</div>

	<div class="ta-drawer" id="ta-drawer" popover>
		<div class="ta-drawer__head">
			<p class="ta-drawer__title"><?php echo esc_html( $name ); ?></p>
			<button class="ta-icon-btn" type="button" popovertarget="ta-drawer" popovertargetaction="hide" aria-label="Close menu"><?php echo top_icon( 'close' ); ?></button>
		</div>
		<nav class="ta-drawer__nav" aria-label="Main, mobile">
			<?php foreach ( $sections as $id => $sec ) : ?>
				<details class="ta-drawer__group">
					<summary><?php echo esc_html( $sec['label'] ); ?><?php echo top_icon( 'chevron-down', '', 18 ); ?></summary>
					<ul<?php echo $sec['logos'] ? ' class="ta-drawer__logos"' : ''; ?>>
						<?php foreach ( $sec['items'] as $item ) : ?>
							<li><?php echo $sec['logos'] ? $brand_card( $item ) : '<a href="' . esc_url( $item[0] ) . '">' . esc_html( $item[1] ) . '</a>'; // phpcs:ignore ?></li>
						<?php endforeach; ?>
						<?php if ( $sec['all'][0] ) : ?>
							<li><a class="ta-drawer__all" href="<?php echo esc_url( $sec['all'][0] ); ?>"><?php echo esc_html( $sec['all'][1] ); ?></a></li>
						<?php endif; ?>
					</ul>
				</details>
			<?php endforeach; ?>
			<ul class="ta-drawer__plain">
				<?php foreach ( $links as $l ) : ?>
					<li><a href="<?php echo esc_url( $l[0] ); ?>"><?php echo esc_html( $l[1] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<div class="ta-drawer__ctas">
			<?php if ( $phone ) : ?>
				<a class="ta-btn ta-btn--call" href="<?php echo esc_attr( top_phone_href() ); ?>"><?php echo top_icon( 'phone' ); ?><span><?php echo esc_html( 'Call ' . $phone ); ?></span></a>
			<?php endif; ?>
			<a class="ta-btn ta-btn--book" href="<?php echo esc_url( top_booking_url() ); ?>"><?php echo top_icon( 'calendar' ); ?><span>Book Service</span></a>
			<p class="ta-drawer__hours"><?php echo esc_html( top_hours_summary() ); ?></p>
		</div>
	</div>
</div>
