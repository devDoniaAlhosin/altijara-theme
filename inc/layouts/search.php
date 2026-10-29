<?php
/**
 * Search results sidebar layout.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Saved search sidebar, or the preset.
 *
 * @return array{visible:bool,sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_get_search_sidebar_layout() {
	$raw = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'search_sidebar_layout', null ) : null;
	if ( function_exists( 'altijara_layout_pack_is_stored' ) && altijara_layout_pack_is_stored( $raw ) ) {
		if ( ! isset( $raw['visible'] ) ) {
			$raw['visible'] = true;
		}
		if ( empty( $raw['order'] ) || ! is_array( $raw['order'] ) ) {
			$raw['order'] = is_array( $raw['sections'] ) ? array_keys( $raw['sections'] ) : array();
		}
		if ( ! is_array( $raw['sections'] ) ) {
			$raw['sections'] = array();
		}
		return $raw;
	}
	if ( function_exists( 'altijara_layout_preset_search_sidebar' ) ) {
		return altijara_layout_preset_search_sidebar();
	}
	return array(
		'visible'  => false,
		'sections' => array(),
		'order'    => array(),
	);
}
