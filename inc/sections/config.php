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

	if ( function_exists( 'altijara_sanitize_section_tag_ids' ) ) {
		$merged['tag'] = altijara_sanitize_section_tag_ids( $merged['tag'] ?? 0 );
	} elseif ( ! is_array( $merged['tag'] ?? null ) ) {
		$one           = absint( $merged['tag'] ?? 0 );
		$merged['tag'] = $one > 0 ? array( $one ) : array();
	}

	$filter = isset( $merged['category_filter'] ) ? sanitize_key( (string) $merged['category_filter'] ) : 'include';
	$merged['category_filter'] = ( 'exclude' === $filter ) ? 'exclude' : 'include';

	if ( function_exists( 'altijara_sanitize_section_category_ids' ) ) {
		if ( ! empty( $merged['categories'] ) ) {
			$merged['categories'] = altijara_sanitize_section_category_ids( $merged['categories'] );
		} elseif ( ! empty( $merged['category'] ) ) {
			$merged['categories'] = altijara_sanitize_section_category_ids( $merged['category'] );
		} else {
			$merged['categories'] = array();
		}
	} elseif ( empty( $merged['categories'] ) || ! is_array( $merged['categories'] ) ) {
		$merged['categories'] = array();
	}
	if ( $merged['categories'] && empty( $merged['category'] ) ) {
		$merged['category'] = (string) (int) $merged['categories'][0];
	}

	if ( function_exists( 'altijara_widget_supports' ) && altijara_widget_supports( $merged['widget'], 'article_source' ) ) {
		$cat_source = isset( $merged['category_source'] ) ? sanitize_key( (string) $merged['category_source'] ) : '';
		if ( ! in_array( $cat_source, array( 'same', 'selected' ), true ) ) {
			$cat_source = ! empty( $merged['categories'] ) ? 'selected' : 'same';
		}
		$merged['category_source'] = $cat_source;
		if ( 'same' === $cat_source ) {
			$merged['categories']      = array();
			$merged['category']         = '';
			$merged['category_filter']  = 'include';
		}
	} else {
		$merged['category_source'] = '';
	}

	if ( empty( $merged['variant'] ) ) {
		$merged['variant'] = ( 'article_grid' === ( $merged['widget'] ?? '' ) && 'tech' === $id ) ? 'cars_row' : 'default';
	} else {
		$merged['variant'] = sanitize_key( (string) $merged['variant'] );
		$wdef              = function_exists( 'altijara_get_widget_definition' )
			? altijara_get_widget_definition( $merged['widget'] )
			: null;
		if ( $wdef && ! empty( $wdef['variants'] ) && ! isset( $wdef['variants'][ $merged['variant'] ] ) ) {
			$merged['variant'] = ( 'article_grid' === $merged['widget'] && 'tech' === $id ) ? 'cars_row' : 'default';
		}
	}

	if ( empty( $merged['view_period'] ) ) {
		$merged['view_period'] = 'all';
	}
	if ( empty( $merged['source'] ) || ! in_array( $merged['source'], array( 'issues', 'posts' ), true ) ) {
		$merged['source'] = ( 'magazine_slider' === $merged['widget'] && 'hero_magazine' !== $id ) ? 'issues' : 'posts';
	}
	// Cover slider defaults to personality posts only until an editor picks another source or category.
	if ( 'hero_magazine' === $id ) {
		if ( 'posts' === $merged['source'] && empty( $merged['category'] ) && empty( $merged['categories'] ) ) {
			$merged['category'] = 'personality';
			if ( function_exists( 'altijara_sanitize_section_category_ids' ) ) {
				$merged['categories'] = altijara_sanitize_section_category_ids( 'personality' );
			}
		}
		if ( empty( $merged['count'] ) || (int) $merged['count'] < 1 ) {
			$merged['count'] = 6;
		}
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
	// Repair lost cars_row on already-saved tech + most-read pairs (no re-save needed).
	if ( 'article_grid' === ( $merged['widget'] ?? '' ) && '' !== $merged['pair_with'] ) {
		$merged['variant'] = 'cars_row';
	}

	return $merged;
}

/**
 * @return array<string, array<string, mixed>>
 */
function altijara_get_sections() {
	// Runtime override from layout renderer (category / issues / custom packs).
	if ( ! empty( $GLOBALS['altijara_layout_sections_override'] ) && is_array( $GLOBALS['altijara_layout_sections_override'] ) ) {
		return $GLOBALS['altijara_layout_sections_override'];
	}

	// Home 2 front page — sections come from the page metabox (front only; admin builder stays on theme options).
	if ( ! is_admin() && function_exists( 'altijara_front_uses_home2' ) && altijara_front_uses_home2() && function_exists( 'altijara_sections_from_home2_page' ) ) {
		$from_home2 = altijara_sections_from_home2_page();
		if ( is_array( $from_home2 ) && ! empty( $from_home2 ) ) {
			return $from_home2;
		}
	}

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
