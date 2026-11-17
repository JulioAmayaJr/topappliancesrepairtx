<?php
/**
 * Site configuration (run by bin/setup.sh through `wp eval-file`). Idempotent.
 * This file is the ONLY place business data is written from code: it seeds the
 * "Business Info" settings, which the whole site then reads.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$theme_dir = get_template_directory();

/** Import a file into the Media Library once (keyed), return the attachment ID. */
function top_seed_media( string $path, string $key, string $title, string $alt ): int {
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_top_seed_key', 'meta_value' => $key, 'fields' => 'ids', 'numberposts' => 1 ) );
	if ( $found ) {
		update_post_meta( $found[0], '_wp_attachment_image_alt', $alt );
		return (int) $found[0];
	}
	if ( ! file_exists( $path ) ) {
		WP_CLI::warning( "Missing media file $path" );
		return 0;
	}
	$tmp = wp_tempnam( basename( $path ) );
	copy( $path, $tmp );
	$id = media_handle_sideload( array( 'name' => basename( $path ), 'tmp_name' => $tmp ), 0, $title );
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( $id->get_error_message() );
		return 0;
	}
	update_post_meta( $id, '_top_seed_key', $key );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	return (int) $id;
}

/* ------------------------------------------------------------------ Logos */
$brand_dir = $theme_dir . '/assets/images/brand/';
$logo_mark  = top_seed_media( $brand_dir . 'cropped-topappliance-favicon-270x270.png', 'logo-mark', 'Top Appliances Repair logo mark', 'Top Appliances Repair logo' );
$logo_full  = top_seed_media( $brand_dir . 'logo-desktop.png', 'logo-full', 'Top Appliances Repair logo', 'Top Appliances Repair logo' );
$logo_light = top_seed_media( $brand_dir . 'footer-logo.png', 'logo-light', 'Top Appliances Repair logo (white)', 'Top Appliances Repair logo' );
update_option( 'site_icon', $logo_mark );

/* Remove the old labelled placeholders (replaced by text-free brand images). */
foreach ( array( 'hero', 'why', 'blog-washer-not-draining', 'blog-dishwasher-not-cleaning', 'blog-refrigerator-not-cooling', 'blog-types-of-stoves', 'blog-smelly-garbage-disposal', 'blog-how-long-refrigerators-last', 'blog-refrigerator-leak', 'blog-refrigerator-compressor', 'blog-speed-queen-washer' ) as $old_key ) {
	foreach ( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_top_seed_key', 'meta_value' => $old_key, 'fields' => 'ids', 'numberposts' => -1 ) ) as $old_id ) {
		wp_delete_attachment( $old_id, true );
	}
}

/* Real photos (from the client, 2026-11) → Media Library, one import per file. */
$photo_dir   = '/project/seed/photos/';
$photo_files = array(
	'gas-cooktop-repair-houston-tx'                 => array( 'Gas cooktop repair in Houston, TX', 'Gas cooktop with all four burners lit during a service visit in Houston, TX' ),
	'appliance-repair-technician-houston-tx'        => array( 'Appliance repair technician in Houston, TX', 'Top Appliances Repair technician working on a built-in refrigerator compressor in Houston, TX' ),
	'refrigerator-repair-houston-tx'                => array( 'Refrigerator repair in Houston, TX', 'Refrigerator evaporator coil exposed during a repair in Houston, TX' ),
	'refrigerator-compressor-replacement-houston-tx' => array( 'Refrigerator compressor replacement in Houston, TX', 'New compressor ready to be installed in a refrigerator in Houston, TX' ),
	'oven-repair-houston-tx'                        => array( 'Oven repair in Houston, TX', 'Electric oven bake element glowing during a heating test in Houston, TX' ),
	'gas-range-stove-repair-houston-tx'             => array( 'Stove repair in Houston, TX', 'Gas range burners being tested during a stove repair in Houston, TX' ),
	'built-in-microwave-oven-repair-houston-tx'     => array( 'Built-in microwave repair in Houston, TX', 'Technician servicing a built-in microwave and wall oven combination in Houston, TX' ),
	'refrigerator-evaporator-coil-houston-tx-16x9'  => array( 'Refrigerator evaporator coil (wide)', 'Refrigerator evaporator coil exposed during a repair in Houston, TX' ),
	'appliance-repair-technician-houston-tx-16x9'   => array( 'Appliance repair technician at work (wide)', 'Top Appliances Repair technician working on a refrigerator compressor in Houston, TX' ),
	'oven-heating-element-houston-tx-16x9'          => array( 'Oven heating element (wide)', 'Electric oven bake element glowing during a heating test in Houston, TX' ),
	'refrigerator-compressor-houston-tx-16x9'       => array( 'Refrigerator compressor (wide)', 'New compressor ready to be installed in a refrigerator in Houston, TX' ),
	'gas-range-stove-houston-tx-16x9'               => array( 'Gas range burners (wide)', 'Gas range burners being tested during a stove repair in Houston, TX' ),
);
$photo_ids = array();
foreach ( $photo_files as $file => $meta ) {
	$photo_ids[ $file ] = top_seed_media( $photo_dir . $file . '.webp', 'photo-' . $file, $meta[0], $meta[1] );
}

