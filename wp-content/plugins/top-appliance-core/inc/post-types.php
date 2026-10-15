<?php
/**
 * Custom post types, taxonomy and meta.
 *
 * URL design (see docs/url-inventory.csv for the 301 map from the current site):
 *   /services/                       service archive     /services/refrigerator-repair/
 *   /brands/                         brand archive       /brands/sub-zero/
 *   /service-areas/                  area archive        /service-areas/katy/
 *   /error-codes/                    error code archive  /error-codes/washer/            (appliance term)
 *                                                        /error-codes/washer/samsung-4c/ (single)
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'top_register_post_types', 5 );

function top_register_post_types(): void {
	$common = array(
		'public'        => true,
		'show_in_rest'  => true,
		'menu_position' => 21,
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions', 'page-attributes' ),
	);

	register_post_type(
		'service',
		$common + array(
			'labels'      => top_cpt_labels( 'Service', 'Services' ),
			'menu_icon'   => 'dashicons-admin-tools',
			'has_archive' => 'services',
			'rewrite'     => array( 'slug' => 'services', 'with_front' => false ),
		)
	);

	register_post_type(
		'brand',
		$common + array(
			'labels'      => top_cpt_labels( 'Brand', 'Brands' ),
			'menu_icon'   => 'dashicons-awards',
			'has_archive' => 'brands',
			'rewrite'     => array( 'slug' => 'brands', 'with_front' => false ),
		)
	);

	register_post_type(
		'service_area',
		$common + array(
			'labels'      => top_cpt_labels( 'Service Area', 'Service Areas' ),
			'menu_icon'   => 'dashicons-location-alt',
			'has_archive' => 'service-areas',
			'rewrite'     => array( 'slug' => 'service-areas', 'with_front' => false ),
		)
	);

	register_post_type(
		'error_code',
		$common + array(
			'labels'      => top_cpt_labels( 'Error Code', 'Error Codes' ),
			'menu_icon'   => 'dashicons-warning',
			'has_archive' => 'error-codes',
			'rewrite'     => array( 'slug' => 'error-codes/%appliance%', 'with_front' => false ),
			'taxonomies'  => array( 'appliance' ),
		)
	);

	register_taxonomy(
		'appliance',
		array( 'error_code', 'service' ),
		array(
			'labels'            => array(
				'name'          => 'Appliances',
				'singular_name' => 'Appliance',
				'add_new_item'  => 'Add appliance',
				'edit_item'     => 'Edit appliance',
				'all_items'     => 'All appliances',
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'error-codes', 'with_front' => false, 'hierarchical' => false ),
		)
	);

	top_register_meta();
}

function top_cpt_labels( string $one, string $many ): array {
	return array(
		'name'               => $many,
		'singular_name'      => $one,
		'add_new_item'       => "Add $one",
		'edit_item'          => "Edit $one",
		'new_item'           => "New $one",
		'view_item'          => "View $one",
		'search_items'       => "Search $many",
		'not_found'          => "No $many found",
		'all_items'          => "All $many",
		'archives'           => "$many",
		'menu_name'          => $many,
		'item_link'          => "$one link",
		'items_list'         => "$many list",
	);
}

/** Meta schema per post type: key => [type, description, default]. */
function top_meta_schema(): array {
	return array(
		'service'      => array(
			'top_icon'       => array( 'string', 'Icon slug from the theme icon set.', 'tools' ),
			'top_short_name' => array( 'string', 'Short label for grids and menus, e.g. "Refrigerator".', '' ),
			'top_tagline'    => array( 'string', 'One-line description used on cards.', '' ),
			'top_h1'         => array( 'string', 'H1 of the service page, e.g. "Refrigerator Repair in Houston, TX".', '' ),
			'top_subtitle'   => array( 'string', 'Hero subtitle.', '' ),
			'top_form_value' => array( 'string', 'Appliance option preselected in the booking form.', '' ),
			'top_faqs'       => array( 'array', 'FAQs: [{q, a}]. Rendered as the accordion and as FAQPage schema.', array() ),
			'top_brands'     => array( 'array', 'Brand slugs relevant to this appliance.', array() ),
			'top_costs'      => array( 'array', 'Cost table rows [{repair, range, note}]. Leave empty until prices are approved.', array() ),
		),
		'brand'        => array(
			'top_featured'  => array( 'boolean', 'Show on the Home brand wall.', true ),
			'top_premium'   => array( 'boolean', 'Premium / built-in brand (e.g. Sub-Zero).', false ),
			'top_logo_id'   => array( 'integer', 'Brand logo attachment ID (optional).', 0 ),
		),
		'service_area' => array(
			'top_zips'   => array( 'string', 'ZIP codes served in this area, comma separated.', '' ),
			'top_lat'    => array( 'number', 'Latitude of the area centre.', 0 ),
			'top_lng'    => array( 'number', 'Longitude of the area centre.', 0 ),
			'top_county' => array( 'string', 'County name.', '' ),
		),
		'error_code'   => array(
			'top_code'     => array( 'string', 'The code as shown on the display, e.g. 4C.', '' ),
			'top_brand_id' => array( 'integer', 'Related brand post ID.', 0 ),
			'top_meaning'  => array( 'string', 'Short meaning of the code.', '' ),
			'top_diy_safe' => array( 'boolean', 'Safe for the homeowner to try a reset.', false ),
		),
	);
}

