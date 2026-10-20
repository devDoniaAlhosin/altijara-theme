<?php
/**
 * Page meta schema: template map, keys, labels, admin detection.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ALTIJARA_PAGE_META_KEY', '_altijara_page_meta' );

/**
 * Map template file → schema key.
 *
 * @return array<string, string>
 */
function altijara_page_template_map() {
	return array(
		'page-about.php'     => 'about',
		'page-contact.php'   => 'contact',
		'page-advertise.php' => 'advertise',
		'page-subscribe.php' => 'subscribe',
		'page-login.php'     => 'login',
	);
}

/**
 * Resolve schema key for a page (from template slug).
 *
 * @param int $post_id Post ID.
 * @return string Empty if not a managed template.
 */
function altijara_get_page_schema_key( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	if ( ! $post_id ) {
		return '';
	}
	$slug = get_page_template_slug( $post_id );
	$map  = altijara_page_template_map();
	return isset( $map[ $slug ] ) ? $map[ $slug ] : '';
}

/**
 * Human labels for schema keys.
 *
 * @return array<string, string>
 */
function altijara_page_schema_labels() {
	return array(
		'about'     => __( 'About', 'altijara' ),
		'contact'   => __( 'Contact', 'altijara' ),
		'advertise' => __( 'Advertise', 'altijara' ),
		'subscribe' => __( 'Subscribe', 'altijara' ),
		'login'     => __( 'Login', 'altijara' ),
	);
}

/**
 * Whether current admin page is an Altijara utility template.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function altijara_is_utility_page_template( $post_id = 0 ) {
	return (bool) altijara_get_page_schema_key( $post_id );
}

/**
 * Resolve post ID being edited in admin (post.php / block editor).
 *
 * @return int
 */
function altijara_admin_editing_post_id() {
	if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return absint( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	if ( isset( $_POST['post_ID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return absint( $_POST['post_ID'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}
	return 0;
}

/**
 * Whether current admin request is editing a Page.
 *
 * @param string $hook Current admin hook (optional).
 * @return bool
 */
function altijara_admin_is_page_editor( $hook = '' ) {
	$post_id = altijara_admin_editing_post_id();
	if ( $post_id && 'page' === get_post_type( $post_id ) ) {
		return true;
	}

	if ( $hook && in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && 'page' === $screen->post_type ) {
			return true;
		}
		if ( 'post-new.php' === $hook ) {
			$type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : 'post'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return ( 'page' === $type );
		}
	}

	// Block editor / late screen detection.
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	return (bool) ( $screen && 'page' === $screen->post_type );
}
