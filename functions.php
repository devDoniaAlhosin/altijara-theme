<?php
/**
 * Altijara Theme bootstrap
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ALTIJARA_THEME_VERSION', '2.2.2' );
define( 'ALTIJARA_THEME_DIR', get_template_directory() );
define( 'ALTIJARA_THEME_URI', get_template_directory_uri() );
if ( ! defined( 'ALTIJARA_OPTION_KEY' ) ) {
	define( 'ALTIJARA_OPTION_KEY', 'altijara_theme_settings' );
}
if ( ! defined( 'ALTIJARA_SETUP_FLAG' ) ) {
	define( 'ALTIJARA_SETUP_FLAG', 'altijara_theme_setup_state' );
}

$altijara_includes = array(
	'/inc/helpers.php',
	'/inc/design.php',
	'/inc/defaults.php',
	'/inc/caching.php',
	'/inc/queries.php',
	'/inc/social.php',
	'/inc/nav.php',
	'/inc/widgets/registry.php',
	'/inc/widgets/query-engine.php',
	'/inc/widgets/renderer.php',
	'/inc/widgets/ajax.php',
	'/inc/section-manager.php',
	'/inc/theme-options.php',
	'/inc/activation.php',
	'/inc/setup.php',
	'/inc/enqueue.php',
);

foreach ( $altijara_includes as $file ) {
	$path = ALTIJARA_THEME_DIR . $file;
	if ( file_exists( $path ) ) {
		require_once $path;
	}
}
