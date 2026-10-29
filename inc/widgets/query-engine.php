<?php
/**
 * Widget Query Engine — content strategies per widget type.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query strategy id → callable registry (filterable).
 *
 * @return array<string, callable>
 */
function altijara_query_strategy_registry() {
	$registry = array(
		'posts'     => 'altijara_query_strategy_posts',
		'issues'    => 'altijara_query_strategy_issues',
		'most_read' => 'altijara_query_strategy_most_read',
	);

	/**
	 * Filter widget query strategy callbacks.
	 *
	 * @param array<string, callable> $registry Strategy id => callback( $section_key, $section, $override ).
	 */
	return apply_filters( 'altijara_query_strategy_registry', $registry );
}

/**
 * Resolve posts for a homepage section / widget instance.
 *
 * @param string               $section_key Section ID.
 * @param array<string, mixed> $override    Optional WP_Query overrides.
 * @return WP_Post[]
 */
function altijara_get_section_posts( $section_key, $override = array() ) {
	$section = altijara_get_section_config( $section_key );
	if ( empty( $section['visible'] ) ) {
		return array();
	}

	$widget = ! empty( $section['widget'] ) ? sanitize_key( $section['widget'] ) : altijara_layout_to_widget( $section['layout'] ?? 'news_grid' );
	$def    = altijara_get_widget_definition( $widget );
	$strategy = ( $def && ! empty( $def['query'] ) ) ? $def['query'] : 'posts';
	if ( ! empty( $section['source'] ) && 'issues' === $section['source'] && function_exists( 'altijara_widget_supports' ) && altijara_widget_supports( $widget, 'source' ) ) {
		$strategy = 'issues';
	}

	$skip_dedupe = ! empty( $override['altijara_skip_chamber_dedupe'] );
	unset( $override['altijara_skip_chamber_dedupe'] );

	$registry = altijara_query_strategy_registry();
	$callback = isset( $registry[ $strategy ] ) ? $registry[ $strategy ] : ( $registry['posts'] ?? null );
	$posts    = ( $callback && is_callable( $callback ) )
		? call_user_func( $callback, $section_key, $section, $override )
		: altijara_query_strategy_posts( $section_key, $section, $override );

	if ( 'chamber_column' === $widget && ! $skip_dedupe ) {
		$posts = altijara_dedupe_chamber_column_posts( $section_key, $section, $posts );
	}

	return is_array( $posts ) ? $posts : array();
}

/**
 * Sibling hero section keys whose posts the chamber column should exclude.
 *
 * @param string $section_key Chamber section id.
 * @return string[]
 */
function altijara_chamber_exclude_section_keys( $section_key = '' ) {
	$keys = array( 'hero_magazine', 'hero_interviews', 'hero_personality', 'hero_logistics' );

	/**
	 * Filter section keys excluded from the chamber column feed.
	 *
	 * @param string[] $keys        Section keys.
	 * @param string   $section_key Chamber section id.
	 */
	$keys = apply_filters( 'altijara_chamber_exclude_section_keys', $keys, $section_key );
	return array_values( array_filter( array_map( 'sanitize_key', (array) $keys ) ) );
}

/**
 * Re-query / filter chamber posts so they do not overlap sibling hero columns.
 *
 * @param string               $section_key Section ID.
 * @param array<string, mixed> $config      Section config.
 * @param WP_Post[]            $posts       Initial posts.
 * @return WP_Post[]
 */
