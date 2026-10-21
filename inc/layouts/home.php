<?php
/**
 * Home 2 layout: full builder storage + migrate + front render.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build full layout pack from Home 2 page meta (layout or legacy sections).
 *
 * @param int $page_id Page ID.
 * @return array{sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_get_home2_layout( $page_id = 0 ) {
	$page_id = $page_id ? absint( $page_id ) : ( function_exists( 'altijara_get_static_front_page_id' ) ? altijara_get_static_front_page_id() : 0 );
	$preset  = altijara_layout_preset_home();
	if ( ! $page_id ) {
		return $preset;
	}
	$meta = function_exists( 'altijara_get_page_meta' ) ? altijara_get_page_meta( $page_id ) : array();

	// New full layout.
	if ( ! empty( $meta['layout'] ) && is_array( $meta['layout'] ) ) {
		$pack = altijara_normalize_layout_pack( $meta['layout'] );
		if ( ! empty( $pack['sections'] ) ) {
			return $pack;
		}
	}

	// Migrate legacy content-only sections onto default matrix.
	$legacy = isset( $meta['sections'] ) && is_array( $meta['sections'] ) ? $meta['sections'] : array();
	$out    = $preset['sections'];
	foreach ( $out as $id => $def ) {
		$row = isset( $legacy[ $id ] ) && is_array( $legacy[ $id ] ) ? $legacy[ $id ] : array();
		if ( ! $row ) {
			continue;
		}
		if ( array_key_exists( 'visible', $row ) ) {
			$out[ $id ]['visible'] = ! empty( $row['visible'] );
		}
		if ( ! empty( $row['label'] ) ) {
			$out[ $id ]['label'] = (string) $row['label'];
		}
		if ( isset( $row['count'] ) ) {
			$out[ $id ]['count'] = max( 1, (int) $row['count'] );
		}
		$mode = isset( $row['source_mode'] ) ? sanitize_key( $row['source_mode'] ) : 'category';
		if ( 'random' === $mode ) {
			$mode               = 'category';
			$row['orderby']     = 'rand';
		}
		$manual = array();
		if ( ! empty( $row['manual_posts'] ) ) {
			$manual = array_values(
				array_filter(
					array_map(
						'absint',
						preg_split( '/[\s,]+/', (string) $row['manual_posts'] ) ?: array()
					)
				)
			);
		}
		if ( 'specific' === $mode && $manual ) {
			$out[ $id ]['manual_posts'] = $manual;
			$out[ $id ]['category']     = '';
		} else {
			$out[ $id ]['manual_posts'] = array();
			if ( ! empty( $row['category_id'] ) ) {
				$out[ $id ]['category'] = (string) absint( $row['category_id'] );
			}
			if ( ! empty( $row['orderby'] ) ) {
				$out[ $id ]['orderby'] = sanitize_key( $row['orderby'] );
			}
			if ( ! empty( $row['order'] ) ) {
				$out[ $id ]['order'] = ( 'ASC' === strtoupper( (string) $row['order'] ) ) ? 'ASC' : 'DESC';
			}
		}
		$out[ $id ] = altijara_normalize_section_config( $id, $out[ $id ], $def );
	}

	return array(
		'sections' => $out,
		'order'    => $preset['order'],
	);
}

/**
 * Sanitize Home 2 layout from page meta POST.
 *
 * @param array<string, mixed> $raw Raw altijara_page.
 * @return array{layout:array{sections:array,order:string[]}}
 */
function altijara_sanitize_home2_layout_meta( $raw ) {
	$sections = isset( $raw['layout_sections'] ) && is_array( $raw['layout_sections'] ) ? $raw['layout_sections'] : array();
	$order    = isset( $raw['layout_order'] ) && is_array( $raw['layout_order'] ) ? $raw['layout_order'] : array();
	$pack     = altijara_sanitize_layout( $sections, $order, array( 'lock_hero' => true ) );
	return array(
		'layout' => $pack,
	);
}
