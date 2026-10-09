<?php
/**
 * Seed content (run by bin/setup.sh through `wp eval-file`). Idempotent: matches by slug.
 * Data: seed/content.json (services, brands, areas, appliances) and seed/posts.json (real posts
 * from the current site: titles, slugs, dates, meta descriptions).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$content = json_decode( file_get_contents( '/project/seed/content.json' ), true );
$posts   = json_decode( file_get_contents( '/project/seed/posts.json' ), true );

function top_upsert( array $args, array $meta = array() ): int {
	$existing = get_page_by_path( $args['post_name'], OBJECT, $args['post_type'] );
	$args     = array_merge( array( 'post_status' => 'publish', 'post_author' => 1, 'comment_status' => 'closed', 'ping_status' => 'closed' ), $args );
	if ( $existing ) {
		$args['ID'] = $existing->ID;
		$id         = wp_update_post( wp_slash( $args ), true );
	} else {
		$id = wp_insert_post( wp_slash( $args ), true );
	}
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( $id->get_error_message() );
		return 0;
	}
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, $k, $v );
	}
	return (int) $id;
}

function top_seed_image( string $file, string $key, string $title, string $alt ): int {
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_top_seed_key', 'meta_value' => $key, 'fields' => 'ids', 'numberposts' => 1 ) );
	if ( $found ) {
		return (int) $found[0];
	}
	$tmp = wp_tempnam( basename( $file ) );
	copy( $file, $tmp );
	$id = media_handle_sideload( array( 'name' => basename( $file ), 'tmp_name' => $tmp ), 0, $title );
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( $id->get_error_message() );
		return 0;
	}
	update_post_meta( $id, '_top_seed_key', $key );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	return (int) $id;
}

$p = fn( string $text ) => "<!-- wp:paragraph -->\n<p>" . esc_html( $text ) . "</p>\n<!-- /wp:paragraph -->";
$todo = '<!-- wp:paragraph {"className":"ta-todo"} -->' . "\n<p class=\"ta-todo\">Full page content will be written in the next phase.</p>\n<!-- /wp:paragraph -->";

/* ---------------------------------------------------------------- Appliances */
foreach ( $content['appliances'] as $name ) {
	if ( ! term_exists( $name, 'appliance' ) ) {
		wp_insert_term( $name, 'appliance' );
	}
}

/* ------------------------------------------------------------------ Services */
foreach ( $content['services'] as $i => $s ) {
	$lower = strtolower( $s['short'] );
	top_upsert(
		array(
			'post_type'    => 'service',
			'post_name'    => $s['slug'],
			'post_title'   => $s['title'],
			'menu_order'   => $i,
			'post_excerpt' => sprintf( 'Same-day %s service in Houston, TX: free estimate and a warranty on parts and labor.', strtolower( $s['title'] ) ),
			'post_content' => $p( sprintf( 'Need %s in Houston? Top Appliances Repair sends a technician the same day, gives you a free estimate before any work starts and covers the repair with a warranty on parts and labor. We service every major brand, including Sub-Zero.', strtolower( $s['title'] ) ) ) . "\n\n" . $todo,
		),
		array( 'top_icon' => $s['icon'], 'top_short_name' => $s['short'], 'top_tagline' => $s['tagline'] )
	);
	unset( $lower );
}

