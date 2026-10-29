<?php
/**
 * Cached post query helpers and related posts.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cached WP_Query wrapper returning post IDs or posts.
 *
 * @param array<string, mixed> $args     WP_Query args.
 * @param string               $cache_group Cache group label.
 * @return WP_Post[]
 */
function altijara_query_posts( $args = array(), $cache_group = 'posts' ) {
	$defaults = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 6,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	$args = wp_parse_args( $args, $defaults );

	// Never allow raw unescaped tax queries from untrusted input without sanitizing common fields.
	if ( isset( $args['cat'] ) ) {
		$args['cat'] = absint( $args['cat'] );
	}
	if ( isset( $args['posts_per_page'] ) ) {
		$args['posts_per_page'] = max( 1, min( 200, (int) $args['posts_per_page'] ) );
	}
	$ctx = function_exists( 'altijara_get_layout_context' ) ? altijara_get_layout_context() : array();
	if ( ! empty( $ctx['exclude_post'] ) ) {
		$exclude = isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array();
		$exclude[] = absint( $ctx['exclude_post'] );
		$args['post__not_in'] = array_values( array_unique( array_filter( array_map( 'absint', $exclude ) ) ) );
	}
	if ( isset( $args['offset'] ) ) {
		$args['offset'] = max( 0, (int) $args['offset'] );
	}

	$post_types = isset( $args['post_type'] ) ? (array) $args['post_type'] : array( 'post' );
	if ( in_array( 'post', $post_types, true ) && empty( $args['altijara_allow_no_thumb'] ) ) {
		$args['altijara_require_thumb'] = true;
	}

	$cache_key = altijara_cache_key( $cache_group, $args );
	$cached    = altijara_cache_get( $cache_key );
	if ( is_array( $cached ) ) {
		$posts = array_filter( array_map( 'get_post', $cached ) );
		if ( count( $posts ) === count( $cached ) ) {
			return array_values( $posts );
		}
	}

	$query = new WP_Query( $args );
	$posts = $query->posts;
	$ids   = wp_list_pluck( $posts, 'ID' );
	altijara_cache_set( $cache_key, $ids );
	wp_reset_postdata();

	return $posts;
}

/**
 * Category tax query clause, with the archive child-category setting.
 *
 * @param int       $term_id          Category term ID.
 * @param bool|null $include_children Null uses the Category Archives setting.
 * @return array<string, mixed>
 */
function altijara_category_tax_clause( $term_id, $include_children = null ) {
	$term_id = absint( $term_id );
	if ( null === $include_children ) {
		$include_children = function_exists( 'altijara_archive_include_children' ) ? altijara_archive_include_children() : true;
	}
	return array(
		'taxonomy'         => 'category',
		'field'            => 'term_id',
		'terms'            => array( $term_id ),
		'include_children' => (bool) $include_children,
		'operator'         => 'IN',
	);
}

/**
 * Add a category constraint. Child categories follow the archive setting when asked.
 *
 * @param array<string, mixed> $args             Query args.
 * @param int                  $term_id          Category term ID.
 * @param bool|null            $include_children Null keeps WordPress `cat` (children included).
 * @return array<string, mixed>
 */
function altijara_args_with_category( $args, $term_id, $include_children = null ) {
	return altijara_args_with_categories( $args, array( $term_id ), $include_children, 'include' );
}

/**
 * Add one or more category constraints (Include or Exclude).
 *
 * @param array<string, mixed> $args             Query args.
 * @param int[]                $term_ids         Category term IDs.
 * @param bool|null            $include_children Null/true includes children; false is exact terms only.
 * @param string               $filter           include|exclude.
 * @return array<string, mixed>
 */
function altijara_args_with_categories( $args, $term_ids, $include_children = null, $filter = 'include' ) {
	$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $term_ids ) ) ) );
	if ( ! $ids ) {
		return $args;
	}
	$filter = ( 'exclude' === sanitize_key( (string) $filter ) ) ? 'exclude' : 'include';
	$children = ( null === $include_children ) ? true : (bool) $include_children;

	unset( $args['cat'], $args['category__in'], $args['category__not_in'] );

	// Fast path: single include with children → native `cat`.
	if ( 'include' === $filter && $children && 1 === count( $ids ) ) {
		$args['cat'] = $ids[0];
		return $args;
	}

	$tax   = ( isset( $args['tax_query'] ) && is_array( $args['tax_query'] ) ) ? $args['tax_query'] : array();
	$tax[] = array(
		'taxonomy'         => 'category',
		'field'            => 'term_id',
		'terms'            => $ids,
		'include_children' => $children,
		'operator'         => ( 'exclude' === $filter ) ? 'NOT IN' : 'IN',
	);
	$args['tax_query'] = $tax;
	return $args;
}

