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

require_once '/project/bin/content.php';

$content = json_decode( file_get_contents( '/project/seed/content.json' ), true );
$posts   = json_decode( file_get_contents( '/project/seed/posts.json' ), true );
$brand_data = json_decode( file_get_contents( '/project/seed/brands.json' ), true );
$page_data  = json_decode( file_get_contents( '/project/seed/pages.json' ), true );
$new_posts  = json_decode( file_get_contents( '/project/seed/posts-new.json' ), true );

// Each service/brand page links to the article written for it, so the link runs both ways.
$related_reading = array(
	'service:dryer-repair'             => array( array( 'dryer-not-heating', 'Dryer not heating? Causes and fixes' ) ),
	'service:dishwasher-repair'        => array( array( 'dishwasher-not-draining', 'Dishwasher not draining: causes and fixes' ) ),
	'service:ice-maker-repair'         => array( array( 'ice-maker-not-making-ice', 'Ice maker not making ice? Here is why' ), array( 'samsung-ice-maker-freezing-up', 'Samsung ice maker freezing up?' ) ),
	'service:stove-oven-repair'        => array( array( 'oven-not-heating', 'Oven not heating? Gas and electric causes' ) ),
	'service:freezer-repair'           => array( array( 'freezer-not-freezing', 'Freezer not freezing? What to check' ) ),
	'service:garbage-disposal-repair'  => array( array( 'garbage-disposal-humming', 'Garbage disposal humming but not working?' ) ),
	'service:refrigerator-repair'      => array( array( 'refrigerator-not-cooling-what-might-be-wrong', 'Refrigerator not cooling: what might be wrong' ), array( 'freezer-not-freezing', 'Freezer not freezing? What to check' ) ),
	'service:washer-repair'            => array( array( 'what-to-do-if-your-washing-machine-is-not-draining', 'Washing machine not draining? What to do' ) ),
	'brand:samsung'                    => array( array( 'samsung-ice-maker-freezing-up', 'Samsung ice maker freezing up? Why it keeps happening' ) ),
	'brand:bosch'                      => array( array( 'bosch-dishwasher-not-drying', 'Bosch dishwasher not drying? How condensation drying works' ) ),
	'brand:ge'                         => array( array( 'ice-maker-not-making-ice', 'Ice maker not making ice? Here is why' ) ),
	'brand:whirlpool'                  => array( array( 'dryer-not-heating', 'Dryer not heating? Causes and fixes' ) ),
);
$area_data  = json_decode( file_get_contents( '/project/seed/areas.json' ), true );

// Lookup tables used by the brand/area content builders for internal links.
$service_names = array();
foreach ( $content['services'] as $s ) {
	$service_names[ $s['slug'] ] = $s['title'];
}
$brand_names = array();
foreach ( $content['brands'] as $b ) {
	$brand_names[ sanitize_title( $b['title'] ) ] = $b['title'];
}
$area_titles = array();
foreach ( $content['areas'] as $a ) {
	$area_titles[ $a['slug'] ] = $a['title'];
}

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

/** Related-reading section for an entity, or '' when it has no article yet. */
function top_related_block( array $map, string $key ): string {
	return isset( $map[ $key ] ) ? "\n\n" . top_build_related_reading( $map[ $key ] ) : '';
}

/** Attachment ID by seed key (imported by bin/configure.php). */
function top_seed_attachment( string $key ): int {
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_top_seed_key', 'meta_value' => $key, 'fields' => 'ids', 'numberposts' => 1 ) );
	return $found ? (int) $found[0] : 0;
}

/*
 * Landscape photo per service, used as featured image so Rank Math picks it as
 * og:image / twitter:image (services without one fall back to the branded PNG).
 */
$service_featured = array(
	'refrigerator-repair' => 'photo-refrigerator-evaporator-coil-houston-tx-16x9',
	'stove-oven-repair'   => 'photo-gas-range-stove-houston-tx-16x9',
	'cooktop-repair'      => 'photo-gas-cooktop-repair-houston-tx',
);

/* JPG social image per post/service (og:image must be PNG/JPG — social networks reject AVIF). */
$social_jpg = array(
	'refrigerator-repair'                          => 'og-refrigerator-repair-houston-tx',
	'stove-oven-repair'                            => 'og-stove-oven-repair-houston-tx',
	'cooktop-repair'                               => 'og-cooktop-repair-houston-tx',
	'refrigerator-not-cooling-what-might-be-wrong' => 'og-refrigerator-repair-houston-tx',
	'what-happens-when-a-refrigerator'             => 'og-refrigerator-compressor-houston-tx',
	'what-are-the-different-types-of-stoves'       => 'og-stove-oven-repair-houston-tx',
);