/* Media slots used by patterns (hero, why-choose-us) and the top/photo block on service pages. */
update_option(
	'top_media',
	array_merge(
		(array) get_option( 'top_media', array() ),
		array(
			'hero'                          => $photo_ids['gas-cooktop-repair-houston-tx'],
			'why'                           => $photo_ids['appliance-repair-technician-houston-tx'],
			'photo-refrigerator'            => $photo_ids['refrigerator-repair-houston-tx'],
			'photo-refrigerator-compressor' => $photo_ids['refrigerator-compressor-replacement-houston-tx'],
			'photo-oven'                    => $photo_ids['oven-repair-houston-tx'],
			'photo-stove'                   => $photo_ids['gas-range-stove-repair-houston-tx'],
			'photo-microwave'               => $photo_ids['built-in-microwave-oven-repair-houston-tx'],
			'photo-cooktop'                 => $photo_ids['gas-cooktop-repair-houston-tx'],
		)
	)
);
// Social images must stay PNG/JPG (Facebook/LinkedIn/X reject AVIF): import them with format conversion off.
$img = '/project/seed/images/';
remove_all_filters( 'image_editor_output_format' );
$top_media       = (array) get_option( 'top_media', array() );
$top_media['og'] = top_seed_media( $img . 'og-default.png', 'og-png', 'Top Appliances Repair social image', 'Top Appliances Repair: same-day appliance repair in Houston, TX' );
update_option( 'top_media', $top_media );
// JPG variants of the real photos for per-page og:image (assigned in bin/seed.php).
foreach ( array(
	'og-refrigerator-repair-houston-tx'     => 'Refrigerator evaporator coil exposed during a repair in Houston, TX',
	'og-refrigerator-compressor-houston-tx' => 'New compressor ready to be installed in a refrigerator in Houston, TX',
	'og-stove-oven-repair-houston-tx'       => 'Gas range burners being tested during a stove repair in Houston, TX',
	'og-cooktop-repair-houston-tx'          => 'Gas cooktop with all four burners lit during a service visit in Houston, TX',
	'og-appliance-repair-technician-houston-tx' => 'Top Appliances Repair technician working on a refrigerator compressor in Houston, TX',
	'og-oven-heating-element-houston-tx'    => 'Electric oven bake element glowing during a heating test in Houston, TX',
) as $og_file => $og_alt ) {
	top_seed_media( $photo_dir . 'jpg/' . $og_file . '.jpg', $og_file, 'Social image: ' . $og_file, $og_alt );
}
set_theme_mod( 'custom_logo', $logo_mark );
update_option( 'site_logo', $logo_mark );

