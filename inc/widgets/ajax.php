<?php
/**
 * Admin AJAX — searchable post / issue picker for Section Manager.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Search published posts (and optionally issues) by title.
 */
function altijara_ajax_search_posts() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}
	check_ajax_referer( 'altijara_admin_search', 'nonce' );

	$q         = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : 'post';
	if ( ! in_array( $post_type, array( 'post', 'issue', 'any' ), true ) ) {
		$post_type = 'post';
	}

	$types = ( 'any' === $post_type ) ? array( 'post', 'issue' ) : array( $post_type );
	$items = array();
	$seen  = array();

	$push = static function ( $post ) use ( &$items, &$seen ) {
		if ( ! $post instanceof WP_Post || isset( $seen[ (int) $post->ID ] ) ) {
			return;
		}
		$seen[ (int) $post->ID ] = true;
		$items[] = array(
			'id'    => (int) $post->ID,
			'title' => html_entity_decode( get_the_title( $post ), ENT_QUOTES, get_bloginfo( 'charset' ) ) . ' (#' . (int) $post->ID . ')',
			'type'  => $post->post_type,
			'date'  => get_the_date( 'Y-m-d', $post ),
		);
	};

	// Exact ID lookup — type a number to pick by ID.
	if ( '' !== $q && ctype_digit( $q ) ) {
		$by_id = get_post( (int) $q );
		if ( $by_id && 'publish' === $by_id->post_status && in_array( $by_id->post_type, $types, true ) ) {
			$push( $by_id );
		}
	}

	if ( strlen( $q ) < 2 && ! ctype_digit( $q ) ) {
		wp_send_json_success( array( 'items' => $items ) );
	}

	$args = array(
		'post_type'      => $types,
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	);
	if ( strlen( $q ) >= 2 ) {
		$args['s'] = $q;
	}

	$query = new WP_Query( $args );
	foreach ( $query->posts as $post ) {
		$push( $post );
	}
	wp_reset_postdata();

	wp_send_json_success( array( 'items' => $items ) );
}
add_action( 'wp_ajax_altijara_search_posts', 'altijara_ajax_search_posts' );

/**
 * Shared Section Manager CSS/JS (single handles for all admin screens).
 *
 * @param array<string, mixed> $args {
 *     Optional. style_deps (string[]), script_deps (string[]), i18n (array).
 * }
 */
function altijara_enqueue_section_manager_assets( $args = array() ) {
	$args         = is_array( $args ) ? $args : array();
	$style_deps   = isset( $args['style_deps'] ) && is_array( $args['style_deps'] ) ? $args['style_deps'] : array( 'dashicons' );
	$script_deps  = isset( $args['script_deps'] ) && is_array( $args['script_deps'] ) ? $args['script_deps'] : array();
	$i18n_extra   = isset( $args['i18n'] ) && is_array( $args['i18n'] ) ? $args['i18n'] : array();
	$css_path     = ALTIJARA_THEME_DIR . '/assets/css/admin-sections.css';
	$js_path      = ALTIJARA_THEME_DIR . '/assets/js/admin/section-manager.js';
	$css_ver      = file_exists( $css_path ) ? (string) filemtime( $css_path ) : ALTIJARA_THEME_VERSION;
	$js_ver       = file_exists( $js_path ) ? (string) filemtime( $js_path ) : ALTIJARA_THEME_VERSION;

	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style(
		'altijara-admin-sections',
		altijara_asset( 'assets/css/admin-sections.css' ),
		$style_deps,
		$css_ver
	);
	wp_enqueue_script(
		'altijara-admin-sections',
		altijara_asset( 'assets/js/admin/section-manager.js' ),
		$script_deps,
		$js_ver,
		true
	);

	$i18n = array_merge(
		array(
			'searchPlaceholder' => __( 'Search by title or ID…', 'altijara' ),
			'noResults'         => __( 'No results', 'altijara' ),
			'remove'            => __( 'Remove', 'altijara' ),
			'removeSection'     => __( 'Remove this section?', 'altijara' ),
			'searchCategories'  => __( 'Search categories…', 'altijara' ),
			'chooseCategory'    => __( 'Choose category', 'altijara' ),
			'noCategoryMatches' => __( 'No matches', 'altijara' ),
			'pairedModules'     => __( 'Paired modules', 'altijara' ),
		),
		$i18n_extra
	);

	wp_localize_script(
		'altijara-admin-sections',
		'AltijaraAdmin',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'altijara_admin_search' ),
			'i18n'    => $i18n,
		)
	);
}

/**
 * Enqueue Section Manager admin assets on Theme Options screens.
 *
 * @param string $hook_suffix Current admin page.
 */
function altijara_admin_enqueue_section_manager( $hook_suffix ) {
	$on_theme_screen = function_exists( 'altijara_is_theme_admin_screen' )
		? altijara_is_theme_admin_screen( $hook_suffix )
		: ( 'toplevel_page_altijara-theme' === $hook_suffix );
	if ( ! $on_theme_screen ) {
		return;
	}
	altijara_enqueue_section_manager_assets(
		array(
			'i18n' => array(
				'searchPlaceholder' => __( 'ابحث عن مقال بالعنوان…', 'altijara' ),
				'noResults'         => __( 'لا نتائج', 'altijara' ),
				'remove'            => __( 'إزالة', 'altijara' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'altijara_admin_enqueue_section_manager' );