function altijara_dedupe_chamber_column_posts( $section_key, $config, $posts ) {
	$posts   = is_array( $posts ) ? $posts : array();
	$blocked = array();
	foreach ( altijara_chamber_exclude_section_keys( $section_key ) as $other_key ) {
		if ( $other_key === $section_key || ! altijara_section_is_visible( $other_key ) ) {
			continue;
		}
		foreach ( altijara_get_section_posts( $other_key, array( 'altijara_skip_chamber_dedupe' => true ) ) as $other_post ) {
			if ( $other_post instanceof WP_Post ) {
				$blocked[ (int) $other_post->ID ] = true;
			}
		}
	}

	if ( $blocked ) {
		$need  = ! empty( $config['count'] ) ? max( 1, (int) $config['count'] ) : 7;
		$posts = altijara_get_section_posts(
			$section_key,
			array(
				'posts_per_page'             => min( 50, $need + count( $blocked ) ),
				'post__not_in'               => array_keys( $blocked ),
				'altijara_skip_chamber_dedupe' => true,
			)
		);
	}

	$unique = array();
	$seen   = array();
	foreach ( $posts as $item ) {
		if ( ! $item instanceof WP_Post || isset( $seen[ $item->ID ] ) || isset( $blocked[ (int) $item->ID ] ) ) {
			continue;
		}
		$seen[ $item->ID ] = true;
		$unique[]          = $item;
	}
	$need = ! empty( $config['count'] ) ? max( 1, (int) $config['count'] ) : 7;
	return array_slice( $unique, 0, $need );
}

/**
 * Resolve related-articles view data for the article_related widget.
 *
 * @param string               $section_key Section ID.
 * @param array<string, mixed> $config      Section config.
 * @param WP_Post[]            $fallback    Posts already resolved by the engine.
 * @return array{posts:WP_Post[],initial:int,per_load:int,button_label:string,heading:string}
 */
function altijara_resolve_related_section_data( $section_key, $config = array(), $fallback = array() ) {
	$config  = is_array( $config ) ? $config : array();
	$ctx     = function_exists( 'altijara_get_layout_context' ) ? altijara_get_layout_context() : array();
	$post_id = ! empty( $ctx['exclude_post'] ) ? absint( $ctx['exclude_post'] ) : 0;

	$settings = function_exists( 'altijara_get_article_settings' ) ? altijara_get_article_settings() : array();
	$initial  = isset( $config['count'] ) ? max( 1, min( 12, (int) $config['count'] ) ) : 2;
	$per_load = isset( $config['per_load'] ) ? (int) $config['per_load'] : 0;
	if ( $per_load < 1 ) {
		// Legacy theme-option fallback, then a sensible default.
		$per_load = isset( $settings['related_per_load'] ) ? (int) $settings['related_per_load'] : 2;
	}
	$per_load = max( 1, min( 12, $per_load ) );
	$pool     = min( 48, $initial + ( $per_load * 6 ) );

	$button_label = isset( $config['button_label'] ) ? trim( (string) $config['button_label'] ) : '';
	if ( '' === $button_label && ! empty( $settings['related_more_label'] ) ) {
		$button_label = trim( (string) $settings['related_more_label'] );
	}
	if ( '' === $button_label ) {
		$button_label = __( 'تحميل المزيد', 'altijara' );
	}

	$has_cats = function_exists( 'altijara_section_category_ids' )
		? ! empty( altijara_section_category_ids( $config ) )
		: ! empty( $config['category'] );
	$cat_source = function_exists( 'altijara_section_category_source' )
		? altijara_section_category_source( $config )
		: sanitize_key( (string) ( $config['category_source'] ?? '' ) );
	if ( ! in_array( $cat_source, array( 'same', 'selected' ), true ) ) {
		$cat_source = $has_cats ? 'selected' : 'same';
	}
	$explicit = ! empty( $config['manual_posts'] ) || ! empty( $config['tag'] ) || ( 'selected' === $cat_source );
	if ( $explicit ) {
		$posts = altijara_get_section_posts( $section_key, array( 'posts_per_page' => $pool ) );
	} elseif ( $post_id && function_exists( 'altijara_get_article_related_posts' ) ) {
		$posts = altijara_get_article_related_posts( $post_id, $pool );
	} else {
		$posts = is_array( $fallback ) ? $fallback : array();
		if ( count( $posts ) < $pool && function_exists( 'altijara_get_article_related_posts' ) && $post_id ) {
			$posts = altijara_get_article_related_posts( $post_id, $pool );
		}
	}

	$heading = function_exists( 'altijara_section_heading' )
		? altijara_section_heading( $config, __( 'اقرأ ايضاً', 'altijara' ) )
		: ( isset( $config['label'] ) ? (string) $config['label'] : __( 'اقرأ ايضاً', 'altijara' ) );

	return array(
		'posts'         => is_array( $posts ) ? $posts : array(),
		'initial'       => $initial,
		'per_load'      => $per_load,
		'button_label'  => $button_label,
		'heading'       => $heading,
	);
}