/** Set Rank Math's social image metas from a seed key. */
function top_set_social_image( int $post_id, string $key ): void {
	$id = top_seed_attachment( $key );
	if ( ! $id ) {
		return;
	}
	update_post_meta( $post_id, 'rank_math_facebook_image', wp_get_attachment_url( $id ) );
	update_post_meta( $post_id, 'rank_math_facebook_image_id', $id );
	update_post_meta( $post_id, 'rank_math_twitter_use_facebook', 'on' );
}
$todo = '<!-- wp:paragraph {"className":"ta-todo"} -->' . "\n<p class=\"ta-todo\">Full page content will be written in the next phase.</p>\n<!-- /wp:paragraph -->";

/* ---------------------------------------------------------------- Appliances */
foreach ( $content['appliances'] as $name ) {
	if ( ! term_exists( $name, 'appliance' ) ) {
		wp_insert_term( $name, 'appliance' );
	}
}

/* ------------------------------------------------------------------ Services */
// Finished services: seed/services/<slug>.html (block markup) + <slug>.json (SEO, H1, FAQs, brands).
// Services without files keep the provisional text (editor-only "content pending" note).
foreach ( $content['services'] as $i => $s ) {
	$html_file = '/project/seed/services/' . $s['slug'] . '.html';
	$json_file = '/project/seed/services/' . $s['slug'] . '.json';
	$meta      = array( 'top_icon' => $s['icon'], 'top_short_name' => $s['short'], 'top_tagline' => $s['tagline'] );
	if ( file_exists( $html_file ) && file_exists( $json_file ) ) {
		$data = json_decode( file_get_contents( $json_file ), true );
		$sid  = top_upsert(
			array(
				'post_type'    => 'service',
				'post_name'    => $s['slug'],
				'post_title'   => $data['title'] ?? $s['title'],
				'menu_order'   => $i,
				'post_excerpt' => $data['excerpt'] ?? '',
				'post_content' => file_get_contents( $html_file ) . top_related_block( $related_reading, 'service:' . $s['slug'] ),
			),
			$meta + array(
				'top_h1'                  => $data['h1'] ?? '',
				'top_subtitle'            => $data['subtitle'] ?? '',
				'top_form_value'          => $data['form_value'] ?? $s['short'],
				'top_faqs'                => $data['faqs'] ?? array(),
				'top_brands'              => $data['brands'] ?? array(),
				'top_costs'               => $data['costs'] ?? array(),
				'rank_math_title'         => $data['meta_title'] ?? '',
				'rank_math_description'   => $data['meta_description'] ?? '',
				'rank_math_focus_keyword' => $data['focus_keyword'] ?? '',
			)
		);
		if ( $sid && isset( $service_featured[ $s['slug'] ] ) && ( $tid = top_seed_attachment( $service_featured[ $s['slug'] ] ) ) ) {
			set_post_thumbnail( $sid, $tid );
		}
		if ( $sid && isset( $social_jpg[ $s['slug'] ] ) ) {
			top_set_social_image( $sid, $social_jpg[ $s['slug'] ] );
		}
		continue;
	}
	$provisional = $p( sprintf( 'Need %s in Houston? Top Appliances Repair sends a technician the same day, gives you a free estimate before any work starts and covers the repair with a warranty on parts and labor. We service every major brand, including Sub-Zero.', strtolower( $s['title'] ) ) );
	// Real photo (when we have one for this appliance) between the intro and the editor note.
	if ( ! empty( $s['photo_slot'] ) ) {
		$provisional .= "\n\n" . '<!-- wp:top/photo {"slot":"' . $s['photo_slot'] . '","sizes":"(min-width: 600px) 520px, calc(100vw - 2rem)"} /-->';
	}
	$provisional_meta = array();
	if ( ! empty( $s['meta_description'] ) ) {
		$provisional_meta['rank_math_description'] = $s['meta_description'];
	}
	$sid = top_upsert(
		array(
			'post_type'    => 'service',
			'post_name'    => $s['slug'],
			'post_title'   => $s['title'],
			'menu_order'   => $i,
			'post_excerpt' => sprintf( 'Same-day %s service in Houston, TX: free estimate and a warranty on parts and labor.', strtolower( $s['title'] ) ),
			'post_content' => $provisional . "\n\n" . $todo . top_related_block( $related_reading, 'service:' . $s['slug'] ),
		),
		$meta + $provisional_meta
	);
	if ( $sid && isset( $service_featured[ $s['slug'] ] ) && ( $tid = top_seed_attachment( $service_featured[ $s['slug'] ] ) ) ) {
		set_post_thumbnail( $sid, $tid );
	}
	if ( $sid && isset( $social_jpg[ $s['slug'] ] ) ) {
		top_set_social_image( $sid, $social_jpg[ $s['slug'] ] );
	}
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
	$slug = sanitize_title( $b['title'] );
	$d    = $brand_data[ $slug ] ?? null;
	$meta = array( 'top_featured' => true, 'top_premium' => (bool) $b['premium'], 'top_logo_id' => $logo_ids[ $b['title'] ] ?? 0 );
	if ( $d ) {
		// Full page: direct-answer intro, appliances, brand notes, problems and FAQs.
		$meta['top_h1']                = sprintf( '%s Appliance Repair in Houston, TX', $b['title'] );
		$meta['top_faqs']              = $d['faqs'];
		$meta['rank_math_description'] = $d['meta_description'];
		$meta['rank_math_focus_keyword'] = strtolower( $b['title'] ) . ' appliance repair houston';
		$content_html                  = top_build_brand_content( $d, $service_names ) . top_related_block( $related_reading, 'brand:' . $slug );
		$excerpt                       = $d['meta_description'];
	} else {
		$content_html = $p( sprintf( 'Top Appliances Repair repairs %s appliances across Houston with same-day service, a free estimate and a warranty on parts and labor.', $b['title'] ) ) . "\n\n" . $todo;
		$excerpt      = sprintf( '%s appliance repair in Houston, TX with same-day service.', $b['title'] );
	}
	top_upsert(
		array(
			'post_type'    => 'brand',
			'post_name'    => $slug,
			'post_title'   => $b['title'],
			'menu_order'   => $i,
			'post_excerpt' => $excerpt,
			'post_content' => $content_html,
		),
		$meta
	);
}

