<?php
/**
 * Top Appliance theme bootstrap.
 */

defined( 'ABSPATH' ) || exit;

define( 'TOP_THEME_VERSION', '1.0.0' );
define( 'TOP_THEME_DIR', get_template_directory() );
define( 'TOP_THEME_URI', get_template_directory_uri() );

require_once TOP_THEME_DIR . '/inc/setup.php';
require_once TOP_THEME_DIR . '/inc/assets.php';
require_once TOP_THEME_DIR . '/inc/cleanup.php';
require_once TOP_THEME_DIR . '/inc/images.php';
require_once TOP_THEME_DIR . '/inc/patterns.php';
