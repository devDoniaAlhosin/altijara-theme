<?php
/**
 * Homepage sections — config getters
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
function altijara_normalize_section_config( $id, $row, $def = array() ) {
	if ( ! is_array( $row ) ) {
		$row = array();
	}
	if ( ! is_array( $def ) ) {
		$def = array();
	}
	$merged = array_merge( $def, $row );

	if ( empty( $merged['widget'] ) ) {
		$merged['widget'] = altijara_layout_to_widget( $merged['layout'] ?? 'news_grid' );
	} else {
		$merged['widget'] = sanitize_key( $merged['widget'] );
		if ( ! altijara_get_widget_definition( $merged['widget'] ) ) {
			$merged['widget'] = altijara_layout_to_widget( $merged['layout'] ?? 'news_grid' );
		}
	}

	// Keep legacy layout in sync for older callers / saves.
	$merged['layout'] = altijara_widget_to_layout( $merged['widget'] );

	if ( ! empty( $merged['manual_posts'] ) && ! is_array( $merged['manual_posts'] ) ) {
		$merged['manual_posts'] = array_values(
			array_filter( array_map( 'absint', preg_split( '/[\s,]+/', (string) $merged['manual_posts'] ) ?: array() ) )
		);
	}
	if ( empty( $merged['manual_posts'] ) || ! is_array( $merged['manual_posts'] ) ) {
		$merged['manual_posts'] = array();
	}

	if ( ! empty( $merged['issue_ids'] ) && ! is_array( $merged['issue_ids'] ) ) {
		$merged['issue_ids'] = array_values(
			array_filter( array_map( 'absint', preg_split( '/[\s,]+/', (string) $merged['issue_ids'] ) ?: array() ) )
		);
	}
	if ( empty( $merged['issue_ids'] ) || ! is_array( $merged['issue_ids'] ) ) {
		$merged['issue_ids'] = array();
	}

	if ( empty( $merged['variant'] ) ) {
		$merged['variant'] = ( 'tech' === $id ) ? 'cars_row' : 'default';
	}

	if ( empty( $merged['view_period'] ) ) {
		$merged['view_period'] = 'all';
	}
	if ( empty( $merged['source'] ) ) {
		$merged['source'] = ( 'magazine_slider' === $merged['widget'] ) ? 'issues' : 'posts';
	}

	$hero_ids = altijara_hero_section_ids();
	if ( empty( $merged['zone'] ) ) {
		$merged['zone'] = in_array( $id, $hero_ids, true ) ? 'hero' : 'body';
	} else {
		$merged['zone'] = ( 'hero' === $merged['zone'] ) ? 'hero' : 'body';
	}

	if ( isset( $merged['label'] ) ) {
		$merged['label'] = altijara_strip_hero_label_prefix( (string) $merged['label'] );
	}
	if ( empty( $merged['pair_with'] ) ) {
		$merged['pair_with'] = '';
	} else {
		$merged['pair_with'] = sanitize_key( $merged['pair_with'] );
	}

	return $merged;
}

/**
 * @return array<string, array<string, mixed>>
 */
function altijara_get_sections() {
	$saved   = altijara_get_option( 'sections', array() );
	$defaults = altijara_default_sections();
	$out     = array();

	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	// Always ensure hero defaults exist.
	foreach ( altijara_hero_section_ids() as $hid ) {
		$row        = isset( $saved[ $hid ] ) && is_array( $saved[ $hid ] ) ? $saved[ $hid ] : array();
		$out[ $hid ] = altijara_normalize_section_config( $hid, $row, $defaults[ $hid ] ?? array() );
	}

	// Body: use raw saved order when key was stored (empty array = user cleared body).
	$raw_opts  = get_option( ALTIJARA_OPTION_KEY, array() );
	$order_set = is_array( $raw_opts ) && array_key_exists( 'sections_order', $raw_opts );
	if ( $order_set && is_array( $raw_opts['sections_order'] ) ) {
		$body_order = $raw_opts['sections_order'];
	} else {
		$body_order = altijara_default_body_order();
		foreach ( array_keys( $saved ) as $sid ) {
			if ( in_array( $sid, altijara_hero_section_ids(), true ) ) {
				continue;
			}
			if ( ! in_array( $sid, $body_order, true ) ) {
				$body_order[] = $sid;
			}
		}
	}

	foreach ( $body_order as $bid ) {
		$bid = sanitize_key( $bid );
		if ( ! $bid || in_array( $bid, altijara_hero_section_ids(), true ) ) {
			continue;
		}
		$row = isset( $saved[ $bid ] ) && is_array( $saved[ $bid ] ) ? $saved[ $bid ] : array();
		$def = isset( $defaults[ $bid ] ) ? $defaults[ $bid ] : array(
			'zone'         => 'body',
			'widget'       => 'article_grid',
			'layout'       => 'news_grid',
			'label'        => $bid,
			'visible'      => true,
			'category'     => '',
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 4,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'issue_ids'    => array(),
			'view_period'  => 'all',
			'pair_with'    => '',
		);
		// Skip missing custom sections that were never saved (only defaults may hydrate empty).
		if ( ! isset( $saved[ $bid ] ) && ! isset( $defaults[ $bid ] ) ) {
			continue;
		}
		if ( ! isset( $saved[ $bid ] ) && isset( $defaults[ $bid ] ) ) {
			$row = array();
		}
		$out[ $bid ] = altijara_normalize_section_config( $bid, $row, $def );
	}

	return $out;
}

/**
 * Ordered body section IDs currently configured.
 *
 * @return string[]
 */
function altijara_get_body_section_ids() {
	$sections = altijara_get_sections();
	$ids      = array();
	foreach ( $sections as $id => $cfg ) {
		if ( 'body' === ( $cfg['zone'] ?? 'body' ) ) {
			$ids[] = $id;
		}
	}
	return $ids;
}

/**
 * @param string $section_key Section ID.
 * @return array<string, mixed>
 */
function altijara_get_section_config( $section_key ) {
	$sections = altijara_get_sections();
	$key      = sanitize_key( $section_key );
	if ( isset( $sections[ $key ] ) ) {
		return $sections[ $key ];
	}
	return altijara_normalize_section_config(
		$key,
		array(),
		array(
			'widget'       => 'article_grid',
			'layout'       => 'news_grid',
			'label'        => $key,
			'visible'      => true,
			'category'     => $key,
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 6,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'issue_ids'    => array(),
			'view_period'  => 'all',
		)
	);
}