/* ------------------------------------------------------------- Service areas */
foreach ( $content['areas'] as $i => $a ) {
	$d    = $area_data[ $a['slug'] ] ?? null;
	$meta = array( 'top_zips' => implode( ',', $a['zips'] ), 'top_lat' => $a['lat'], 'top_lng' => $a['lng'], 'top_county' => $a['county'] );
	if ( $d ) {
		$meta['top_h1']                  = sprintf( 'Appliance Repair in %s, TX', $a['title'] );
		$meta['top_faqs']                = $d['faqs'];
		$meta['rank_math_description']   = $d['meta_description'];
		$meta['rank_math_focus_keyword'] = 'appliance repair ' . strtolower( $a['title'] );
		$content_html                    = top_build_area_content( $d, $service_names, $brand_names, $area_titles, $a );
		$excerpt                         = $d['meta_description'];
	} else {
		$content_html = $p( sprintf( 'Top Appliances Repair serves homes in %s, TX with same-day appliance repair, a free estimate and a warranty on parts and labor.', $a['title'] ) ) . "\n\n" . $todo;
		$excerpt      = sprintf( 'Same-day appliance repair in %s, TX.', $a['title'] );
	}
	top_upsert(
		array(
			'post_type'    => 'service_area',
			'post_name'    => $a['slug'],
			'post_title'   => $a['title'],
			'menu_order'   => $i,
			'post_excerpt' => $excerpt,
			'post_content' => $content_html,
		),
		$meta
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
		),
		array_filter(
			array(
				'rank_math_title'       => $post['seo_title'] ?? '',
				'rank_math_description' => $post['meta_description'] ?? '',
			)
		)
	);
	if ( $id ) {
		// Real photos where we have one that matches the article; brand image otherwise.
		$featured_map = array(
			'refrigerator-not-cooling-what-might-be-wrong' => 'photo-refrigerator-evaporator-coil-houston-tx-16x9',
			'what-happens-when-a-refrigerator'             => 'photo-refrigerator-compressor-houston-tx-16x9',
			'what-are-the-different-types-of-stoves'       => 'photo-gas-range-stove-houston-tx-16x9',
		);
		$img = 0;
		if ( isset( $featured_map[ $post['slug'] ] ) ) {
			$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_top_seed_key', 'meta_value' => $featured_map[ $post['slug'] ], 'fields' => 'ids', 'numberposts' => 1 ) );
			$img   = $found ? (int) $found[0] : 0;
		}
		if ( ! $img ) {
			$img = top_seed_image( '/project/seed/images/brand-16x9.webp', 'brand-16x9', 'Top Appliances Repair brand image (replace with the article photo)', 'Top Appliances Repair' );
		}
		if ( $img ) {
			set_post_thumbnail( $id, $img );
		}
		if ( isset( $social_jpg[ $post['slug'] ] ) ) {
			top_set_social_image( $id, $social_jpg[ $post['slug'] ] );
		}
	}
}