/**
 * Whether this query is the category page inheriting the viewed category.
 *
 * @param int $term_id Resolved category term ID.
 * @return bool
 */
function altijara_query_is_category_archive_context( $term_id ) {
	$term_id = absint( $term_id );
	$ctx     = function_exists( 'altijara_get_layout_context' ) ? altijara_get_layout_context() : array();
	if ( ! $term_id || empty( $ctx['category'] ) ) {
		return false;
	}
	$surface = isset( $ctx['surface'] ) ? (string) $ctx['surface'] : '';
	if ( ! in_array( $surface, array( 'category', 'category_archive' ), true ) ) {
		return false;
	}
	return (int) $ctx['category'] === $term_id;
}

/**
 * Latest posts for a category archive (by queried object or key).
 *
 * @param int $term_id Category term ID.
 * @param int $count   Number of posts.
 * @param int $offset  Offset.
 * @return WP_Post[]
 */
function altijara_get_category_posts( $term_id, $count = 12, $offset = 0 ) {
	$term_id = absint( $term_id );
	if ( ! $term_id ) {
		return array();
	}
	$include = function_exists( 'altijara_archive_include_children' ) ? altijara_archive_include_children() : true;

	return altijara_query_posts(
		array(
			'posts_per_page' => $count,
			'offset'         => $offset,
			'tax_query'      => array(
				altijara_category_tax_clause( $term_id, $include ),
			),
		),
		'category_v3_' . $term_id
	);
}

/**
 * Count published posts in a category (for AJAX pagination).
 *
 * @param int $term_id Category term ID.
 * @return int
 */
function altijara_count_category_posts( $term_id ) {
	$term_id = absint( $term_id );
	if ( ! $term_id ) {
		return 0;
	}
	$include   = function_exists( 'altijara_archive_include_children' ) ? altijara_archive_include_children() : true;
	$cache_key = altijara_cache_key( 'category_count_v3_' . $term_id, array( 'children' => $include ? 1 : 0 ) );
	$cached    = altijara_cache_get( $cache_key );
	if ( is_numeric( $cached ) ) {
		return (int) $cached;
	}
	$q = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 1,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => false,
			'fields'              => 'ids',
			'tax_query'           => array(
				altijara_category_tax_clause( $term_id, $include ),
			),
		)
	);
	$total = (int) $q->found_posts;
	wp_reset_postdata();
	altijara_cache_set( $cache_key, $total );
	return $total;
}

/**
 * Category page pagination asks the REST API to follow the archive child setting.
 *
 * Core `categories` queries set include_children to false. The category page
 * sends altijara_children=1 when the Category Archives option is on.
 *
 * @param array<string, mixed> $args    Query args.
 * @param WP_REST_Request      $request Request.
 * @return array<string, mixed>
 */
function altijara_rest_category_include_children( $args, $request ) {
	if ( ! $request instanceof WP_REST_Request ) {
		return $args;
	}
	$params = $request->get_query_params();
	if ( ! isset( $params['altijara_children'] ) ) {
		return $args;
	}
	$include = in_array( (string) $params['altijara_children'], array( '1', 'true' ), true );
	if ( empty( $args['tax_query'] ) || ! is_array( $args['tax_query'] ) ) {
		return $args;
	}
	foreach ( $args['tax_query'] as $index => $clause ) {
		if ( is_array( $clause ) && isset( $clause['taxonomy'] ) && 'category' === $clause['taxonomy'] ) {
			$args['tax_query'][ $index ]['include_children'] = $include;
		}
	}
	return $args;
}
add_filter( 'rest_post_query', 'altijara_rest_category_include_children', 10, 2 );

/**
 * Related articles for the bottom of a single article, using the Article source setting.
 *
 * @param int $post_id Current post.
 * @param int $count   How many to load (initial plus later “load more” clicks).
 * @return WP_Post[]
 */
