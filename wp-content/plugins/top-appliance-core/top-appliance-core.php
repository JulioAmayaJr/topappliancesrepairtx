<?php
/**
 * Plugin Name:       Top Appliance Core
 * Description:       Business data, custom post types, JSON-LD schema and dynamic blocks for Top Appliances Repair.
 * Version:           1.0.0
 * Requires at least: 6.8
 * Requires PHP:      8.1
 * Author:            Top Appliances Repair
 * License:           GPL-2.0-or-later
 * Text Domain:       top-appliance-core
 */

defined( 'ABSPATH' ) || exit;

define( 'TOP_CORE_VERSION', '1.0.0' );
define( 'TOP_CORE_FILE', __FILE__ );
define( 'TOP_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'TOP_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once TOP_CORE_DIR . 'inc/helpers.php';
require_once TOP_CORE_DIR . 'inc/icons.php';
require_once TOP_CORE_DIR . 'inc/settings.php';
require_once TOP_CORE_DIR . 'inc/post-types.php';
require_once TOP_CORE_DIR . 'inc/bindings.php';
require_once TOP_CORE_DIR . 'inc/blocks.php';
require_once TOP_CORE_DIR . 'inc/schema.php';
require_once TOP_CORE_DIR . 'inc/integrations.php';

register_activation_hook( __FILE__, function () {
	top_register_post_types();
	flush_rewrite_rules();
} );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