/* -------------------------------------------------------------------- Brands */
// Brand logos stay WebP (no AVIF conversion), 200x80 uniform canvases from scripts/brand-logos.py.
$logo_files = array( 'Sub-Zero' => 'sub-zero', 'Whirlpool' => 'whirlpool', 'GE' => 'ge', 'Samsung' => 'samsung', 'LG' => 'lg', 'Bosch' => 'bosch', 'Thermador' => 'thermador', 'KitchenAid' => 'kitchenaid', 'Jenn-Air' => 'jenn-air', 'Maytag' => 'maytag', 'Kenmore' => 'kenmore', 'Viking' => 'viking', 'Amana' => 'amana', 'Admiral' => 'admiral', 'Thor' => 'thor' );
$output_filters = $GLOBALS['wp_filter']['image_editor_output_format'] ?? null;
remove_all_filters( 'image_editor_output_format' );
$logo_ids = array();
foreach ( $logo_files as $brand => $file ) {
	$logo_ids[ $brand ] = top_seed_image( '/project/seed/brands/' . $file . '.webp', 'brand-logo-' . $file, $brand . ' logo', $brand . ' appliance repair' );
}
if ( $output_filters ) {
	$GLOBALS['wp_filter']['image_editor_output_format'] = $output_filters;
}
foreach ( $content['brands'] as $i => $b ) {
	top_upsert(
		array(
			'post_type'    => 'brand',
			'post_name'    => sanitize_title( $b['title'] ),
			'post_title'   => $b['title'],
			'menu_order'   => $i,
			'post_excerpt' => sprintf( '%s appliance repair in Houston, TX with same-day service.', $b['title'] ),
			'post_content' => $p( sprintf( 'Top Appliances Repair repairs %s appliances across Houston with same-day service, a free estimate and a warranty on parts and labor.', $b['title'] ) ) . "\n\n" . $todo,
		),
		array( 'top_featured' => true, 'top_premium' => (bool) $b['premium'], 'top_logo_id' => $logo_ids[ $b['title'] ] ?? 0 )
	);
}

/* ------------------------------------------------------------- Service areas */
foreach ( $content['areas'] as $i => $a ) {
	top_upsert(
		array(
			'post_type'    => 'service_area',
			'post_name'    => $a['slug'],
			'post_title'   => $a['title'],
			'menu_order'   => $i,
			'post_excerpt' => sprintf( 'Same-day appliance repair in %s, TX.', $a['title'] ),
			'post_content' => $p( sprintf( 'Top Appliances Repair serves homes in %s, TX with same-day appliance repair, a free estimate and a warranty on parts and labor.', $a['title'] ) ) . "\n\n" . $todo,
		),
		array( 'top_zips' => implode( ',', $a['zips'] ), 'top_lat' => $a['lat'], 'top_lng' => $a['lng'], 'top_county' => $a['county'] )
	);
}

/* ---------------------------------------------- Blog posts (from current site) */
$blog_cat = get_term_by( 'slug', 'uncategorized', 'category' );
if ( $blog_cat ) {
	wp_update_term( $blog_cat->term_id, 'category', array( 'name' => 'Repair Tips', 'slug' => 'repair-tips' ) );
}
foreach ( $posts as $post ) {
	$id = top_upsert(
		array(
			'post_type'     => 'post',
			'post_name'     => $post['slug'],
			'post_title'    => $post['title'],
			'post_excerpt'  => $post['excerpt'],
			'post_date'     => $post['date'],
			'post_date_gmt' => get_gmt_from_date( $post['date'] ),
			'post_content'  => $p( $post['excerpt'] ) . "\n\n" . '<!-- wp:paragraph {"className":"ta-todo"} -->' . "\n<p class=\"ta-todo\">Migrate the full article from " . esc_url( $post['source'] ) . "</p>\n<!-- /wp:paragraph -->",
		)
	);
	if ( $id ) {
		$img = top_seed_image( '/project/seed/images/brand-16x9.webp', 'brand-16x9', 'Top Appliances Repair brand image (replace with the article photo)', 'Top Appliances Repair' );
		if ( $img ) {
			set_post_thumbnail( $id, $img );
		}
	}
}

