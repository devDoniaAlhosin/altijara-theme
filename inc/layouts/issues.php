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
 * Get issues archive layout pack.
 *
 * @param bool $fallback_preset Use preset when empty.
 * @return array{sections:array,order:string[]}
 */
function altijara_get_issues_layout( $fallback_preset = true ) {
	$raw = altijara_get_option( 'issues_archive_layout', null );
	if ( is_array( $raw ) && ! empty( $raw['sections'] ) ) {
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
 * Whether a custom issues layout is saved.
 */
function altijara_issues_has_custom_layout() {
	$raw = altijara_get_option( 'issues_archive_layout', null );
	return is_array( $raw ) && ! empty( $raw['sections'] );
}
