<?php
/**
 * Admin assets and body class for page meta.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue page-meta admin CSS/JS (shared by classic + block editor hooks).
 */
function altijara_admin_enqueue_page_meta_assets() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	$css_path = ALTIJARA_THEME_DIR . '/assets/css/admin-page-meta.css';
	$js_path  = ALTIJARA_THEME_DIR . '/assets/js/admin/page-meta.js';
	$css_ver  = file_exists( $css_path ) ? (string) filemtime( $css_path ) : ALTIJARA_THEME_VERSION;
	$js_ver   = file_exists( $js_path ) ? (string) filemtime( $js_path ) : ALTIJARA_THEME_VERSION;
	$post_id  = altijara_admin_editing_post_id();

	wp_enqueue_media();
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style(
		'altijara-page-meta',
		altijara_asset( 'assets/css/admin-page-meta.css' ),
		array( 'dashicons' ),
		$css_ver
	);

	// Post picker + Home 2 section controls (shared Section Manager handles).
	if ( function_exists( 'altijara_enqueue_section_manager_assets' ) ) {
		altijara_enqueue_section_manager_assets(
			array(
				'style_deps' => array( 'altijara-page-meta' ),
			)
		);
	}

	wp_enqueue_script(
		'altijara-page-meta',
		altijara_asset( 'assets/js/admin/page-meta.js' ),
		array( 'jquery', 'altijara-admin-sections' ),
		$js_ver,
		true
	);
	wp_localize_script(
		'altijara-page-meta',
		'altijaraPageMeta',
		array(
			'itemLabel'    => __( 'Item', 'altijara' ),
			'isUtility'    => $post_id ? altijara_is_utility_page_template( $post_id ) : false,
			'notice'       => __( 'Use Altijara Page Settings below for this page’s content. The block editor is hidden because layout lives in the theme template.', 'altijara' ),
			'contactAgent' => function_exists( 'altijara_page_meta_contact_agent_snapshot' )
				? altijara_page_meta_contact_agent_snapshot()
				: array(),
			'i18n'         => array(
				'synced'    => __( 'Copied from Contact. Save the page to keep changes.', 'altijara' ),
				'syncEmpty' => __( 'Contact page has no agent details yet.', 'altijara' ),
			),
		)
	);
}

/**
 * Admin assets for page meta UI.
 *
 * @param string $hook Hook.
 */
function altijara_admin_enqueue_page_meta( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	if ( ! altijara_admin_is_page_editor( $hook ) ) {
		return;
	}
	altijara_admin_enqueue_page_meta_assets();
}
add_action( 'admin_enqueue_scripts', 'altijara_admin_enqueue_page_meta', 20 );

/**
 * Also enqueue for the block editor (meta boxes live in the parent admin document).
 */
function altijara_block_editor_enqueue_page_meta() {
	if ( ! altijara_admin_is_page_editor( '' ) ) {
		return;
	}
	altijara_admin_enqueue_page_meta_assets();
}
add_action( 'enqueue_block_editor_assets', 'altijara_block_editor_enqueue_page_meta' );

/**
 * Body class so CSS can hide the unused block editor on utility templates.
 *
 * @param string $classes Classes.
 * @return string
 */
function altijara_admin_body_class_page_meta( $classes ) {
	$post_id = altijara_admin_editing_post_id();
	if ( $post_id && altijara_is_utility_page_template( $post_id ) ) {
		$classes .= ' altijara-utility-page-edit';
	}
	return $classes;
}
add_filter( 'admin_body_class', 'altijara_admin_body_class_page_meta' );

/**
 * Last-resort print of page-meta CSS if the handle was registered but not printed
 * (some block-editor setups skip late admin styles).
 */
function altijara_admin_print_page_meta_css() {
	if ( ! altijara_admin_is_page_editor( '' ) ) {
		return;
	}
	if ( wp_style_is( 'altijara-page-meta', 'done' ) || wp_style_is( 'altijara-page-meta', 'enqueued' ) ) {
		// Ensure it is printed even if only registered via block-editor hook.
		wp_enqueue_style( 'altijara-page-meta' );
		return;
	}
	altijara_admin_enqueue_page_meta_assets();
}
add_action( 'admin_print_styles-post.php', 'altijara_admin_print_page_meta_css', 5 );
add_action( 'admin_print_styles-post-new.php', 'altijara_admin_print_page_meta_css', 5 );