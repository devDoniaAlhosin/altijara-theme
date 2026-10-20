<?php
/**
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get all settings (merged with defaults for read — does not write).
 *
 * @return array<string, mixed>
 */
function altijara_get_options() {
	$defaults = altijara_default_options();
	$saved    = get_option( ALTIJARA_OPTION_KEY, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	// If a branch was corrupted to a non-array, drop it so defaults win.
	foreach ( array_keys( $defaults ) as $branch ) {
		if ( array_key_exists( $branch, $saved ) && is_array( $defaults[ $branch ] ) && ! is_array( $saved[ $branch ] ) ) {
			unset( $saved[ $branch ] );
		}
	}

	$merged = array_replace_recursive( $defaults, $saved );

	// Guarantee critical admin branches are arrays (prevents fatal on theme options).
	foreach ( array( 'general', 'design', 'header', 'footer', 'social', 'article', 'magazine', 'advertising', 'integrations', 'advanced', 'category_map', 'sections', 'sections_order', 'archive_layouts' ) as $branch ) {
		if ( ! isset( $merged[ $branch ] ) || ! is_array( $merged[ $branch ] ) ) {
			$merged[ $branch ] = isset( $defaults[ $branch ] ) && is_array( $defaults[ $branch ] ) ? $defaults[ $branch ] : array();
		}
	}
	if ( empty( $merged['social']['items'] ) || ! is_array( $merged['social']['items'] ) ) {
		$merged['social']['items'] = isset( $defaults['social']['items'] ) && is_array( $defaults['social']['items'] )
			? $defaults['social']['items']
			: array();
	}

	// category_map values must be strings (WP 6.7+ esc_attr(null) TypeError → HTTP 500).
	if ( is_array( $merged['category_map'] ) ) {
		$clean_map = array();
		foreach ( $merged['category_map'] as $k => $v ) {
			$clean_map[ sanitize_key( (string) $k ) ] = is_scalar( $v ) ? (string) $v : '';
		}
		$merged['category_map'] = $clean_map;
	}

	return $merged;
}

/**
 * @param string $key     Root key.
 * @param mixed  $default Default.
 * @return mixed
 */
function altijara_get_option( $key, $default = null ) {
	$opts = altijara_get_options();
	if ( array_key_exists( $key, $opts ) ) {
		return $opts[ $key ];
	}
	return $default;
}

/**
 * Merge-patch options (does not delete sibling keys).
 *
 * @param array<string, mixed> $patch Patch.
 */
function altijara_update_options( $patch ) {
	$saved = get_option( ALTIJARA_OPTION_KEY, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	$opts = array_replace_recursive( $saved, $patch );
	update_option( ALTIJARA_OPTION_KEY, $opts, false );
	if ( function_exists( 'altijara_cache_purge_all' ) ) {
		altijara_cache_purge_all();
	}
}