/* ------------------------------------------------------------ Business Info */
$content  = json_decode( file_get_contents( '/project/seed/content.json' ), true );
$all_zips = array();
foreach ( $content['areas'] as $area ) {
	$all_zips = array_merge( $all_zips, $area['zips'] );
}
$all_zips = array_values( array_unique( $all_zips ) );
sort( $all_zips );

$hero_id = (int) ( get_option( 'top_media', array() )['hero'] ?? 0 );
update_option(
	'top_business',
	array_merge(
		(array) get_option( 'top_business', array() ),
		array(
			'name'                => 'Top Appliances Repair',
			'phone'               => '832-514-1594',
			'email'               => 'topappliancesrepair@yahoo.com',
			'street'              => '7474 South Kirkwood Road',
			'city'                => 'Houston',
			'region'              => 'TX',
			'postal_code'         => '77072',
			'country'             => 'US',
			'lat'                 => '29.6972365',
			'lng'                 => '-95.5878163',
			'hours'               => array(
				'mon' => array( 'open' => '09:00', 'close' => '17:00' ),
				'tue' => array( 'open' => '09:00', 'close' => '17:00' ),
				'wed' => array( 'open' => '09:00', 'close' => '17:00' ),
				'thu' => array( 'open' => '09:00', 'close' => '17:00' ),
				'fri' => array( 'open' => '09:00', 'close' => '17:00' ),
				'sat' => array( 'open' => '09:00', 'close' => '15:00' ),
				'sun' => array( 'open' => '', 'close' => '' ),
			),
			'years'               => '10',
			'warranty_days'       => '90',
			'facebook'            => 'https://www.facebook.com/topappliancesrepair/',
			'instagram'           => 'https://www.instagram.com/topappliancesrepair/',
			'youtube'             => 'https://www.youtube.com/channel/UCvPRBS6Ec_p_vG8fTtKOZ_g',
			// Google Business Profile from the current site's map embed (CID 0x6bf5c81a7add7595). Verify.
			'google_business'     => 'https://maps.google.com/?cid=7779343947408438677',
			'google_reviews_url'  => 'https://maps.google.com/?cid=7779343947408438677',
			'google_rating'       => '',
			'google_review_count' => '',
			// The real review published on the current site (always shown first).
			'review_pinned'       => array(
				'name'   => 'Linda L',
				'rating' => 5,
				'date'   => '',
				'text'   => 'Cannot say enough nice things about this company. The professionalism and dedication to customer service with something you don’t see very often. They were wonderful! Will recommend them to all of my friends and family.',
			),
			'booking_url'         => '/contact/#book',
			'map_embed_url'       => 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d443150.1792414071!2d-95.4665328!3d29.8033439!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x6bf5c81a7add7595!2sTop%20Appliances%20Repair%20Corporation!5e0!3m2!1sen!2s!4v1604068549916!5m2!1sen!2s',
			'map_link_url'        => 'https://maps.google.com/?cid=7779343947408438677',
			'zips'                => implode( ' ', $all_zips ),
			'logo_id'             => $logo_mark,
			'logo_full_id'        => $logo_full,
			'logo_light_id'       => $logo_light,
			'image_id'            => $hero_id,
		)
	)
);
delete_transient( 'top_nav_data' );

