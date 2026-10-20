<?php
/**
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register admin menu.
 */
function altijara_register_admin_menu() {
	add_menu_page(
		__( 'Altijara Theme Settings', 'altijara' ),
		__( 'Altijara Theme', 'altijara' ),
		'manage_options',
		'altijara-theme',
		'altijara_render_settings_page',
		'dashicons-admin-site-alt3',
		61
	);
	add_submenu_page(
		'altijara-theme',
		__( 'Theme Settings', 'altijara' ),
		__( 'Theme Settings', 'altijara' ),
		'manage_options',
		'altijara-theme',
		'altijara_render_settings_page'
	);
}
add_action( 'admin_menu', 'altijara_register_admin_menu' );

/**
 * Enqueue admin media + settings JS.
 *
 * @param string $hook Hook.
 */
function altijara_admin_enqueue_settings( $hook ) {
	if ( 'toplevel_page_altijara-theme' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style(
		'altijara-admin-settings',
		altijara_asset( 'assets/css/admin-sections.css' ),
		array( 'dashicons' ),
		ALTIJARA_THEME_VERSION
	);
	wp_enqueue_script(
		'altijara-admin-settings',
		altijara_asset( 'assets/js/admin/theme-settings.js' ),
		array( 'jquery' ),
		ALTIJARA_THEME_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'altijara_admin_enqueue_settings' );