/**
 * Standard posts (category / tag / manual).
 *
 * @param string               $section_key Section ID.
 * @param array<string, mixed> $section     Config.
 * @param array<string, mixed> $override    Overrides.
 * @return WP_Post[]
 */
function altijara_query_strategy_posts( $section_key, $section, $override = array() ) {
	$count   = isset( $override['posts_per_page'] ) ? (int) $override['posts_per_page'] : (int) $section['count'];
	$offset  = isset( $override['offset'] ) ? (int) $override['offset'] : (int) $section['offset'];
	$orderby = ! empty( $section['orderby'] ) ? sanitize_key( $section['orderby'] ) : 'date';
	$order   = ( isset( $section['order'] ) && 'ASC' === strtoupper( (string) $section['order'] ) ) ? 'ASC' : 'DESC';

	$args = array(
		'posts_per_page' => $count,
		'offset'         => $offset,
		'orderby'        => $orderby,
		'order'          => $order,
	);

	if ( ! empty( $section['manual_posts'] ) && is_array( $section['manual_posts'] ) ) {
		$ids = array_values( array_filter( array_map( 'absint', $section['manual_posts'] ) ) );
		if ( $ids ) {
			if ( $offset > 0 ) {
				$ids = array_slice( $ids, $offset );
			}
			if ( ! $ids ) {
				return array();
			}
			$args['post__in']       = $ids;
			$args['orderby']        = 'post__in';
			$args['posts_per_page'] = max( 1, $count );
			unset( $args['offset'] );
			return altijara_query_posts( array_merge( $args, $override ), 'section_' . $section_key );
		}
	}

	$cat_ids = altijara_resolve_section_category_ids( $section, $section_key );
	if ( $cat_ids ) {
		$children = function_exists( 'altijara_section_include_children' )
			? altijara_section_include_children( $section )
			: true;
		$filter   = function_exists( 'altijara_section_category_filter' )
			? altijara_section_category_filter( $section )
			: 'include';
		$args     = function_exists( 'altijara_args_with_categories' )
			? altijara_args_with_categories( $args, $cat_ids, $children, $filter )
			: array_merge( $args, array( 'cat' => (int) $cat_ids[0] ) );
	}

	$tag_ids = function_exists( 'altijara_section_tag_ids' ) ? altijara_section_tag_ids( $section ) : array();
	if ( count( $tag_ids ) > 1 ) {
		$args['tag__in'] = $tag_ids;
	} elseif ( 1 === count( $tag_ids ) ) {
		$args['tag_id'] = $tag_ids[0];
	}

	return altijara_query_posts( array_merge( $args, $override ), 'section_' . $section_key );
}

/**
 * Magazine issues CPT, with optional manual issue IDs; falls back to posts.
 *
 * @param string               $section_key Section ID.
 * @param array<string, mixed> $section     Config.
 * @param array<string, mixed> $override    Overrides.
 * @return WP_Post[]
 */