/* --------------------------------------------------------------- Rank Math */
// 'instant-indexing' = IndexNow: new and updated URLs are pushed to Bing and the other
// IndexNow participants the moment they are published. No 'rich-snippet' (we own the schema).
update_option( 'rank_math_modules', array( 'sitemap', 'redirections', 'link-counter', 'instant-indexing' ) );
update_option( 'rank_math_wizard_completed', true );
update_option( 'rank_math_registration_skip', 1 );
update_option( 'rank_math_is_configured', true );
if ( class_exists( '\RankMath\Installer' ) ) {
	global $wpdb;
	if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'rank_math_redirections' ) ) ) {
		$installer = new \RankMath\Installer();
		$method    = new ReflectionMethod( $installer, 'create_tables' );
		$method->setAccessible( true );
		$method->invoke( $installer, array( 'redirections', 'link-counter' ) );
	}
}
$og_id = (int) ( get_option( 'top_media', array() )['og'] ?? 0 );
$titles = (array) get_option( 'rank-math-options-titles', array() );
update_option(
	'rank-math-options-titles',
	array_merge(
		$titles,
		array(
			'title_separator'           => '|',
			'knowledgegraph_type'       => 'company',
			'knowledgegraph_name'       => 'Top Appliances Repair',
			'website_name'              => 'Top Appliances Repair',
			'knowledgegraph_logo'       => wp_get_attachment_url( $logo_full ),
			'knowledgegraph_logo_id'    => $logo_full,
			'open_graph_image'          => $og_id ? wp_get_attachment_url( $og_id ) : '',
			'open_graph_image_id'       => $og_id,
			'twitter_card_type'         => 'summary_large_image',
			'disable_author_archives'   => 'on',
			'disable_date_archives'     => 'on',
			'pt_post_title'             => '%title% %sep% %sitename%',
			'pt_page_title'             => '%title% %sep% %sitename%',
			'pt_service_title'          => '%title% in Houston, TX %sep% %sitename%',
			'pt_service_archive_title'  => 'Houston Appliance Repair Services %sep% %sitename%',
			'pt_service_archive_description' => 'Every appliance we repair in Houston, TX: refrigerators, washers, dryers, dishwashers, stoves, microwaves and more. Same-day service and free estimates.',
			'pt_brand_title'            => '%title% Appliance Repair Houston %sep% %sitename%',
			'pt_brand_archive_title'    => 'Brands We Service in Houston %sep% %sitename%',
			'pt_brand_archive_description' => 'Appliance brands we repair in Houston, TX — from Sub-Zero, Thermador and Viking to Samsung, LG, Whirlpool and GE. Same-day service, 90-day warranty.',
			'pt_service_area_title'     => '%title% Appliance Repair %sep% %sitename%',
			'pt_service_area_archive_title' => 'Service Areas Around Houston, TX %sep% %sitename%',
			'pt_service_area_archive_description' => 'Cities where Top Appliances Repair offers same-day appliance repair: Houston, Katy, Sugar Land, Cypress, Spring, The Woodlands and more nearby areas.',
			'pt_error_code_title'       => '%title% %sep% %sitename%',
			'pt_error_code_archive_title' => 'Appliance Error Codes %sep% %sitename%',
			'pt_error_code_archive_description' => 'Common appliance error codes explained by Houston repair technicians: what each code means, what to check and when to book a repair visit.',
			'tax_appliance_title'       => '%term% Error Codes %sep% %sitename%',
			'tax_appliance_description' => '%term% error codes explained by Houston appliance repair technicians: what each code means, safe checks to try and when to call for service.',
			'pt_attachment_robots'      => array( 'noindex' ),
		)
	)
);
$general = (array) get_option( 'rank-math-options-general', array() );
update_option(
	'rank-math-options-general',
	array_merge(
		$general,
		array(
			'breadcrumbs'             => 'off',
			'attachment_redirect_urls' => 'on',
			'attachment_redirect_default' => home_url( '/' ),
			'strip_category_base'     => 'off',
			'nofollow_external_links' => 'off',
			'new_window_external_links' => 'off',
			'robots_txt_content'      => "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n\nSitemap: " . ( function_exists( 'top_public_base' ) ? top_public_base() : home_url() ) . '/sitemap_index.xml',
		)
	)
);
$sitemap = (array) get_option( 'rank-math-options-sitemap', array() );
update_option(
	'rank-math-options-sitemap',
	array_merge(
		$sitemap,
		array(
			'items_per_page'          => 200,
			'include_images'          => 'on',
			'authors_sitemap'         => 'off',
			'pt_post_sitemap'         => 'on',
			'pt_page_sitemap'         => 'on',
			'pt_service_sitemap'      => 'on',
			'pt_brand_sitemap'        => 'on',
			'pt_service_area_sitemap' => 'on',
			'pt_error_code_sitemap'   => 'on',
			'pt_attachment_sitemap'   => 'off',
			'tax_appliance_sitemap'   => 'on',
			'tax_category_sitemap'    => 'off',
			'tax_post_tag_sitemap'    => 'off',
		)
	)
);

