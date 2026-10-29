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

	// Prefer raw post meta so an intentional empty body order is not
	// rehydrated by page-meta defaults merge (empty list → preset).
	$saved = get_post_meta( $page_id, defined( 'ALTIJARA_PAGE_META_KEY' ) ? ALTIJARA_PAGE_META_KEY : '_altijara_page_meta', true );
	if ( is_array( $saved ) && isset( $saved['layout'] ) && is_array( $saved['layout'] ) ) {
		$pack = altijara_normalize_layout_pack( $saved['layout'] );
		if ( ! empty( $pack['sections'] ) || array_key_exists( 'order', $saved['layout'] ) ) {
			return $pack;
		}
	}

	$meta = function_exists( 'altijara_get_page_meta' ) ? altijara_get_page_meta( $page_id ) : array();

	// Merged meta fallback (older reads / incomplete saves).
	if ( ! empty( $meta['layout'] ) && is_array( $meta['layout'] ) ) {
		$pack = altijara_normalize_layout_pack( $meta['layout'] );
		if ( ! empty( $pack['sections'] ) ) {
			return $pack;
		}
	}

	// Migrate legacy content-only sections onto default matrix.
	$legacy = isset( $meta['sections'] ) && is_array( $meta['sections'] ) ? $meta['sections'] : array();
	return altijara_home2_pack_from_legacy_sections( $legacy, $preset );
}

/**
 * Overlay legacy Home 2 content-only rows onto a layout pack preset.
 *
 * @param array<string, array<string, mixed>>              $legacy Legacy section rows.
 * @param array{sections:array,order:string[]}|null        $preset Optional preset.
 * @return array{sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_home2_pack_from_legacy_sections( $legacy, $preset = null ) {
	if ( ! is_array( $preset ) || empty( $preset['sections'] ) ) {
		$preset = function_exists( 'altijara_layout_preset_home' )
			? altijara_layout_preset_home()
			: array( 'sections' => array(), 'order' => array() );
	}
	$legacy = is_array( $legacy ) ? $legacy : array();
	$out    = isset( $preset['sections'] ) && is_array( $preset['sections'] ) ? $preset['sections'] : array();
	foreach ( $out as $id => $def ) {
		$row = isset( $legacy[ $id ] ) && is_array( $legacy[ $id ] ) ? $legacy[ $id ] : array();
		if ( ! $row ) {
			continue;
		}
		if ( array_key_exists( 'visible', $row ) ) {
			$out[ $id ]['visible'] = ! empty( $row['visible'] );
		}
		if ( array_key_exists( 'label', $row ) ) {
			$out[ $id ]['label'] = (string) $row['label'];
		}
		if ( isset( $row['count'] ) ) {
			$out[ $id ]['count'] = max( 1, (int) $row['count'] );
		}
		$mode = isset( $row['source_mode'] ) ? sanitize_key( $row['source_mode'] ) : 'category';
		if ( 'random' === $mode ) {
			$mode           = 'category';
			$row['orderby'] = 'rand';
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
		if ( function_exists( 'altijara_normalize_section_config' ) ) {
			$out[ $id ] = altijara_normalize_section_config( $id, $out[ $id ], $def );
		}
	}

	return array(
		'sections' => $out,
		'order'    => isset( $preset['order'] ) && is_array( $preset['order'] ) ? $preset['order'] : array_keys( $out ),
	);
}

/**
 * Persist migrated Home 2 layout and drop legacy `sections` keys (one-time per page).
 *
 * @return int Number of pages updated.
 */
