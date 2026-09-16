<?php
/**
 * mac:lab — точка входа темы.
 *
 * @package maclab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MACLAB_VERSION', '1.0.0' );
define( 'MACLAB_DIR', get_template_directory() );
define( 'MACLAB_URI', get_template_directory_uri() );

require_once MACLAB_DIR . '/inc/setup.php';
require_once MACLAB_DIR . '/inc/settings.php';
require_once MACLAB_DIR . '/inc/products.php';
require_once MACLAB_DIR . '/inc/leads.php';