/* ----------------------------------------------------------- IndexNow */
/*
 * IndexNow pushes new and updated URLs straight to Bing, Yandex, Seznam and Naver
 * instead of waiting for a crawl. The key is generated once and then served from
 * <key>.txt at the site root, which is how the protocol verifies ownership.
 */
$ii = (array) get_option( 'rank-math-options-instant-indexing', array() );
if ( empty( $ii['indexnow_api_key'] ) ) {
	$ii['indexnow_api_key'] = str_replace( '-', '', wp_generate_uuid4() );
}
$ii['bing_post_types'] = array( 'post', 'page', 'service', 'brand', 'service_area', 'error_code' );
update_option( 'rank-math-options-instant-indexing', $ii );
WP_CLI::log( 'IndexNow key: ' . $ii['indexnow_api_key'] );

/* ------------------------------------------- 301 redirects (old site URLs) */
/*
 * seed/redirects.csv maps every indexable URL of the current live site to its
 * closest equivalent here (source of truth: docs/url-inventory.csv). Loaded into
 * Rank Math's Redirections module; inert until the domain points at this site.
 */
if ( class_exists( '\RankMath\Redirections\DB' ) && file_exists( '/project/seed/redirects.csv' ) ) {
	$existing = array();
	foreach ( \RankMath\Redirections\DB::get_redirections( array( 'limit' => 1000 ) )['redirections'] ?? array() as $r ) {
		foreach ( (array) maybe_unserialize( $r['sources'] ) as $src ) {
			$existing[ $src['pattern'] ?? '' ] = true;
		}
	}
	$added = 0;
	$fh    = fopen( '/project/seed/redirects.csv', 'r' );
	fgetcsv( $fh ); // header
	while ( ( $row = fgetcsv( $fh ) ) !== false ) {
		list( $from, $to ) = array_map( 'trim', $row );
		$pattern = trim( $from, '/' );
		if ( '' === $pattern || isset( $existing[ $pattern ] ) ) {
			continue;
		}
		\RankMath\Redirections\DB::add(
			array(
				'sources'     => array( array( 'pattern' => $pattern, 'comparison' => 'exact', 'ignore' => 'case' ) ),
				'url_to'      => $to,
				'header_code' => 301,
				'status'      => 'active',
			)
		);
		$added++;
	}
	fclose( $fh );
	WP_CLI::log( "Redirections: $added added" );
}

/* ------------------------------------------------- FluentSMTP → Mailpit */
$conn_key = md5( 'mailpit-local' );
$admin    = get_option( 'admin_email' );
update_option(
	'fluentmail-settings',
	array(
		'mappings'    => array( $admin => $conn_key ),
		'connections' => array(
			$conn_key => array(
				'title'             => 'Mailpit (local)',
				'provider_settings' => array(
					'sender_name'      => 'Top Appliances Repair',
					'sender_email'     => $admin,
					'force_from_name'  => 'yes',
					'force_from_email' => 'yes',
					'return_path'      => 'yes',
					'host'             => 'mailpit',
					'port'             => '1025',
					'auth'             => 'no',
					'username'         => '',
					'password'         => '',
					'auto_tls'         => 'no',
					'encryption'       => 'none',
					'key_store'        => 'db',
					'provider'         => 'smtp',
				),
			),
		),
		'misc'        => array(
			'log_emails'              => 'yes',
			'log_saved_interval_days' => '14',
			'disable_fluentcrm_logs'  => 'no',
			'default_connection'      => $conn_key,
			'fallback_connection'     => '',
		),
		'use_encrypt' => '',
	)
);

