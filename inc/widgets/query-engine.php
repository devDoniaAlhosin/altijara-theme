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

	switch ( $strategy ) {
		case 'issues':
			return altijara_query_strategy_issues( $section_key, $section, $override );
		case 'most_read':
			return altijara_query_strategy_most_read( $section_key, $section, $override );
		case 'posts':
		default:
			return altijara_query_strategy_posts( $section_key, $section, $override );
	}
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
			$args['post__in'] = $ids;
			$args['orderby']  = 'post__in';
			unset( $args['offset'] );
			return altijara_query_posts( array_merge( $args, $override ), 'section_' . $section_key );
		}
	}

	$cat_id = altijara_resolve_section_category_id( $section, $section_key );
	if ( $cat_id ) {
		$args['cat'] = $cat_id;
	}

	if ( ! empty( $section['tag'] ) ) {
		$args['tag_id'] = absint( $section['tag'] );
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

	$issue_ids = array();
	if ( ! empty( $section['issue_ids'] ) && is_array( $section['issue_ids'] ) ) {
		$issue_ids = array_values( array_filter( array_map( 'absint', $section['issue_ids'] ) ) );
	} elseif ( ! empty( $section['manual_posts'] ) && is_array( $section['manual_posts'] ) && 'issues' === $source ) {
		$issue_ids = array_values( array_filter( array_map( 'absint', $section['manual_posts'] ) ) );
	}

	if ( 'posts' === $source ) {
		return altijara_query_strategy_posts( $section_key, $section, $override );
	}

	$args = array(
		'post_type'      => 'issue',
		'post_status'    => 'publish',
		'posts_per_page' => max( 1, $count ),
		'orderby'        => ! empty( $section['orderby'] ) ? sanitize_key( $section['orderby'] ) : 'date',
		'order'          => ( isset( $section['order'] ) && 'ASC' === strtoupper( (string) $section['order'] ) ) ? 'ASC' : 'DESC',
	);

	if ( $issue_ids ) {
		$args['post__in'] = $issue_ids;
		$args['orderby']  = 'post__in';
	} elseif ( ! empty( $section['offset'] ) ) {
		$args['offset'] = (int) $section['offset'];
	}

	$issues = altijara_query_posts( array_merge( $args, $override ), 'issues_' . $section_key );
	if ( ! empty( $issues ) ) {
		return $issues;
	}

	// Fallback to personality/category posts if no issues published.
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
		$ids = array_values( array_filter( array_map( 'absint', $section['manual_posts'] ) ) );
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

	$cat_id = altijara_resolve_section_category_id( $section, $section_key );
	if ( $cat_id ) {
		$args['cat'] = $cat_id;
	}
	if ( ! empty( $section['tag'] ) ) {
		$args['tag_id'] = absint( $section['tag'] );
	}
	if ( $after ) {
		$args['date_query'] = array(
			array(
				'after'     => $after,
				'inclusive' => true,
			),
		);
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
 * @param array<string, mixed> $section     Config.
 * @param string               $section_key Fallback key.
 * @return int
 */
function altijara_resolve_section_category_id( $section, $section_key ) {
	if ( ! empty( $section['category'] ) ) {
		if ( is_numeric( $section['category'] ) ) {
			return (int) $section['category'];
		}
		return altijara_resolve_category_id( (string) $section['category'] );
	}
	// Category archive layout context: inherit queried term when row has no category.
	$ctx = function_exists( 'altijara_get_layout_context' ) ? altijara_get_layout_context() : array();
	if ( ! empty( $ctx['category'] ) ) {
		return absint( $ctx['category'] );
	}
	return altijara_resolve_category_id( $section_key );
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
