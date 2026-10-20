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
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}
	check_ajax_referer( 'altijara_admin_search', 'nonce' );

	$q         = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : 'post';
	if ( ! in_array( $post_type, array( 'post', 'issue', 'any' ), true ) ) {
		$post_type = 'post';
	}

	$types = ( 'any' === $post_type ) ? array( 'post', 'issue' ) : array( $post_type );

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
	$items = array();
	foreach ( $query->posts as $post ) {
		$items[] = array(
			'id'    => (int) $post->ID,
			'title' => html_entity_decode( get_the_title( $post ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'type'  => $post->post_type,
			'date'  => get_the_date( 'Y-m-d', $post ),
		);
	}
	wp_reset_postdata();

	wp_send_json_success( array( 'items' => $items ) );
}
add_action( 'wp_ajax_altijara_search_posts', 'altijara_ajax_search_posts' );

/**
 * Enqueue Section Manager admin assets.
 *
 * @param string $hook_suffix Current admin page.
 */
function altijara_admin_enqueue_section_manager( $hook_suffix ) {
	if ( 'toplevel_page_altijara-theme' !== $hook_suffix ) {
		return;
	}
	/* Homepage panel is always in the SPA DOM — load picker on every theme settings view. */

	$js = ALTIJARA_THEME_DIR . '/assets/js/admin/section-manager.js';
	wp_enqueue_script(
		'altijara-admin-sections',
		altijara_asset( 'assets/js/admin/section-manager.js' ),
		array(),
		file_exists( $js ) ? (string) filemtime( $js ) : ALTIJARA_THEME_VERSION,
		true
	);

	wp_localize_script(
		'altijara-admin-sections',
		'AltijaraAdmin',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'altijara_admin_search' ),
			'i18n'    => array(
				'searchPlaceholder' => __( 'ابحث عن مقال بالعنوان…', 'altijara' ),
				'noResults'         => __( 'لا نتائج', 'altijara' ),
				'remove'            => __( 'إزالة', 'altijara' ),
				'removeSection'     => __( 'Remove this section?', 'altijara' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'altijara_admin_enqueue_section_manager' );