function altijara_migrate_home2_legacy_page_meta() {
	$key = defined( 'ALTIJARA_PAGE_META_KEY' ) ? ALTIJARA_PAGE_META_KEY : '_altijara_page_meta';
	$q   = new WP_Query(
		array(
			'post_type'      => 'page',
			'post_status'    => 'any',
			'posts_per_page' => 50,
			'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => 'home-2.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	$updated = 0;
	foreach ( $q->posts as $page_id ) {
		$page_id = absint( $page_id );
		$saved   = get_post_meta( $page_id, $key, true );
		if ( ! is_array( $saved ) || empty( $saved['sections'] ) || ! is_array( $saved['sections'] ) ) {
			continue;
		}
		$has_layout = isset( $saved['layout'] ) && is_array( $saved['layout'] )
			&& ( ! empty( $saved['layout']['sections'] ) || array_key_exists( 'order', $saved['layout'] ) );
		if ( $has_layout ) {
			unset( $saved['sections'] );
			update_post_meta( $page_id, $key, $saved );
			++$updated;
			continue;
		}
		$pack = altijara_home2_pack_from_legacy_sections( $saved['sections'] );
		update_post_meta(
			$page_id,
			$key,
			array(
				'layout' => $pack,
			)
		);
		++$updated;
	}
	return $updated;
}

/**
 * One-time admin migrate for Home 2 pages still storing legacy `sections`.
 */
function altijara_maybe_migrate_home2_legacy_page_meta() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( get_option( 'altijara_home2_legacy_migrated' ) ) {
		return;
	}
	altijara_migrate_home2_legacy_page_meta();
	update_option( 'altijara_home2_legacy_migrated', 1, false );
}
add_action( 'admin_init', 'altijara_maybe_migrate_home2_legacy_page_meta', 6 );

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

/**
 * Query fingerprint for home stream offset packing.
 *
 * Only standard posts streams participate. Manual picks, issues feeds, and
 * most-read (or other non-posts strategies) return empty.
 *
 * @param array<string, mixed> $section Section config.
 * @return string Empty when the section should not join a shared stream.
 */
function altijara_home_section_stream_fingerprint( $section ) {
	if ( ! is_array( $section ) ) {
		return '';
	}

	if ( ! empty( $section['manual_posts'] ) && is_array( $section['manual_posts'] ) ) {
		$manual = array_values( array_filter( array_map( 'absint', $section['manual_posts'] ) ) );
		if ( $manual ) {
			return '';
		}
	}

	$widget = ! empty( $section['widget'] )
		? sanitize_key( (string) $section['widget'] )
		: ( function_exists( 'altijara_layout_to_widget' )
			? altijara_layout_to_widget( $section['layout'] ?? 'news_grid' )
			: 'article_grid' );
	$def      = function_exists( 'altijara_get_widget_definition' ) ? altijara_get_widget_definition( $widget ) : null;
	$strategy = ( $def && ! empty( $def['query'] ) ) ? sanitize_key( (string) $def['query'] ) : 'posts';
	$source   = ! empty( $section['source'] ) ? sanitize_key( (string) $section['source'] ) : '';

	if ( 'issues' === $source && function_exists( 'altijara_widget_supports' ) && altijara_widget_supports( $widget, 'source' ) ) {
		return '';
	}

	// magazine_slider defaults to the issues strategy but falls through to posts when source=posts.
	if ( 'issues' === $strategy ) {
		if ( 'posts' === $source ) {
			$strategy = 'posts';
		} else {
			return '';
		}
	}

	if ( 'posts' !== $strategy ) {
		return '';
	}

	$cat_ids = function_exists( 'altijara_resolve_section_category_ids' )
		? altijara_resolve_section_category_ids( $section )
		: array();
	$cat_ids = array_values( array_filter( array_map( 'absint', (array) $cat_ids ) ) );
	sort( $cat_ids, SORT_NUMERIC );

	$filter = function_exists( 'altijara_section_category_filter' )
		? altijara_section_category_filter( $section )
		: 'include';

	$tag_ids = function_exists( 'altijara_section_tag_ids' )
		? altijara_section_tag_ids( $section )
		: array();
	$tag_ids = array_values( array_filter( array_map( 'absint', (array) $tag_ids ) ) );
	sort( $tag_ids, SORT_NUMERIC );

	$orderby = ! empty( $section['orderby'] ) ? sanitize_key( (string) $section['orderby'] ) : 'date';
	$order   = ( isset( $section['order'] ) && 'ASC' === strtoupper( (string) $section['order'] ) ) ? 'ASC' : 'DESC';
	$children = function_exists( 'altijara_section_include_children' )
		? ( altijara_section_include_children( $section ) ? '1' : '0' )
		: '1';

	return implode(
		'|',
		array(
			'posts',
			$filter,
			'c:' . implode( ',', $cat_ids ),
			't:' . implode( ',', $tag_ids ),
			'ob:' . $orderby,
			'o:' . $order,
			'ch:' . $children,
		)
	);
}

/**
 * Auto-advance offsets across same-source home modules (like category archives).
 *
 * Render-time only — does not persist offsets to page meta / theme options.
 * Lone fingerprints and non-streamable modules keep their saved offset.
 *
 * @param array{sections?:array<string,array<string,mixed>>,order?:string[]} $pack Layout pack.
 * @return array{sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_home_pack_apply_stream_offsets( $pack ) {
	$pack = is_array( $pack ) ? $pack : array();
	$sections = ( isset( $pack['sections'] ) && is_array( $pack['sections'] ) ) ? $pack['sections'] : array();
	$order    = ( isset( $pack['order'] ) && is_array( $pack['order'] ) ) ? array_values( $pack['order'] ) : array();
	if ( ! $sections ) {
		return array(
			'sections' => $sections,
			'order'    => $order,
		);
	}

	$hero_ids = function_exists( 'altijara_hero_section_ids' ) ? altijara_hero_section_ids() : array();
	$ids      = array();
	foreach ( $hero_ids as $hid ) {
		$hid = sanitize_key( (string) $hid );
		if ( $hid && isset( $sections[ $hid ] ) ) {
			$ids[] = $hid;
		}
	}
	foreach ( $order as $bid ) {
		$bid = sanitize_key( (string) $bid );
		if ( ! $bid || ! isset( $sections[ $bid ] ) || in_array( $bid, $ids, true ) ) {
			continue;
		}
		// Hero slots belong in the hero pass only.
		if ( in_array( $bid, $hero_ids, true ) ) {
			continue;
		}
		$ids[] = $bid;
	}

	$fp_counts = array();
	$meta      = array();
	foreach ( $ids as $id ) {
		$row     = is_array( $sections[ $id ] ) ? $sections[ $id ] : array();
		$visible = ! array_key_exists( 'visible', $row ) || ! empty( $row['visible'] );
		$fp      = $visible ? altijara_home_section_stream_fingerprint( $row ) : '';
		$count   = isset( $row['count'] ) ? max( 1, (int) $row['count'] ) : 1;
		$meta[ $id ] = array(
			'fp'      => $fp,
			'count'   => $count,
			'visible' => $visible,
		);
		if ( '' !== $fp ) {
			$fp_counts[ $fp ] = isset( $fp_counts[ $fp ] ) ? ( (int) $fp_counts[ $fp ] + 1 ) : 1;
		}
	}

	$cursors = array();
	foreach ( $ids as $id ) {
		$fp = $meta[ $id ]['fp'];
		if ( '' === $fp || (int) ( $fp_counts[ $fp ] ?? 0 ) < 2 ) {
			continue;
		}
		if ( ! isset( $cursors[ $fp ] ) ) {
			$cursors[ $fp ] = 0;
		}
		$sections[ $id ]['offset'] = (int) $cursors[ $fp ];
		if ( ! empty( $meta[ $id ]['visible'] ) ) {
			$cursors[ $fp ] += (int) $meta[ $id ]['count'];
		}
	}

	return array(
		'sections' => $sections,
		'order'    => $order,
	);
}

/**
 * Classic (theme-options) home pack with stream offsets applied.
 *
 * @return array{sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_get_classic_home_layout_pack() {
	$sections = function_exists( 'altijara_get_sections' ) ? altijara_get_sections() : array();
	$order    = function_exists( 'altijara_get_body_section_ids' ) ? altijara_get_body_section_ids() : array();
	return altijara_home_pack_apply_stream_offsets(
		array(
			'sections' => is_array( $sections ) ? $sections : array(),
			'order'    => is_array( $order ) ? $order : array(),
		)
	);
}
