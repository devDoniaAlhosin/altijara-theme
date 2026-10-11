<?php
/**
 * Transient caching helpers
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default cache TTL (15 minutes).
 */
function altijara_cache_ttl() {
	return (int) apply_filters( 'altijara_cache_ttl', 15 * MINUTE_IN_SECONDS );
}

/**
 * Build a stable cache key.
 *
 * @param string               $group Group prefix.
 * @param array<string, mixed> $args  Args hashed into key.
 */
function altijara_cache_key( $group, $args = array() ) {
	return 'altijara_' . sanitize_key( $group ) . '_' . md5( wp_json_encode( $args ) );
}

/**
 * Get a cached value.
 *
 * @param string $key Cache key.
 * @return mixed|false
 */
function altijara_cache_get( $key ) {
	return get_transient( $key );
}

/**
 * Store a cached value.
 *
 * @param string $key  Cache key.
 * @param mixed  $data Data.
 * @param int    $ttl  TTL seconds.
 */
function altijara_cache_set( $key, $data, $ttl = null ) {
	if ( null === $ttl ) {
		$ttl = altijara_cache_ttl();
	}
	return set_transient( $key, $data, (int) $ttl );
}

/**
 * Delete a cached value.
 *
 * @param string $key Cache key.
 */
function altijara_cache_delete( $key ) {
	return delete_transient( $key );
}

/**
 * Purge all known Altijara query/section caches.
 */
function altijara_cache_purge_all() {
	global $wpdb;

	$like = $wpdb->esc_like( '_transient_altijara_' ) . '%';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$keys = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
			$like
		)
	);

	if ( empty( $keys ) ) {
		do_action( 'altijara_cache_purged' );
		return;
	}

	foreach ( $keys as $option_name ) {
		$key = str_replace( '_transient_', '', $option_name );
		$key = str_replace( '_transient_timeout_', '', $key );
		delete_transient( $key );
	}

	do_action( 'altijara_cache_purged' );
}

/**
 * Invalidate caches when content changes.
 *
 * @param int $post_id Post ID.
 */
function altijara_cache_invalidate_on_save( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
		return;
	}
	altijara_cache_purge_all();
}
add_action( 'save_post', 'altijara_cache_invalidate_on_save' );
add_action( 'deleted_post', 'altijara_cache_purge_all' );
add_action( 'edited_term', 'altijara_cache_purge_all' );
add_action( 'delete_term', 'altijara_cache_purge_all' );
