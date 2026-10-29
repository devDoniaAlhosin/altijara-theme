<?php
/**
 * Issues archive layout (theme option).
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Raw issues_archive_layout from the options row (no defaults merge).
 *
 * @return mixed null when unset.
 */
function altijara_get_issues_layout_raw() {
	$saved = get_option( ALTIJARA_OPTION_KEY, array() );
	if ( ! is_array( $saved ) || ! array_key_exists( 'issues_archive_layout', $saved ) ) {
		return null;
	}
	return $saved['issues_archive_layout'];
}

/**
 * Get issues archive layout pack.
 *
 * @param bool $fallback_preset Use preset when empty.
 * @return array{sections:array,order:string[]}
 */
function altijara_get_issues_layout( $fallback_preset = true ) {
	// Read raw storage so Reset (key removed / empty) is not confused with merged defaults.
	$raw = function_exists( 'altijara_get_issues_layout_raw' )
		? altijara_get_issues_layout_raw()
		: altijara_get_option( 'issues_archive_layout', null );
	if ( function_exists( 'altijara_layout_pack_is_stored' ) && altijara_layout_pack_is_stored( $raw ) ) {
		return altijara_normalize_layout_pack( $raw );
	}
	if ( $fallback_preset ) {
		return altijara_layout_preset_issues();
	}
	return array(
		'sections' => array(),
		'order'    => array(),
	);
}

/**
 * Whether a custom issues layout is saved (including an empty module list).
 */
function altijara_issues_has_custom_layout() {
	$raw = function_exists( 'altijara_get_issues_layout_raw' )
		? altijara_get_issues_layout_raw()
		: altijara_get_option( 'issues_archive_layout', null );
	return function_exists( 'altijara_layout_pack_is_stored' )
		? altijara_layout_pack_is_stored( $raw )
		: ( is_array( $raw ) && ! empty( $raw['sections'] ) );
}
