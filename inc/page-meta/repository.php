<?php
/**
 * Page meta read/merge helpers.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merge saved meta with defaults for a page.
 *
 * @param int $post_id Post ID.
 * @return array<string, mixed>
 */
function altijara_get_page_meta( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$schema  = altijara_get_page_schema_key( $post_id );
	if ( ! $schema ) {
		return array();
	}
	$defaults = altijara_page_meta_defaults( $schema );
	$saved    = get_post_meta( $post_id, ALTIJARA_PAGE_META_KEY, true );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	return altijara_page_meta_merge( $defaults, $saved );
}

/**
 * Recursive merge that keeps default list shapes when saved is empty.
 *
 * @param array<string, mixed> $defaults Defaults.
 * @param array<string, mixed> $saved    Saved.
 * @return array<string, mixed>
 */
function altijara_page_meta_merge( $defaults, $saved ) {
	$out = $defaults;
	foreach ( $saved as $key => $value ) {
		if ( is_array( $value ) && isset( $defaults[ $key ] ) && is_array( $defaults[ $key ] ) ) {
			$def = $defaults[ $key ];
			$is_list = empty( $def ) || array_keys( $def ) === range( 0, count( $def ) - 1 );
			if ( $is_list ) {
				// Indexed repeaters: use saved rows when present, else defaults.
				$out[ $key ] = ! empty( $value ) ? array_values( $value ) : $def;
			} else {
				$out[ $key ] = altijara_page_meta_merge( $def, $value );
			}
		} else {
			$out[ $key ] = $value;
		}
	}
	return $out;
}

/**
 * Get one page field with fallback.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Field key.
 * @param mixed  $default Optional override default.
 * @return mixed
 */
function altijara_get_page_field( $post_id, $key, $default = null ) {
	$meta = altijara_get_page_meta( $post_id );
	if ( array_key_exists( $key, $meta ) ) {
		return $meta[ $key ];
	}
	return $default;
}
