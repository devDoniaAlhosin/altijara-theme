<?php
/**
 * Homepage sections — sanitize
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize section tag field to a unique list of term IDs.
 *
 * @param mixed $raw Int, array, or comma-separated string.
 * @return int[]
 */
function altijara_sanitize_section_tag_ids( $raw ) {
	if ( is_array( $raw ) ) {
		$ids = array_map( 'absint', $raw );
	} elseif ( is_string( $raw ) && false !== strpos( $raw, ',' ) ) {
		$ids = array_map( 'absint', preg_split( '/[\s,]+/', $raw ) ?: array() );
	} else {
		$one = absint( $raw );
		$ids = $one > 0 ? array( $one ) : array();
	}
	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Tag IDs from a section config (supports legacy single int).
 *
 * @param array<string, mixed> $section Section config.
 * @return int[]
 */
function altijara_section_tag_ids( $section ) {
	if ( ! is_array( $section ) ) {
		return array();
	}
	return altijara_sanitize_section_tag_ids( $section['tag'] ?? 0 );
}

/**
 * Normalize section category field(s) to unique term IDs.
 *
 * Accepts int, array, comma-separated string, or legacy slug keys via resolve.
 *
 * @param mixed $raw Raw categories value.
 * @return int[]
 */
function altijara_sanitize_section_category_ids( $raw ) {
	$parts = array();
	if ( is_array( $raw ) ) {
		$parts = $raw;
	} elseif ( is_string( $raw ) && '' !== trim( $raw ) ) {
		$parts = preg_split( '/[\s,]+/', $raw ) ?: array();
	} elseif ( is_numeric( $raw ) ) {
		$parts = array( $raw );
	}
	$ids = array();
	foreach ( $parts as $part ) {
		if ( is_numeric( $part ) ) {
			$id = absint( $part );
		} elseif ( is_string( $part ) && '' !== $part && function_exists( 'altijara_resolve_category_id' ) ) {
			$id = absint( altijara_resolve_category_id( $part ) );
		} else {
			$id = 0;
		}
		if ( $id > 0 ) {
			$ids[] = $id;
		}
	}
	return array_values( array_unique( $ids ) );
}

/**
 * Category IDs from a section config (supports legacy single category).
 *
 * @param array<string, mixed> $section Section config.
 * @return int[]
 */
function altijara_section_category_ids( $section ) {
	if ( ! is_array( $section ) ) {
		return array();
	}
	if ( ! empty( $section['categories'] ) && is_array( $section['categories'] ) ) {
		return altijara_sanitize_section_category_ids( $section['categories'] );
	}
	if ( ! empty( $section['category'] ) ) {
		return altijara_sanitize_section_category_ids( $section['category'] );
	}
	return array();
}

/**
 * Category filter mode for a section: include|exclude.
 *
 * @param array<string, mixed> $section Section config.
 * @return string
 */
function altijara_section_category_filter( $section ) {
	if ( ! is_array( $section ) ) {
		return 'include';
	}
	$filter = isset( $section['category_filter'] ) ? sanitize_key( (string) $section['category_filter'] ) : 'include';
	return ( 'exclude' === $filter ) ? 'exclude' : 'include';
}

/**
 * Article category source mode: same|selected|'' (empty = not an article-source widget).
 *
 * @param array<string, mixed> $section Section config.
 * @return string
 */
function altijara_section_category_source( $section ) {
	if ( ! is_array( $section ) ) {
		return '';
	}
	$source = isset( $section['category_source'] ) ? sanitize_key( (string) $section['category_source'] ) : '';
	if ( in_array( $source, array( 'same', 'selected' ), true ) ) {
		return $source;
	}
	return '';
}

/**
 * Whether a section should include posts from child categories.
 *
 * Explicit section value wins. Legacy category-archive modules without a saved
 * value follow the Category Archives setting; everything else defaults to on.
 *
 * @param array<string, mixed> $section Section config.
 * @return bool
 */
function altijara_section_include_children( $section ) {
	if ( ! is_array( $section ) ) {
		return true;
	}
	if ( array_key_exists( 'include_children', $section ) ) {
		return ! empty( $section['include_children'] ) && '0' !== (string) $section['include_children'];
	}
	$widget = isset( $section['widget'] ) ? sanitize_key( (string) $section['widget'] ) : '';
	if (
		in_array(
			$widget,
			array(
				'cat_archive_hero',
				'cat_archive_featured',
				'cat_archive_news',
				'cat_archive_lower',
				'cat_sidebar_most_read',
			),
			true
		)
		&& function_exists( 'altijara_archive_include_children' )
	) {
		return altijara_archive_include_children();
	}
	return true;
}

function altijara_sanitize_section_row( $id, $row, $def = array() ) {
	$widgets = array_keys( altijara_get_widget_registry() );
	$hero    = in_array( $id, altijara_hero_section_ids(), true );

	$widget = isset( $row['widget'] ) ? sanitize_key( $row['widget'] ) : ( $def['widget'] ?? 'article_grid' );
	if ( ! in_array( $widget, $widgets, true ) ) {
		$widget = ! empty( $row['layout'] ) ? altijara_layout_to_widget( $row['layout'] ) : ( $def['widget'] ?? 'article_grid' );
	}
	if ( ! in_array( $widget, $widgets, true ) ) {
		$widget = $def['widget'] ?? 'article_grid';
	}

	$manual_raw = isset( $row['manual_posts'] ) ? (string) $row['manual_posts'] : '';
	$manual_ids = array_values(
		array_filter( array_map( 'absint', preg_split( '/[\s,]+/', $manual_raw ) ?: array() ) )
	);
	$issue_raw = isset( $row['issue_ids'] ) ? (string) $row['issue_ids'] : '';
	$issue_ids = array_values(
		array_filter( array_map( 'absint', preg_split( '/[\s,]+/', $issue_raw ) ?: array() ) )
	);

	$orderby = isset( $row['orderby'] ) ? sanitize_key( $row['orderby'] ) : ( $def['orderby'] ?? 'date' );
	if ( ! in_array( $orderby, array( 'date', 'modified', 'title', 'rand', 'menu_order', 'comment_count', 'issue_number' ), true ) ) {
		$orderby = 'date';
	}

	$variant = isset( $row['variant'] ) ? sanitize_key( $row['variant'] ) : ( $def['variant'] ?? 'default' );
	$wdef    = altijara_get_widget_definition( $widget );
	if ( $wdef && ! empty( $wdef['variants'] ) && ! isset( $wdef['variants'][ $variant ] ) ) {
		$variant = 'default';
	}
	$pair_with = isset( $row['pair_with'] ) ? sanitize_key( $row['pair_with'] ) : ( $def['pair_with'] ?? '' );
	// Tech + Most Read pairs (and the classic تقنية slot) always use cars_row.
	if ( 'article_grid' === $widget && $pair_with ) {
		$variant = 'cars_row';
	} elseif ( '' === (string) ( $row['variant'] ?? '' ) && 'article_grid' === $widget && 'tech' === $id ) {
		$variant = 'cars_row';
	}

	$view_period = isset( $row['view_period'] ) ? sanitize_key( $row['view_period'] ) : ( $def['view_period'] ?? 'all' );
	if ( ! in_array( $view_period, array( 'day', 'week', 'month', 'year', 'all' ), true ) ) {
		$view_period = 'all';
	}

	$source = isset( $row['source'] ) ? sanitize_key( $row['source'] ) : ( $def['source'] ?? 'posts' );
	if ( ! in_array( $source, array( 'issues', 'posts' ), true ) ) {
		$source = 'posts';
	}

	$category_filter = isset( $row['category_filter'] ) ? sanitize_key( (string) $row['category_filter'] ) : ( isset( $def['category_filter'] ) ? sanitize_key( (string) $def['category_filter'] ) : 'include' );
	if ( 'exclude' !== $category_filter ) {
		$category_filter = 'include';
	}

	$categories = array();
	if ( ! empty( $row['categories_present'] ) || array_key_exists( 'categories', $row ) ) {
		$categories = altijara_sanitize_section_category_ids( $row['categories'] ?? array() );
	} elseif ( array_key_exists( 'category', $row ) && '' !== (string) $row['category'] && '0' !== (string) $row['category'] ) {
		$categories = altijara_sanitize_section_category_ids( $row['category'] );
	} elseif ( ! empty( $def['categories'] ) ) {
		$categories = altijara_sanitize_section_category_ids( $def['categories'] );
	} elseif ( ! empty( $def['category'] ) ) {
		$categories = altijara_sanitize_section_category_ids( $def['category'] );
	}

	$supports_article_src = function_exists( 'altijara_widget_supports' ) && altijara_widget_supports( $widget, 'article_source' );
	$category_source      = '';
	if ( $supports_article_src ) {
		if ( array_key_exists( 'category_source', $row ) ) {
			$category_source = sanitize_key( (string) $row['category_source'] );
		} elseif ( array_key_exists( 'category_source', $def ) ) {
			$category_source = sanitize_key( (string) $def['category_source'] );
		} else {
			// Migration: previously filled categories meant selected; empty meant same.
			$category_source = $categories ? 'selected' : 'same';
		}
		if ( ! in_array( $category_source, array( 'same', 'selected' ), true ) ) {
			$category_source = 'same';
		}
		if ( 'same' === $category_source ) {
			$categories      = array();
			$category_filter = 'include';
		}
	}

	// Primary legacy field: first included category (empty when none / exclude-only lists still keep first for BC labels when include).
	$cat = $categories ? (string) (int) $categories[0] : '';

	// Category-archive stream modules follow the viewed category (no source override).
	// Sidebar most-read may keep a chosen category (search/tag sitewide when empty).
	$category_stream = in_array(
		$widget,
		array( 'cat_archive_hero', 'cat_archive_featured', 'cat_archive_news', 'cat_archive_lower' ),
		true
	);
	if ( $category_stream ) {
		$cat             = '';
		$categories      = array();
		$category_filter = 'include';
		$manual_ids      = array();
		$issue_ids       = array();
		if ( 'cat_archive_hero' === $widget ) {
			$variant = 'default';
		}
	}

	$label = isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : ( $def['label'] ?? $id );
	$label = altijara_strip_hero_label_prefix( $label );

	$sidebar_order = isset( $row['sidebar_order'] ) ? sanitize_text_field( (string) $row['sidebar_order'] ) : ( isset( $def['sidebar_order'] ) ? sanitize_text_field( (string) $def['sidebar_order'] ) : '' );
	if ( isset( $row['sidebar_positions'] ) && is_array( $row['sidebar_positions'] ) && function_exists( 'altijara_sanitize_archive_sidebar' ) ) {
		$clean         = altijara_sanitize_archive_sidebar(
			array(
				'visible'   => 1,
				'positions' => $row['sidebar_positions'],
			)
		);
		$sidebar_order = implode( ',', $clean['order'] );
	} elseif ( '' !== $sidebar_order ) {
		$allowed = function_exists( 'altijara_archive_sidebar_widgets' ) ? altijara_archive_sidebar_widgets() : array( 'ad', 'magazine', 'most_read' );
		$parts   = array_values( array_filter( array_map( 'sanitize_key', explode( ',', $sidebar_order ) ) ) );
		$parts   = array_values( array_intersect( $parts, $allowed ) );
		foreach ( $allowed as $widget_key ) {
			if ( ! in_array( $widget_key, $parts, true ) ) {
				$parts[] = $widget_key;
			}
		}
		$sidebar_order = implode( ',', $parts );
	}

	$zone = isset( $row['zone'] ) ? sanitize_key( $row['zone'] ) : ( $def['zone'] ?? '' );
	if ( 'hero' !== $zone && 'body' !== $zone ) {
		$zone = $hero ? 'hero' : 'body';
	}

	if ( 'cat_archive_hero' === $widget ) {
		$count = isset( $row['count'] ) ? max( 0, min( 30, (int) $row['count'] ) ) : (int) ( $def['count'] ?? 3 );
	} elseif ( 'cat_archive_featured' === $widget ) {
		$count = isset( $row['count'] ) ? (int) $row['count'] : (int) ( $def['count'] ?? 8 );
		if ( ! in_array( $count, array( 4, 8, 12 ), true ) ) {
			$count = 8;
		}
	} elseif ( 'cat_archive_news' === $widget ) {
		$count = isset( $row['count'] ) ? max( 1, min( 50, (int) $row['count'] ) ) : (int) ( $def['count'] ?? 10 );
	} elseif ( 'cat_sidebar_magazine' === $widget ) {
		$count = isset( $row['count'] ) ? max( 1, min( 20, (int) $row['count'] ) ) : (int) ( $def['count'] ?? 8 );
	} else {
		$count = isset( $row['count'] ) ? max( 1, min( 48, (int) $row['count'] ) ) : (int) ( $def['count'] ?? 4 );
	}
	if ( 'article_list' === $widget || 'hero_interviews' === $id ) {
		$count = isset( $row['count'] ) ? max( 1, min( 200, (int) $row['count'] ) ) : max( 1, (int) $count );
	}
	if ( 'issues_lead' === $widget ) {
		$count = 1;
	}
	$pick_max = $count;
	if ( 'cat_archive_hero' === $widget ) {
		$pick_max = 1 + $count;
	}
	if ( $pick_max > 0 ) {
		if ( count( $manual_ids ) > $pick_max ) {
			$manual_ids = array_slice( $manual_ids, 0, $pick_max );
		}
		if ( count( $issue_ids ) > $pick_max ) {
			$issue_ids = array_slice( $issue_ids, 0, $pick_max );
		}
	}

	$band_min = isset( $row['band_min'] ) ? max( 0, min( 9999, (int) $row['band_min'] ) ) : max( 0, min( 9999, (int) ( $def['band_min'] ?? 0 ) ) );
	$band_max = isset( $row['band_max'] ) ? max( 0, min( 9999, (int) $row['band_max'] ) ) : max( 0, min( 9999, (int) ( $def['band_max'] ?? 0 ) ) );
	if ( $band_min > 0 && $band_max > 0 && $band_min > $band_max ) {
		$tmp      = $band_min;
		$band_min = $band_max;
		$band_max = $tmp;
	}

	$tag_ids = array();
	if ( ! empty( $row['tag_present'] ) || array_key_exists( 'tag', $row ) ) {
		$tag_ids = altijara_sanitize_section_tag_ids( $row['tag'] ?? array() );
	} elseif ( array_key_exists( 'tag', $def ) ) {
		$tag_ids = altijara_sanitize_section_tag_ids( $def['tag'] );
	}

	if ( array_key_exists( 'include_children', $row ) ) {
		$include_children = ! empty( $row['include_children'] ) && '0' !== (string) $row['include_children'];
	} elseif ( array_key_exists( 'include_children', $def ) ) {
		$include_children = ! empty( $def['include_children'] ) && '0' !== (string) $def['include_children'];
	} else {
		$include_children = true;
	}

	return array(
		'zone'             => $zone,
		'widget'           => $widget,
		'layout'           => altijara_widget_to_layout( $widget ),
		'label'            => $label,
		'visible'          => ! empty( $row['visible'] ),
		'category'         => $cat,
		'categories'       => $categories,
		'category_filter'  => $category_filter,
		'category_source'  => $category_source,
		'tag'              => $tag_ids,
		'include_children' => $include_children,
		'manual_posts'     => $manual_ids,
		'count'            => $count,
		'offset'           => isset( $row['offset'] ) ? max( 0, min( 100, (int) $row['offset'] ) ) : (int) ( $def['offset'] ?? 0 ),
		'orderby'          => $orderby,
		'order'            => ( isset( $row['order'] ) && 'ASC' === strtoupper( (string) $row['order'] ) ) ? 'ASC' : 'DESC',
		'variant'          => $variant,
		'source'           => $source,
		'issue_ids'        => $issue_ids,
		'view_period'      => $view_period,
		'pair_with'        => $pair_with,
		'band_min'         => $band_min,
		'band_max'         => $band_max,
		'sidebar_order'    => $sidebar_order,
		'per_load'         => isset( $row['per_load'] ) ? max( 0, min( 12, (int) $row['per_load'] ) ) : max( 0, min( 12, (int) ( $def['per_load'] ?? 0 ) ) ),
		'button_label'     => isset( $row['button_label'] ) ? sanitize_text_field( (string) $row['button_label'] ) : ( isset( $def['button_label'] ) ? sanitize_text_field( (string) $def['button_label'] ) : '' ),
		'ad_shortcode'     => isset( $row['ad_shortcode'] ) ? sanitize_text_field( (string) $row['ad_shortcode'] ) : ( isset( $def['ad_shortcode'] ) ? sanitize_text_field( (string) $def['ad_shortcode'] ) : '' ),
		'ad_image'         => isset( $row['ad_image'] ) ? esc_url_raw( (string) $row['ad_image'] ) : ( isset( $def['ad_image'] ) ? esc_url_raw( (string) $def['ad_image'] ) : '' ),
		'ad_url'           => isset( $row['ad_url'] ) ? esc_url_raw( (string) $row['ad_url'] ) : ( isset( $def['ad_url'] ) ? esc_url_raw( (string) $def['ad_url'] ) : '' ),
	);
}

/**
 * Sanitize posted section settings (supports dynamic body instances).
 *
 * @param array<string, mixed> $raw   Raw POST sections.
 * @param array<int, string>   $order Optional body order from POST.
 * @return array{sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_sanitize_sections_input( $raw, $order = array() ) {
	$defaults = altijara_default_sections();
	$out      = array();
	$hero_ids = altijara_hero_section_ids();

	if ( ! is_array( $raw ) ) {
		$raw = array();
	}

	foreach ( $hero_ids as $hid ) {
		$row        = isset( $raw[ $hid ] ) && is_array( $raw[ $hid ] ) ? $raw[ $hid ] : array();
		$out[ $hid ] = altijara_sanitize_section_row( $hid, $row, $defaults[ $hid ] ?? array() );
	}

	$body_ids = array();
	if ( is_array( $order ) && ! empty( $order ) ) {
		foreach ( $order as $oid ) {
			$oid = sanitize_key( $oid );
			if ( $oid && ! in_array( $oid, $hero_ids, true ) && isset( $raw[ $oid ] ) ) {
				$body_ids[] = $oid;
			}
		}
	}
	foreach ( array_keys( $raw ) as $rid ) {
		$rid = sanitize_key( $rid );
		if ( ! $rid || in_array( $rid, $hero_ids, true ) || in_array( $rid, $body_ids, true ) ) {
			continue;
		}
		if ( is_array( $raw[ $rid ] ) ) {
			$body_ids[] = $rid;
		}
	}

	foreach ( $body_ids as $bid ) {
		$row         = is_array( $raw[ $bid ] ) ? $raw[ $bid ] : array();
		$out[ $bid ] = altijara_sanitize_section_row( $bid, $row, $defaults[ $bid ] ?? array() );
	}

	return array(
		'sections' => $out,
		'order'    => $body_ids,
	);
}
