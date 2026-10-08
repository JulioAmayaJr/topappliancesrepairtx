<?php
/**
 * Service-area map facade: a lightweight SVG drawn from real coordinates (Business Info +
 * Service Area meta). The Google Maps iframe is injected only after the visitor clicks.
 */
defined( 'ABSPATH' ) || exit;

$areas = array_values( array_filter( top_nav_data()['service_area'] ?? array(), fn( $a ) => $a['lat'] && $a['lng'] ) );
$lat0  = (float) top_biz( 'lat' );
$lng0  = (float) top_biz( 'lng' );
if ( ! $areas || ! $lat0 ) {
	return;
}

// Fit bounds around every point (+ padding), equirectangular with cos(lat) correction.
$lats = array_merge( array( $lat0 ), array_column( $areas, 'lat' ) );
$lngs = array_merge( array( $lng0 ), array_column( $areas, 'lng' ) );
$pad  = 0.06;
$minLat = min( $lats ) - $pad;
$maxLat = max( $lats ) + $pad;
$minLng = min( $lngs ) - $pad * 1.6;
$maxLng = max( $lngs ) + $pad * 1.6;
$k  = cos( deg2rad( ( $minLat + $maxLat ) / 2 ) );
$W  = 600;
$H  = (int) round( $W * ( $maxLat - $minLat ) / ( ( $maxLng - $minLng ) * $k ) );
$px = fn( $lng ) => round( ( $lng - $minLng ) / ( $maxLng - $minLng ) * $W, 1 );
$py = fn( $lat ) => round( ( $maxLat - $lat ) / ( $maxLat - $minLat ) * $H, 1 );
$mi = $H / ( $maxLat - $minLat ) / 69.0; // px per mile (north–south)

// Label placement per area: [text-anchor, dx, dy]. Default: right of the dot.
$place = array(
	'houston'       => array( 'middle', 0, 28 ),
	'the-heights'   => array( 'end', -11, -6 ),
	'memorial'      => array( 'end', -11, -9 ),
	'katy'          => array( 'start', 10, -10 ),
	'cinco-ranch'   => array( 'start', 10, 24 ),
	'richmond'      => array( 'end', -11, 6 ),
	'sugar-land'    => array( 'end', -11, 6 ),
	'spring'        => array( 'end', -11, 6 ),
	'the-woodlands' => array( 'end', -11, 6 ),
);

$cx    = $px( $lng0 );
$cy    = $py( $lat0 );
$names = implode( ', ', array_column( $areas, 'title' ) );
$embed = top_biz( 'map_embed_url' );
$link  = top_biz( 'map_link_url' );
?>
<figure <?php echo top_block_wrapper( 'ta-map' ); ?> data-map-facade>
	<div class="ta-map__frame" style="aspect-ratio:<?php echo (int) $W . ' / ' . (int) $H; ?>">
		<svg class="ta-map__svg" viewBox="0 0 <?php echo (int) $W . ' ' . (int) $H; ?>" role="img" aria-labelledby="ta-map-title">
			<title id="ta-map-title"><?php echo esc_html( sprintf( 'Map of our service area around %s: %s.', top_biz( 'city' ), $names ) ); ?></title>
			<g class="ta-map__rings">
				<?php foreach ( array( 10, 20, 30 ) as $r ) : ?>
					<circle cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="<?php echo round( $r * $mi, 1 ); ?>"/>
					<text x="<?php echo $cx; ?>" y="<?php echo round( $cy - $r * $mi - 6, 1 ); ?>" text-anchor="middle"><?php echo (int) $r; ?> mi</text>
				<?php endforeach; ?>
			</g>
			<g class="ta-map__areas">
				<?php foreach ( $areas as $a ) :
					$slug = sanitize_title( $a['title'] );
					$x    = $px( $a['lng'] );
					$y    = $py( $a['lat'] );
					$pl   = $place[ $slug ] ?? array( 'start', 11, 6 );
					?>
					<circle cx="<?php echo $x; ?>" cy="<?php echo $y; ?>" r="6"/>
					<text x="<?php echo $x + $pl[1]; ?>" y="<?php echo $y + $pl[2]; ?>" text-anchor="<?php echo esc_attr( $pl[0] ); ?>"><?php echo esc_html( $a['title'] ); ?></text>
				<?php endforeach; ?>
			</g>
			<g class="ta-map__shop">
				<circle cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="15"/>
				<circle class="ta-map__shop-ring" cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="7.5"/>
			</g>
		</svg>
	</div>
	<div class="ta-map__bar">
		<figcaption class="ta-map__caption"><span class="ta-map__key" aria-hidden="true"></span><?php echo esc_html( sprintf( 'Our shop: %s. Rings show distance in miles.', top_full_address() ) ); ?></figcaption>
		<?php if ( $embed || $link ) : ?>
			<a class="ta-map__load ta-btn ta-btn--outline" href="<?php echo esc_url( $link ?: $embed ); ?>" data-embed="<?php echo esc_url( $embed ); ?>" data-title="<?php echo esc_attr( sprintf( 'Google map: %s, %s', top_biz( 'name' ), top_full_address() ) ); ?>"><?php echo top_icon( 'map-pin' ); ?><span>Load interactive map</span></a>
		<?php endif; ?>
	</div>
</figure>