function altijara_query_strategy_issues( $section_key, $section, $override = array() ) {
	$count  = isset( $override['posts_per_page'] ) ? (int) $override['posts_per_page'] : (int) $section['count'];
	$source = ! empty( $section['source'] ) ? sanitize_key( $section['source'] ) : 'issues';
	$widget = ! empty( $section['widget'] ) ? sanitize_key( (string) $section['widget'] ) : '';

	$issue_ids = array();
	if ( ! empty( $section['issue_ids'] ) && is_array( $section['issue_ids'] ) ) {
		$issue_ids = array_values( array_filter( array_map( 'absint', $section['issue_ids'] ) ) );
	} elseif ( ! empty( $section['manual_posts'] ) && is_array( $section['manual_posts'] ) && 'issues' === $source ) {
		$issue_ids = array_values( array_filter( array_map( 'absint', $section['manual_posts'] ) ) );
	}

	if ( 'posts' === $source ) {
		return altijara_query_strategy_posts( $section_key, $section, $override );
	}

	$band_min = isset( $section['band_min'] ) ? max( 0, min( 9999, (int) $section['band_min'] ) ) : 0;
	$band_max = isset( $section['band_max'] ) ? max( 0, min( 9999, (int) $section['band_max'] ) ) : 0;
	if ( $band_min > 0 && $band_max > 0 && $band_min > $band_max ) {
		$tmp      = $band_min;
		$band_min = $band_max;
		$band_max = $tmp;
	}
	$apply_band = ! $issue_ids && ( $band_min > 0 || $band_max > 0 );

	$orderby = ! empty( $section['orderby'] ) ? sanitize_key( (string) $section['orderby'] ) : 'date';
	$order   = ( isset( $section['order'] ) && 'ASC' === strtoupper( (string) $section['order'] ) ) ? 'ASC' : 'DESC';
	// Lead / carousel with no pinned IDs always follow issue number so “latest” = highest #.
	if ( ! $issue_ids && 'rand' !== $orderby && in_array( $widget, array( 'issues_lead', 'issues_carousel' ), true ) ) {
		$orderby = 'issue_number';
	}
	$by_number = ( 'issue_number' === $orderby );

	$offset = isset( $override['offset'] ) ? (int) $override['offset'] : (int) ( $section['offset'] ?? 0 );
	$offset = max( 0, $offset );

	$args = array(
		'post_type'      => 'issue',
		'post_status'    => 'publish',
		'posts_per_page' => ( $apply_band || $by_number ) ? max( $count + $offset, 80 ) : max( 1, $count ),
		'orderby'        => $by_number ? 'date' : $orderby,
		'order'          => $by_number ? 'DESC' : $order,
	);

	if ( $issue_ids ) {
		if ( $count > 0 && count( $issue_ids ) > $count ) {
			$issue_ids = array_slice( $issue_ids, 0, $count );
		}
		$args['post__in']       = $issue_ids;
		$args['orderby']        = 'post__in';
		$args['posts_per_page'] = max( 1, count( $issue_ids ) );
	} elseif ( ! $apply_band && ! $by_number && $offset > 0 ) {
		$args['offset'] = $offset;
	}

	$issues = altijara_query_posts( array_merge( $args, $override ), 'issues_' . $section_key );

	if ( $issue_ids ) {
		return is_array( $issues ) ? $issues : array();
	}

	if ( $by_number && function_exists( 'altijara_sort_issues_by_number' ) ) {
		$issues = altijara_sort_issues_by_number( $issues, $order );
	}

	if ( $apply_band && function_exists( 'altijara_issue_number_int' ) ) {
		$filtered = array();
		foreach ( $issues as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}
			$num = altijara_issue_number_int( $post );
			if ( $num < 1 ) {
				continue;
			}
			if ( $band_min > 0 && $num < $band_min ) {
				continue;
			}
			if ( $band_max > 0 && $num > $band_max ) {
				continue;
			}
			$filtered[] = $post;
		}
		$issues = $filtered;
	}

	if ( ( $apply_band || $by_number ) && $offset > 0 ) {
		$issues = array_slice( $issues, $offset );
	}
	if ( ( $apply_band || $by_number ) && $count > 0 && count( $issues ) > $count ) {
		$issues = array_slice( $issues, 0, $count );
	}

	if ( ! empty( $issues ) || 'issues' === $source ) {
		return is_array( $issues ) ? $issues : array();
	}

	return altijara_query_strategy_posts( $section_key, $section, $override );
}

