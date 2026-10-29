<?php
/**
 * Tag archive sidebar layout.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default tag sidebar: advertisement, magazine slider, most read.
 *
 * @return array{visible:bool,sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_layout_preset_tag_sidebar() {
	$ad  = function_exists( 'altijara_category_archive_widget_section' )
		? altijara_category_archive_widget_section( 'cat_sidebar_ad', __( 'Advertisement', 'altijara' ) )
		: array();
	$mag = function_exists( 'altijara_category_archive_widget_section' )
		? altijara_category_archive_widget_section( 'cat_sidebar_magazine', __( 'من إصدارات المجلة', 'altijara' ) )
		: array();
	$mr  = function_exists( 'altijara_category_archive_widget_section' )
		? altijara_category_archive_widget_section( 'cat_sidebar_most_read', __( 'الأكثر قراءة', 'altijara' ) )
		: array();

	$sections = array(
		'tag_side_ad'       => $ad,
		'tag_side_magazine' => $mag,
		'tag_side_most'     => $mr,
	);

	return array(
		'visible'  => true,
		'sections' => $sections,
		'order'    => array_keys( $sections ),
	);
}

/**
 * Saved tag sidebar, or the preset.
 *
 * @return array{visible:bool,sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_get_tag_sidebar_layout() {
	$raw = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'tag_sidebar_layout', null ) : null;
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
	return altijara_layout_preset_tag_sidebar();
}
