<?php
/**
 * Shared layout sanitize (sections map + order).
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a layout option/meta was explicitly saved (including an empty module list).
 *
 * Reset / never-configured values are null or array() without a sections key, and
 * those still fall back to the theme preset. A saved empty pack keeps sections=[].
 *
 * @param mixed $raw Raw option or term meta.
 * @return bool
 */
function altijara_layout_pack_is_stored( $raw ) {
	return is_array( $raw ) && array_key_exists( 'sections', $raw );
}

/**
 * Sanitize a freeform layout (same shape as homepage sections).
 *
 * @param array<string, mixed> $raw_sections Sections from POST/storage.
 * @param array<int, string>   $order        Body order IDs.
 * @param array<string, mixed> $args         Optional: lock_hero (bool), allow_empty_hero (bool).
 * @return array{sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_sanitize_layout( $raw_sections, $order = array(), $args = array() ) {
	$lock_hero = ! array_key_exists( 'lock_hero', $args ) || ! empty( $args['lock_hero'] );

	if ( ! is_array( $raw_sections ) ) {
		$raw_sections = array();
	}
	if ( ! is_array( $order ) ) {
		$order = array();
	}

	// Reuse homepage sanitizer when hero is locked (theme options / Home 2).
	if ( $lock_hero && function_exists( 'altijara_sanitize_sections_input' ) ) {
		return altijara_sanitize_sections_input( $raw_sections, $order );
	}

	// Fully freeform (category / issues): no forced hero slots.
	$out      = array();
	$body_ids = array();
	if ( ! empty( $order ) ) {
		foreach ( $order as $oid ) {
			$oid = sanitize_key( $oid );
			if ( $oid && isset( $raw_sections[ $oid ] ) && is_array( $raw_sections[ $oid ] ) ) {
				$body_ids[] = $oid;
			}
		}
	}
	foreach ( array_keys( $raw_sections ) as $rid ) {
		$rid = sanitize_key( $rid );
		if ( ! $rid || in_array( $rid, $body_ids, true ) ) {
			continue;
		}
		if ( is_array( $raw_sections[ $rid ] ) ) {
			$body_ids[] = $rid;
		}
	}

	foreach ( $body_ids as $bid ) {
		$row         = is_array( $raw_sections[ $bid ] ) ? $raw_sections[ $bid ] : array();
		$out[ $bid ] = altijara_sanitize_section_row( $bid, $row, array( 'zone' => 'body' ) );
		$out[ $bid ]['zone'] = 'body';
	}

	return array(
		'sections' => $out,
		'order'    => $body_ids,
	);
}

/**
 * Whether a layout section belongs to the hero zone (not body modules).
 *
 * @param string               $id  Section ID.
 * @param array<string, mixed> $row Section config.
 * @return bool
 */
function altijara_layout_section_is_hero( $id, $row = array() ) {
	$id = sanitize_key( $id );
	if ( ! $id ) {
		return false;
	}
	$hero_ids = function_exists( 'altijara_hero_section_ids' ) ? altijara_hero_section_ids() : array();
	if ( in_array( $id, $hero_ids, true ) ) {
		return true;
	}
	return is_array( $row ) && ! empty( $row['zone'] ) && 'hero' === $row['zone'];
}

/**
 * Normalize stored layout pack to sections + order.
 * Hero slots stay in `sections` for the hero renderer; `order` is body-only
 * (admin Modules list). Never append hero IDs into body order.
 *
 * @param mixed $pack Raw layout.
 * @return array{sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_normalize_layout_pack( $pack ) {
	if ( ! is_array( $pack ) ) {
		return array(
			'sections' => array(),
			'order'    => array(),
		);
	}
	$sections      = isset( $pack['sections'] ) && is_array( $pack['sections'] ) ? $pack['sections'] : array();
	$order_key_set = array_key_exists( 'order', $pack );
	$order         = ( $order_key_set && is_array( $pack['order'] ) )
		? array_map( 'sanitize_key', $pack['order'] )
		: array();

	$normalize_row = static function ( $id, $row ) {
		return function_exists( 'altijara_normalize_section_config' )
			? altijara_normalize_section_config( $id, $row, array() )
			: $row;
	};

	// Seed body order from stored order, skipping hero slots.
	$body_order = array();
	foreach ( $order as $id ) {
		$id = sanitize_key( $id );
		if ( ! $id || empty( $sections[ $id ] ) || ! is_array( $sections[ $id ] ) ) {
			continue;
		}
		if ( altijara_layout_section_is_hero( $id, $sections[ $id ] ) ) {
			continue;
		}
		$body_order[] = $id;
	}

	// Only invent body order from section keys when order was never stored.
	// Explicit empty order means the editor cleared all body modules.
	if ( ! $order_key_set && empty( $body_order ) && $sections ) {
		foreach ( $sections as $id => $row ) {
			$id = sanitize_key( $id );
			if ( ! $id || ! is_array( $row ) || altijara_layout_section_is_hero( $id, $row ) ) {
				continue;
			}
			$body_order[] = $id;
		}
	}

	$normalized = array();

	// Keep hero sections in the map (rendered by hero grid, not body loop).
	foreach ( $sections as $id => $row ) {
		$id = sanitize_key( $id );
		if ( ! $id || ! is_array( $row ) || ! altijara_layout_section_is_hero( $id, $row ) ) {
			continue;
		}
		$normalized[ $id ] = $normalize_row( $id, $row );
	}

	foreach ( $body_order as $id ) {
		if ( empty( $sections[ $id ] ) || ! is_array( $sections[ $id ] ) ) {
			continue;
		}
		$normalized[ $id ] = $normalize_row( $id, $sections[ $id ] );
	}

	// Append body sections missing from order — never hero slots.
	// When order was explicitly saved (even empty), trust it and do not rehydrate.
	if ( ! $order_key_set ) {
		foreach ( $sections as $id => $row ) {
			$id = sanitize_key( $id );
			if ( ! $id || isset( $normalized[ $id ] ) || ! is_array( $row ) ) {
				continue;
			}
			if ( altijara_layout_section_is_hero( $id, $row ) ) {
				$normalized[ $id ] = $normalize_row( $id, $row );
				continue;
			}
			$normalized[ $id ] = $normalize_row( $id, $row );
			$body_order[]      = $id;
		}
	} else {
		// Still keep any hero rows that were skipped above.
		foreach ( $sections as $id => $row ) {
			$id = sanitize_key( $id );
			if ( ! $id || isset( $normalized[ $id ] ) || ! is_array( $row ) ) {
				continue;
			}
			if ( altijara_layout_section_is_hero( $id, $row ) ) {
				$normalized[ $id ] = $normalize_row( $id, $row );
			}
		}
	}

	return array(
		'sections' => $normalized,
		'order'    => array_values( array_unique( array_filter( $body_order ) ) ),
	);
}