function top_register_meta(): void {
	foreach ( top_meta_schema() as $post_type => $fields ) {
		foreach ( $fields as $key => $def ) {
			$rest = true;
			if ( 'array' === $def[0] ) {
				$rest = array( 'schema' => array( 'type' => 'array', 'items' => array( 'type' => array( 'object', 'string' ), 'additionalProperties' => true ) ) );
			}
			register_post_meta(
				$post_type,
				$key,
				array(
					'show_in_rest'      => $rest,
					'type'              => $def[0],
					'description'       => $def[1],
					'default'           => $def[2],
					'single'            => true,
					'sanitize_callback' => match ( $def[0] ) {
						'integer' => 'absint',
						'number'  => fn( $v ) => (float) $v,
						'boolean' => 'rest_sanitize_boolean',
						'array'   => fn( $v ) => is_array( $v ) ? map_deep( $v, 'wp_kses_post' ) : array(),
						default   => 'sanitize_text_field',
					},
					'auth_callback'     => fn() => current_user_can( 'edit_posts' ),
				)
			);
		}
	}
}

/* Error code permalinks: /error-codes/{appliance}/{code}/ */
add_filter( 'post_type_link', function ( string $link, WP_Post $post ) {
	if ( 'error_code' !== $post->post_type || ! str_contains( $link, '%appliance%' ) ) {
		return $link;
	}
	$terms = get_the_terms( $post, 'appliance' );
	$slug  = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->slug : 'general';
	return str_replace( '%appliance%', $slug, $link );
}, 10, 2 );

add_action( 'init', function () {
	// Single error code wins over the term archive when two segments are present.
	add_rewrite_rule( '^error-codes/(?!(?:page|feed)(?:/|$))([^/]+)/(?!(?:page|feed|rss2?|atom)(?:/|$))([^/]+)/?$', 'index.php?error_code=$matches[2]', 'top' );
}, 20 );

/* Invalidate cached navigation data whenever content that feeds it changes. */
foreach ( array( 'save_post_service', 'save_post_brand', 'save_post_service_area', 'save_post_post', 'deleted_post', 'edited_appliance' ) as $hook ) {
	add_action( $hook, fn() => delete_transient( 'top_nav_data' ) );
}

/**
 * Lightweight, cached lists used by menus, grids and schema.
 * Returns ['service'=>[...], 'brand'=>[...], 'service_area'=>[...], 'appliance'=>[...]].
 */
function top_nav_data(): array {
	$data = get_transient( 'top_nav_data' );
	if ( is_array( $data ) ) {
		return $data;
	}
	$data = array();
	foreach ( array( 'service', 'brand', 'service_area' ) as $pt ) {
		$posts       = get_posts(
			array(
				'post_type'        => $pt,
				'post_status'      => 'publish',
				'posts_per_page'   => 200,
				'orderby'          => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
				'suppress_filters' => false,
			)
		);
		$data[ $pt ] = array_map(
			function ( WP_Post $p ) use ( $pt ) {
				$item = array(
					'id'    => $p->ID,
					'title' => get_the_title( $p ),
					'url'   => get_permalink( $p ),
				);
				if ( 'service' === $pt ) {
					$item['short']   = get_post_meta( $p->ID, 'top_short_name', true ) ?: $item['title'];
					$item['icon']    = get_post_meta( $p->ID, 'top_icon', true ) ?: 'tools';
					$item['tagline'] = get_post_meta( $p->ID, 'top_tagline', true );
				} elseif ( 'brand' === $pt ) {
					$item['featured'] = (bool) get_post_meta( $p->ID, 'top_featured', true );
					$item['logo']     = (int) get_post_meta( $p->ID, 'top_logo_id', true );
					$item['premium']  = (bool) get_post_meta( $p->ID, 'top_premium', true );
				} else {
					$item['zips'] = array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $p->ID, 'top_zips', true ) ) ) );
					$item['lat']  = (float) get_post_meta( $p->ID, 'top_lat', true );
					$item['lng']  = (float) get_post_meta( $p->ID, 'top_lng', true );
				}
				return $item;
			},
			$posts
		);
	}
	$terms             = get_terms( array( 'taxonomy' => 'appliance', 'hide_empty' => false, 'orderby' => 'name' ) );
	$data['appliance'] = is_wp_error( $terms ) ? array() : array_map(
		fn( $t ) => array( 'title' => $t->name, 'url' => get_term_link( $t ) ),
		$terms
	);
	set_transient( 'top_nav_data', $data, DAY_IN_SECONDS );
	return $data;
}
