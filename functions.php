<?php
/**
 * Altijara Theme bootstrap
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ALTIJARA_THEME_VERSION', '2.3.0' );
define( 'ALTIJARA_THEME_DIR', get_template_directory() );
define( 'ALTIJARA_THEME_URI', get_template_directory_uri() );
if ( ! defined( 'ALTIJARA_OPTION_KEY' ) ) {
	define( 'ALTIJARA_OPTION_KEY', 'altijara_theme_settings' );
}
if ( ! defined( 'ALTIJARA_SETUP_FLAG' ) ) {
	define( 'ALTIJARA_SETUP_FLAG', 'altijara_theme_setup_state' );
}

require_once ALTIJARA_THEME_DIR . '/inc/load.php';