/* ------------------------------------------ Blog posts written for this site */
// seed/posts/<slug>.html holds the article body; seed/posts-new.json holds the SEO
// fields, the FAQs (which also generate the FAQPage schema) and the featured image.
$social_for_photo = array(
	'photo-appliance-repair-technician-houston-tx-16x9' => 'og-appliance-repair-technician-houston-tx',
	'photo-oven-heating-element-houston-tx-16x9'        => 'og-oven-heating-element-houston-tx',
	'photo-refrigerator-evaporator-coil-houston-tx-16x9' => 'og-refrigerator-repair-houston-tx',
	'photo-refrigerator-compressor-houston-tx-16x9'     => 'og-refrigerator-compressor-houston-tx',
);
foreach ( $new_posts as $slug => $np ) {
	$body = '/project/seed/posts/' . $slug . '.html';
	if ( ! file_exists( $body ) ) {
		continue;
	}
	$id = top_upsert(
		array(
			'post_type'     => 'post',
			'post_name'     => $slug,
			'post_title'    => $np['title'],
			'post_excerpt'  => $np['excerpt'],
			'post_date'     => $np['date'],
			'post_date_gmt' => get_gmt_from_date( $np['date'] ),
			'post_content'  => file_get_contents( $body ),
		),
		array(
			'top_faqs'                => $np['faqs'],
			'rank_math_title'         => $np['seo_title'],
			'rank_math_description'   => $np['meta_description'],
			'rank_math_focus_keyword' => $np['focus_keyword'],
		)
	);
	if ( ! $id ) {
		continue;
	}
	$img = top_seed_attachment( $np['image'] );
	if ( $img ) {
		set_post_thumbnail( $id, $img );
	}
	if ( isset( $social_for_photo[ $np['image'] ] ) ) {
		top_set_social_image( $id, $social_for_photo[ $np['image'] ] );
	}
	wp_set_post_terms( $id, array( 'repair-tips' ), 'category' );
}

/* --------------------------------------------------------------------- Pages */
$home_sections = array( 'hero', 'trust-bar', 'services', 'how-it-works', 'why-choose-us', 'brands', 'reviews', 'service-areas', 'resources', 'faq', 'cta-form' );
$home_content  = implode( "\n\n", array_map( fn( $s ) => '<!-- wp:pattern {"slug":"top-appliance/' . $s . '"} /-->', $home_sections ) );
$home = top_upsert(
	array( 'post_type' => 'page', 'post_name' => 'home', 'post_title' => 'Home', 'post_content' => $home_content ),
	array(
		'rank_math_title'          => 'Same-Day Appliance Repair Houston | Top Appliances Repair',
		'rank_math_description'    => 'Same-day appliance repair in Houston, TX. Free estimate, 90-day warranty on parts and labor, all major brands including Sub-Zero. Call or book online.',
		'rank_math_focus_keyword'  => 'appliance repair houston',
		'rank_math_facebook_title' => 'Same-Day Appliance Repair in Houston, TX',
		'rank_math_facebook_image' => wp_get_attachment_url( (int) ( get_option( 'top_media', array() )['og'] ?? 0 ) ),
		'rank_math_facebook_image_id' => (int) ( get_option( 'top_media', array() )['og'] ?? 0 ),
		'rank_math_twitter_use_facebook' => 'on',
		'rank_math_robots'         => array( 'index' ),
	)
);
$about_d = $page_data['about'];
$about   = top_upsert(
	array(
		'post_type'    => 'page',
		'post_name'    => 'about',
		'post_title'   => $about_d['title'],
		'post_content' => file_get_contents( '/project/seed/pages/about.html' ),
	),
	array(
		'top_faqs'              => $about_d['faqs'],
		'rank_math_title'       => $about_d['meta_title'],
		'rank_math_description' => $about_d['meta_description'],
	)
);
$contact = top_upsert(
	array(
		'post_type'    => 'page',
		'post_name'    => 'contact',
		'post_title'   => 'Contact Us',
		'post_content' => $p( 'Call us, or send the form below and we will contact you to confirm a same-day visit.' ) . "\n\n" . '<!-- wp:pattern {"slug":"top-appliance/cta-form"} /-->',
	),
	array( 'rank_math_description' => 'Contact Top Appliances Repair in Houston, TX: call 832-514-1594 or book same-day appliance repair online. Free estimate before any work starts.' )
);
$blog = top_upsert(
	array( 'post_type' => 'page', 'post_name' => 'blog', 'post_title' => 'Appliance Repair Tips', 'post_content' => '' ),
	array( 'rank_math_description' => 'Appliance repair tips from Houston technicians: why refrigerators stop cooling, washers won’t drain, dryers run cold — and when to call for service.' )
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
$terms_d = $page_data['terms'];
top_upsert(
	array(
		'post_type'    => 'page',
		'post_name'    => 'terms',
		'post_title'   => $terms_d['title'],
		'post_content' => file_get_contents( '/project/seed/pages/terms.html' ),
	),
	array(
		'rank_math_title'       => $terms_d['meta_title'],
		'rank_math_description' => $terms_d['meta_description'],
	)
);

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home );
update_option( 'page_for_posts', $blog );

delete_transient( 'top_nav_data' );
WP_CLI::success( 'Seed content ready' );