/**
 * Most-read: view meta when available, else comment_count, else recent.
 *
 * @param string               $section_key Section ID.
 * @param array<string, mixed> $section     Config.
 * @param array<string, mixed> $override    Overrides.
 * @return WP_Post[]
 */
function altijara_query_strategy_most_read( $section_key, $section, $override = array() ) {
	if ( ! empty( $section['manual_posts'] ) && is_array( $section['manual_posts'] ) ) {
		$ids   = array_values( array_filter( array_map( 'absint', $section['manual_posts'] ) ) );
		$count = isset( $override['posts_per_page'] ) ? (int) $override['posts_per_page'] : (int) $section['count'];
		if ( $count > 0 && count( $ids ) > $count ) {
			$ids = array_slice( $ids, 0, $count );
		}
		if ( $ids ) {
			return altijara_query_posts(
				array_merge(
					array(
						'post__in'       => $ids,
						'orderby'        => 'post__in',
						'posts_per_page' => count( $ids ),
					),
					$override
				),
				'most_read_manual_' . $section_key
			);
		}
	}

	$count  = isset( $override['posts_per_page'] ) ? (int) $override['posts_per_page'] : (int) $section['count'];
	$period = ! empty( $section['view_period'] ) ? sanitize_key( $section['view_period'] ) : 'all';
	$after  = altijara_view_period_after_mysql( $period );

	$args = array(
		'posts_per_page' => max( 1, $count ),
		'orderby'        => 'comment_count',
		'order'          => 'DESC',
	);

	$cat_ids = altijara_resolve_section_category_ids( $section, $section_key );
	if ( $cat_ids ) {
		$children = function_exists( 'altijara_section_include_children' )
			? altijara_section_include_children( $section )
			: true;
		$filter   = function_exists( 'altijara_section_category_filter' )
			? altijara_section_category_filter( $section )
			: 'include';
		$args     = function_exists( 'altijara_args_with_categories' )
			? altijara_args_with_categories( $args, $cat_ids, $children, $filter )
			: array_merge( $args, array( 'cat' => (int) $cat_ids[0] ) );
	}
	$tag_ids = function_exists( 'altijara_section_tag_ids' ) ? altijara_section_tag_ids( $section ) : array();
	if ( count( $tag_ids ) > 1 ) {
		$args['tag__in'] = $tag_ids;
	} elseif ( 1 === count( $tag_ids ) ) {
		$args['tag_id'] = $tag_ids[0];
	}
	if ( $after ) {
		$args['date_query'] = array(
			array(
				'after'     => $after,
				'inclusive' => true,
			),
		);
	}

	$offset = isset( $override['offset'] ) ? (int) $override['offset'] : (int) ( $section['offset'] ?? 0 );
	if ( $offset > 0 ) {
		$args['offset'] = $offset;
	}

	// Prefer common view-count meta keys when present in DB.
	$view_meta = apply_filters( 'altijara_most_read_meta_key', 'post_views_count' );
	$meta_args = $args;
	$meta_args['meta_key'] = $view_meta; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	$meta_args['orderby']  = 'meta_value_num';

	$posts = altijara_query_posts( array_merge( $meta_args, $override ), 'most_read_meta_' . $section_key . '_' . $period );
	if ( ! empty( $posts ) ) {
		return $posts;
	}

	$posts = altijara_query_posts( array_merge( $args, $override ), 'most_read_comments_' . $section_key . '_' . $period );
	if ( ! empty( $posts ) ) {
		return $posts;
	}

	$args['orderby'] = 'date';
	return altijara_query_posts( array_merge( $args, $override ), 'most_read_recent_' . $section_key . '_' . $period );
}

/**
 * Resolve category term IDs for a section query.
 *
 * Explicit module categories win. For Include with an empty selection, inherit
 * layout context (archive/article). Exclude with an empty selection means no
 * category constraint. Never silently map section_key → category_map.
 *
 * @param array<string, mixed> $section     Config.
 * @param string               $section_key Unused (kept for call-site compatibility).
 * @return int[]
 */