function altijara_get_article_related_posts( $post_id, $count ) {
	$post_id  = absint( $post_id );
	$count    = max( 1, min( 48, (int) $count ) );
	$settings = function_exists( 'altijara_get_article_settings' ) ? altijara_get_article_settings() : array();
	$cfg      = array(
		'source'       => isset( $settings['related_source'] ) ? (string) $settings['related_source'] : 'same',
		'category_ids' => isset( $settings['related_category_ids'] ) && is_array( $settings['related_category_ids'] ) ? $settings['related_category_ids'] : array(),
	);
	$cats     = function_exists( 'altijara_resolve_article_sidebar_cats' )
		? altijara_resolve_article_sidebar_cats( $post_id, $cfg )
		: wp_get_post_categories( $post_id );
	$args     = array(
		'posts_per_page' => $count,
		'post__not_in'   => $post_id ? array( $post_id ) : array(),
	);
	if ( ! empty( $cats ) ) {
		$args['category__in'] = array_values( array_filter( array_map( 'absint', $cats ) ) );
	}
	$posts = altijara_query_posts( $args, 'article_related_' . $post_id . '_' . $count );
	if ( 'selected' === $cfg['source'] || count( $posts ) >= $count ) {
		return array_slice( $posts, 0, $count );
	}
	$more = altijara_query_posts(
		array(
			'posts_per_page' => $count,
			'post__not_in'   => array_merge( $args['post__not_in'], wp_list_pluck( $posts, 'ID' ) ),
		),
		'article_related_fill_' . $post_id
	);
	return array_slice( array_merge( $posts, $more ), 0, $count );
}

/**
 * Whether a query can return posts (articles).
 *
 * @param WP_Query $query Query.
 * @return bool
 */
function altijara_query_includes_posts( $query ) {
	$type = $query->get( 'post_type' );
	if ( empty( $type ) || 'any' === $type ) {
		if ( $query->is_singular() ) {
			return $query->is_singular( 'post' );
		}
		return $query->is_home() || $query->is_category() || $query->is_tag() || $query->is_search() || $query->is_author() || $query->is_archive() || $query->is_feed();
	}
	return in_array( 'post', (array) $type, true );
}

/**
 * Drop articles that have no featured image from public queries.
 *
 * @param array<string, string> $clauses SQL clauses.
 * @param WP_Query              $query   Query.
 * @return array<string, string>
 */
function altijara_posts_clauses_require_thumb( $clauses, $query ) {
	if ( ! $query instanceof WP_Query || ! empty( $query->get( 'altijara_allow_no_thumb' ) ) ) {
		return $clauses;
	}
	$apply = (bool) $query->get( 'altijara_require_thumb' );
	if ( ! $apply && ! is_admin() && altijara_query_includes_posts( $query ) ) {
		$apply = true;
	}
	if ( ! $apply ) {
		return $clauses;
	}
	global $wpdb;
	$video_url   = defined( 'ALTIJARA_FEATURED_VIDEO_URL' ) ? ALTIJARA_FEATURED_VIDEO_URL : '_altijara_featured_video_url';
	$video_id    = defined( 'ALTIJARA_FEATURED_VIDEO_ID' ) ? ALTIJARA_FEATURED_VIDEO_ID : '_altijara_featured_video_id';
	$jnews_video = defined( 'ALTIJARA_JNEWS_VIDEO_META' ) ? ALTIJARA_JNEWS_VIDEO_META : 'jnews_single_post-video';
	$clauses['where'] .= $wpdb->prepare(
		" AND ( {$wpdb->posts}.post_type <> 'post' OR EXISTS (
			SELECT 1 FROM {$wpdb->postmeta} altijara_thumb
			WHERE altijara_thumb.post_id = {$wpdb->posts}.ID
			AND altijara_thumb.meta_key = '_thumbnail_id'
			AND altijara_thumb.meta_value <> ''
			AND altijara_thumb.meta_value <> '0'
		) OR EXISTS (
			SELECT 1 FROM {$wpdb->postmeta} altijara_vurl
			WHERE altijara_vurl.post_id = {$wpdb->posts}.ID
			AND altijara_vurl.meta_key = %s
			AND altijara_vurl.meta_value <> ''
		) OR EXISTS (
			SELECT 1 FROM {$wpdb->postmeta} altijara_vid
			WHERE altijara_vid.post_id = {$wpdb->posts}.ID
			AND altijara_vid.meta_key = %s
			AND altijara_vid.meta_value <> ''
			AND altijara_vid.meta_value <> '0'
		) OR EXISTS (
			SELECT 1 FROM {$wpdb->postmeta} altijara_jv
			WHERE altijara_jv.post_id = {$wpdb->posts}.ID
			AND altijara_jv.meta_key = %s
			AND altijara_jv.meta_value <> ''
		) )",
		$video_url,
		$video_id,
		$jnews_video
	);
	return $clauses;
}
add_filter( 'posts_clauses', 'altijara_posts_clauses_require_thumb', 20, 2 );