/* --------------------------------------------------------------------- Pages */
$home_sections = array( 'hero', 'trust-bar', 'services', 'how-it-works', 'why-choose-us', 'brands', 'reviews', 'service-areas', 'resources', 'faq', 'cta-form' );
$home_content  = implode( "\n\n", array_map( fn( $s ) => '<!-- wp:pattern {"slug":"top-appliance/' . $s . '"} /-->', $home_sections ) );
$home = top_upsert(
	array( 'post_type' => 'page', 'post_name' => 'home', 'post_title' => 'Home', 'post_content' => $home_content ),
	array(
		'rank_math_title'          => 'Same-Day Appliance Repair in Houston, TX | Top Appliances Repair',
		'rank_math_description'    => 'Same-day appliance repair in Houston, TX. Free estimate, 90-day warranty on parts and labor, all major brands including Sub-Zero. Call or book online.',
		'rank_math_focus_keyword'  => 'appliance repair houston',
		'rank_math_facebook_title' => 'Same-Day Appliance Repair in Houston, TX',
		'rank_math_facebook_image' => wp_get_attachment_url( (int) ( get_option( 'top_media', array() )['og'] ?? 0 ) ),
		'rank_math_facebook_image_id' => (int) ( get_option( 'top_media', array() )['og'] ?? 0 ),
		'rank_math_twitter_use_facebook' => 'on',
		'rank_math_robots'         => array( 'index' ),
	)
);
$about = top_upsert(
	array(
		'post_type'    => 'page',
		'post_name'    => 'about',
		'post_title'   => 'About Top Appliances Repair',
		'post_content' => $p( 'Top Appliances Repair is a Houston appliance repair company with more than 10 years of experience. We offer same-day service, a free estimate and a 90-day warranty on parts and labor, for every major brand including Sub-Zero.' ) . "\n\n" . $todo,
	),
	array( 'rank_math_description' => 'Meet Top Appliances Repair: a Houston appliance repair company with more than 10 years of experience, same-day service and a 90-day warranty.' )
);
$contact = top_upsert(
	array(
		'post_type'    => 'page',
		'post_name'    => 'contact',
		'post_title'   => 'Contact Us',
		'post_content' => $p( 'Call us, or send the form below and we will contact you to confirm a same-day visit.' ) . "\n\n" . '<!-- wp:pattern {"slug":"top-appliance/cta-form"} /-->',
	),
	array( 'rank_math_description' => 'Contact Top Appliances Repair in Houston, TX. Call or book same-day appliance repair online.' )
);
$blog = top_upsert(
	array( 'post_type' => 'page', 'post_name' => 'blog', 'post_title' => 'Appliance Repair Tips', 'post_content' => '' ),
	array( 'rank_math_description' => 'Appliance repair tips from Houston technicians: refrigerators, washers, dishwashers, stoves and more.' )
);
$privacy = top_upsert(
	array(
		'post_type'    => 'page',
		'post_name'    => 'privacy-policy',
		'post_title'   => 'Privacy Policy',
		'post_content' => $p( 'Top Appliances Repair collects the information you send through our booking form (name, phone, ZIP code, appliance, brand and a description of the problem) only to respond to your request and schedule your repair. We do not sell your information. To ask us to update or delete your information, contact us by phone or email.' ) . "\n\n" . '<!-- wp:paragraph {"className":"ta-todo"} -->' . "\n<p class=\"ta-todo\">Review this policy with the client before launch.</p>\n<!-- /wp:paragraph -->",
	)
);
update_option( 'wp_page_for_privacy_policy', $privacy );
top_upsert(
	array(
		'post_type'    => 'page',
		'post_name'    => 'terms',
		'post_title'   => 'Terms of Service',
		'post_content' => $p( 'Estimates are provided before any work starts and repairs begin only after you approve them. Repairs are covered by our warranty on parts and labor.' ) . "\n\n" . '<!-- wp:paragraph {"className":"ta-todo"} -->' . "\n<p class=\"ta-todo\">Write the full terms with the client before launch.</p>\n<!-- /wp:paragraph -->",
	)
);

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home );
update_option( 'page_for_posts', $blog );

delete_transient( 'top_nav_data' );
WP_CLI::success( 'Seed content ready' );