function altijara_resolve_section_category_ids( $section, $section_key = '' ) {
	unset( $section_key );
	$section = is_array( $section ) ? $section : array();
	$filter  = function_exists( 'altijara_section_category_filter' )
		? altijara_section_category_filter( $section )
		: 'include';
	$cat_source = function_exists( 'altijara_section_category_source' )
		? altijara_section_category_source( $section )
		: sanitize_key( (string) ( $section['category_source'] ?? '' ) );

	// Same as current article: all of the post’s categories.
	if ( 'same' === $cat_source ) {
		$ctx     = function_exists( 'altijara_get_layout_context' ) ? altijara_get_layout_context() : array();
		$post_id = ! empty( $ctx['exclude_post'] ) ? absint( $ctx['exclude_post'] ) : 0;
		if ( $post_id ) {
			$cats = wp_get_post_categories( $post_id );
			return array_values( array_filter( array_map( 'absint', (array) $cats ) ) );
		}
		if ( ! empty( $ctx['category'] ) ) {
			$id = absint( $ctx['category'] );
			return $id ? array( $id ) : array();
		}
		return array();
	}

	$ids = function_exists( 'altijara_section_category_ids' )
		? altijara_section_category_ids( $section )
		: array();
	if ( ! $ids && ! empty( $section['category'] ) ) {
		// Legacy single field when categories[] not populated yet.
		if ( is_numeric( $section['category'] ) ) {
			$id = absint( $section['category'] );
			$ids = $id ? array( $id ) : array();
		} else {
			$id = function_exists( 'altijara_resolve_category_id' )
				? absint( altijara_resolve_category_id( (string) $section['category'] ) )
				: 0;
			$ids = $id ? array( $id ) : array();
		}
	}
	if ( $ids ) {
		return $ids;
	}

	// Exclude + empty selection → no category clause.
	if ( 'exclude' === $filter ) {
		return array();
	}

	// Selected + Include + empty: do not silently inherit the article category.
	if ( 'selected' === $cat_source ) {
		return array();
	}

	// Include + empty: inherit archive/article layout context.
	$ctx = function_exists( 'altijara_get_layout_context' ) ? altijara_get_layout_context() : array();
	if ( ! empty( $ctx['category'] ) ) {
		$id = absint( $ctx['category'] );
		return $id ? array( $id ) : array();
	}

	return array();
}

/**
 * Primary category ID for labels / single-term helpers.
 *
 * For Exclude filters, returns layout context (if any) so card labels are not the excluded term.
 *
 * @param array<string, mixed> $section     Config.
 * @param string               $section_key Unused (kept for call-site compatibility).
 * @return int
 */
function altijara_resolve_section_category_id( $section, $section_key = '' ) {
	$section = is_array( $section ) ? $section : array();
	$filter  = function_exists( 'altijara_section_category_filter' )
		? altijara_section_category_filter( $section )
		: 'include';
	if ( 'exclude' === $filter ) {
		$ctx = function_exists( 'altijara_get_layout_context' ) ? altijara_get_layout_context() : array();
		return ! empty( $ctx['category'] ) ? absint( $ctx['category'] ) : 0;
	}
	$ids = altijara_resolve_section_category_ids( $section, $section_key );
	return $ids ? (int) $ids[0] : 0;
}

/**
 * @param string $period day|week|month|year|all
 * @return string|null MySQL datetime or null for all-time.
 */
function altijara_view_period_after_mysql( $period ) {
	switch ( $period ) {
		case 'day':
			return gmdate( 'Y-m-d H:i:s', strtotime( '-1 day' ) );
		case 'week':
			return gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) );
		case 'month':
			return gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) );
		case 'year':
			return gmdate( 'Y-m-d H:i:s', strtotime( '-365 days' ) );
		case 'all':
		default:
			return null;
	}
}