/* ------------------------------------------------ Fluent Forms: booking */
global $wpdb;
$forms_table = $wpdb->prefix . 'fluentform_forms';
$meta_table  = $wpdb->prefix . 'fluentform_form_meta';
if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $forms_table ) ) ) {
	$req = fn( $msg ) => array( 'required' => array( 'value' => true, 'message' => $msg ) );
	$no_logic = array( 'type' => 'any', 'status' => false, 'conditions' => array( array( 'field' => '', 'value' => '', 'operator' => '' ) ) );
	$text = function ( $i, $name, $label, $type, $msg, $placeholder = '', $max = '' ) use ( $req, $no_logic ) {
		return array(
			'index'          => $i,
			'element'        => 'input_text',
			'attributes'     => array( 'type' => $type, 'name' => $name, 'value' => '', 'class' => '', 'placeholder' => $placeholder, 'maxlength' => $max, 'id' => 'ff_' . $name ),
			'settings'       => array( 'container_class' => '', 'label' => $label, 'label_placement' => '', 'admin_field_label' => $label, 'help_message' => '', 'prefix_label' => '', 'suffix_label' => '', 'validation_rules' => $req( $msg ), 'conditional_logics' => $no_logic, 'is_unique' => 'no', 'unique_validation_message' => '' ),
			'editor_options' => array( 'title' => 'Simple Text', 'icon_class' => 'ff-edit-text', 'template' => 'inputText' ),
			'uniqElKey'      => 'el_' . $name,
		);
	};
	$select = function ( $i, $name, $label, $options, $msg ) use ( $req, $no_logic ) {
		return array(
			'index'          => $i,
			'element'        => 'select',
			'attributes'     => array( 'name' => $name, 'value' => '', 'id' => 'ff_' . $name, 'class' => '' ),
			'settings'       => array( 'label' => $label, 'admin_field_label' => $label, 'help_message' => '', 'container_class' => '', 'label_placement' => '', 'placeholder' => 'Select…', 'advanced_options' => array_map( fn( $o ) => array( 'label' => $o, 'value' => $o, 'calc_value' => '' ), $options ), 'validation_rules' => $req( $msg ), 'conditional_logics' => $no_logic, 'enable_select_2' => 'no' ),
			'options'        => array_combine( $options, $options ),
			'editor_options' => array( 'title' => 'Dropdown', 'icon_class' => 'ff-edit-dropdown', 'element' => 'select', 'template' => 'select' ),
			'uniqElKey'      => 'el_' . $name,
		);
	};
	$appliances = array_merge( array_map( fn( $s ) => $s['short'], $content['services'] ), array( 'Other' ) );
	$brands     = array_merge( array_map( fn( $b ) => $b['title'], $content['brands'] ), array( 'Other / not sure' ) );
	$fields     = array(
		'fields'       => array(
			$text( 0, 'full_name', 'Full name', 'text', 'Please enter your name.' ),
			$text( 1, 'phone', 'Phone', 'tel', 'Please enter a phone number we can call.' ),
			$text( 2, 'zip', 'ZIP code', 'text', 'Please enter your 5-digit ZIP code.', '', '5' ),
			$select( 3, 'appliance', 'Appliance', $appliances, 'Please choose the appliance.' ),
			$select( 4, 'brand', 'Brand', $brands, 'Please choose the brand.' ),
			array(
				'index'          => 5,
				'element'        => 'textarea',
				'attributes'     => array( 'name' => 'problem', 'value' => '', 'id' => 'ff_problem', 'class' => '', 'placeholder' => 'Example: the fridge is running but not cooling since yesterday.', 'rows' => 4, 'cols' => 2, 'maxlength' => '' ),
				'settings'       => array( 'container_class' => '', 'label' => 'What is the problem?', 'admin_field_label' => 'Problem', 'label_placement' => '', 'help_message' => '', 'validation_rules' => $req( 'Please describe the problem.' ), 'conditional_logics' => $no_logic ),
				'editor_options' => array( 'title' => 'Text Area', 'icon_class' => 'ff-edit-textarea', 'template' => 'inputTextarea' ),
				'uniqElKey'      => 'el_problem',
			),
		),
		'submitButton' => array(
			'uniqElKey'      => 'el_submit',
			'element'        => 'button',
			'attributes'     => array( 'type' => 'submit', 'class' => '' ),
			'settings'       => array( 'align' => 'left', 'button_style' => 'default', 'container_class' => '', 'help_message' => '', 'background_color' => '#054E99', 'button_size' => 'md', 'color' => '#ffffff', 'button_ui' => array( 'type' => 'default', 'text' => 'Book Service', 'img_url' => '' ) ),
			'editor_options' => array( 'title' => 'Submit Button' ),
		),
	);
	$form_id = (int) get_option( 'top_booking_form_id', 0 );
	$exists  = $form_id ? $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $forms_table WHERE id = %d", $form_id ) ) : null;
	$row     = array(
		'title'       => 'Book Service',
		'status'      => 'published',
		'form_fields' => wp_json_encode( $fields ),
		'has_payment' => 0,
		'type'        => 'form',
		'created_by'  => 1,
		'updated_at'  => current_time( 'mysql' ),
	);
	if ( $exists ) {
		$wpdb->update( $forms_table, $row, array( 'id' => $form_id ) );
	} else {
		$row['created_at'] = current_time( 'mysql' );
		$wpdb->insert( $forms_table, $row );
		$form_id = (int) $wpdb->insert_id;
		update_option( 'top_booking_form_id', $form_id );
	}
	$meta = array(
		'formSettings'   => array(
			'confirmation' => array( 'redirectTo' => 'samePage', 'messageToShow' => '<p><strong>Thanks, we received your request.</strong> We will contact you shortly to confirm your appointment.</p>', 'customPage' => null, 'samePageFormBehavior' => 'hide_form', 'customUrl' => null ),
			'restrictions' => array( 'limitNumberOfEntries' => array( 'enabled' => false ), 'scheduleForm' => array( 'enabled' => false ), 'requireLogin' => array( 'enabled' => false ), 'denyEmptySubmission' => array( 'enabled' => false ) ),
			'layout'       => array( 'labelPlacement' => 'top', 'helpMessagePlacement' => 'with_label', 'errorMessagePlacement' => 'inline', 'cssClassName' => 'ta-booking-form', 'asteriskPlacement' => 'asterisk-right' ),
		),
		'notifications'  => array(
			'name'          => 'New service request',
			'sendTo'        => array( 'type' => 'email', 'email' => '{wp.admin_email}', 'field' => '', 'routing' => array() ),
			'fromName'      => 'Top Appliances Repair website',
			'fromEmail'     => '',
			'replyTo'       => '',
			'bcc'           => '',
			'subject'       => 'New service request: {inputs.appliance} ({inputs.zip})',
			'message'       => '<p>{all_data}</p>',
			'conditionals'  => array( 'status' => false, 'type' => 'all', 'conditions' => array() ),
			'enabled'       => true,
			'email_template' => '',
		),
		'template_name'  => 'blank_form',
	);
	foreach ( $meta as $key => $value ) {
		$wpdb->delete( $meta_table, array( 'form_id' => $form_id, 'meta_key' => $key ) );
		$wpdb->insert( $meta_table, array( 'form_id' => $form_id, 'meta_key' => $key, 'value' => wp_json_encode( $value ) ) );
	}
	// Remove the demo forms Fluent Forms creates on activation.
	foreach ( $wpdb->get_col( "SELECT id FROM $forms_table WHERE title IN ('Contact Form Demo','Subscription Form')" ) as $demo_id ) {
		$wpdb->delete( $forms_table, array( 'id' => (int) $demo_id ) );
		$wpdb->delete( $meta_table, array( 'form_id' => (int) $demo_id ) );
	}
	WP_CLI::log( "Fluent Forms booking form #$form_id ready" );
}

WP_CLI::success( 'Configuration applied' );
