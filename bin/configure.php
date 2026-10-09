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

/* Section photos (placeholders until the client sends real photos) → `top_media` slots used by patterns. */
$img = '/project/seed/images/';
update_option(
	'top_media',
	array_merge(
		(array) get_option( 'top_media', array() ),
		array(
			'hero' => top_seed_media( $img . 'brand-4x3.webp', 'brand-4x3', 'Top Appliances Repair brand image (replace with a technician photo)', 'Top Appliances Repair' ),
			'why'  => top_seed_media( $img . 'brand-4x5.webp', 'brand-4x5', 'Top Appliances Repair brand image (replace with a technician portrait)', 'Top Appliances Repair' ),
		)
	)
);
// Social image must stay PNG (Facebook/LinkedIn/X reject AVIF): import it with format conversion off.
remove_all_filters( 'image_editor_output_format' );
$top_media       = (array) get_option( 'top_media', array() );
$top_media['og'] = top_seed_media( $img . 'og-default.png', 'og-png', 'Top Appliances Repair social image', 'Top Appliances Repair: same-day appliance repair in Houston, TX' );
update_option( 'top_media', $top_media );
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
update_option( 'rank_math_modules', array( 'sitemap', 'redirections', 'link-counter' ) ); // no 'rich-snippet' (schema)
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
			'pt_service_archive_title'  => 'Appliance Repair Services in Houston, TX %sep% %sitename%',
			'pt_brand_title'            => '%title% Appliance Repair in Houston %sep% %sitename%',
			'pt_brand_archive_title'    => 'Appliance Brands We Service in Houston, TX %sep% %sitename%',
			'pt_service_area_title'     => 'Appliance Repair in %title%, TX %sep% %sitename%',
			'pt_service_area_archive_title' => 'Service Areas Around Houston, TX %sep% %sitename%',
			'pt_error_code_title'       => '%title% %sep% %sitename%',
			'pt_error_code_archive_title' => 'Appliance Error Codes %sep% %sitename%',
			'tax_appliance_title'       => '%term% Error Codes %sep% %sitename%',
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
			'robots_txt_content'      => "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n\nSitemap: " . home_url( '/sitemap_index.xml' ),
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
