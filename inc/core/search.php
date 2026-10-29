<?php
/**
 * Search query hooks and REST endpoint.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Align search with the news list, and match posts and issues by title or ID.
 *
 * @param WP_Query $query Query.
 */
function altijara_pre_get_posts_search( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}
	$counts   = function_exists( 'altijara_get_archive_counts' )
		? altijara_get_archive_counts()
		: array( 'news_size' => 10 );
	$per_page = max( 1, (int) ( $counts['news_size'] ?? 10 ) );
	$query->set( 'posts_per_page', $per_page );
	$query->set( 'post_type', array( 'post', 'issue' ) );
	$query->set( 'altijara_title_id_search', true );
}
add_action( 'pre_get_posts', 'altijara_pre_get_posts_search' );

/**
 * Tag archives use the same page size as the news list.
 *
 * @param WP_Query $query Query.
 */
function altijara_pre_get_posts_tag( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_tag() ) {
		return;
	}
	$counts   = function_exists( 'altijara_get_archive_counts' )
		? altijara_get_archive_counts()
		: array( 'news_size' => 10 );
	$per_page = max( 1, (int) ( $counts['news_size'] ?? 10 ) );
	$query->set( 'posts_per_page', $per_page );
	$query->set( 'post_type', 'post' );
}
add_action( 'pre_get_posts', 'altijara_pre_get_posts_tag' );

/**
 * Normalize a search string, including Arabic-Indic digits.
 *
 * @param string $term Raw query.
 * @return string
 */
function altijara_normalize_search_term( $term ) {
	$term = trim( wp_strip_all_tags( (string) $term ) );
	return strtr(
		$term,
		array(
			'٠' => '0',
			'١' => '1',
			'٢' => '2',
			'٣' => '3',
			'٤' => '4',
			'٥' => '5',
			'٦' => '6',
			'٧' => '7',
			'٨' => '8',
			'٩' => '9',
		)
	);
}

/**
 * Search post title, issue title, slug, or a numeric post/issue ID.
 *
 * @param string   $search Existing search SQL.
 * @param WP_Query $query  Query.
 * @return string
 */
function altijara_posts_search_title_or_id( $search, $query ) {
	if ( ! $query instanceof WP_Query || ! $query->get( 'altijara_title_id_search' ) ) {
		return $search;
	}
	$term = altijara_normalize_search_term( (string) $query->get( 's' ) );
	if ( '' === $term ) {
		return $search;
	}
	global $wpdb;
	$like = '%' . $wpdb->esc_like( $term ) . '%';
	if ( ctype_digit( $term ) ) {
		return $wpdb->prepare(
			" AND ({$wpdb->posts}.ID = %d OR {$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_name LIKE %s) ",
			(int) $term,
			$like,
			$like
		);
	}
	return $wpdb->prepare(
		" AND ({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_name LIKE %s) ",
		$like,
		$like
	);
}
add_filter( 'posts_search', 'altijara_posts_search_title_or_id', 20, 2 );

/**
 * Public search used by the archive page and the header.
 */
function altijara_register_search_route() {
	register_rest_route(
		'altijara/v1',
		'/search',
		array(
			'methods'             => 'GET',
			'permission_callback' => '__return_true',
			'callback'            => 'altijara_rest_search',
			'args'                => array(
				'search'   => array(
					'type'              => 'string',
					'required'          => true,
					'sanitize_callback' => 'altijara_normalize_search_term',
				),
				'per_page' => array(
					'type'              => 'integer',
					'default'           => 10,
					'sanitize_callback' => 'absint',
				),
				'page'     => array(
					'type'              => 'integer',
					'default'           => 1,
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'altijara_register_search_route' );

/**
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function altijara_rest_search( $request ) {
	$term     = altijara_normalize_search_term( (string) $request->get_param( 'search' ) );
	$per_page = min( 50, max( 1, (int) $request->get_param( 'per_page' ) ) );
	$page     = max( 1, (int) $request->get_param( 'page' ) );
	if ( '' === $term ) {
		$response = rest_ensure_response( array() );
		$response->header( 'X-WP-Total', '0' );
		$response->header( 'X-WP-TotalPages', '1' );
		return $response;
	}

	$query = new WP_Query(
		array(
			's'                         => $term,
			'post_type'                 => array( 'post', 'issue' ),
			'post_status'               => 'publish',
			'posts_per_page'            => $per_page,
			'paged'                     => $page,
			'altijara_title_id_search'  => true,
			'ignore_sticky_posts'       => true,
		)
	);

	$charset = get_bloginfo( 'charset' );
	$items   = array();
	foreach ( $query->posts as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}
		$is_issue = ( 'issue' === $post->post_type );
		$excerpt  = $post->post_excerpt ? $post->post_excerpt : $post->post_content;
		$title    = html_entity_decode( get_the_title( $post ), ENT_QUOTES | ENT_HTML5, $charset );
		$category = $is_issue
			? __( 'أعداد المجلة', 'altijara' )
			: ( function_exists( 'altijara_get_primary_category_name' ) ? altijara_get_primary_category_name( $post->ID ) : '' );
		$items[]  = array(
			'id'       => (int) $post->ID,
			'title'    => $title,
			'link'     => get_permalink( $post ),
			'excerpt'  => function_exists( 'altijara_trim_at_word' ) ? altijara_trim_at_word( $excerpt, 180 ) : wp_trim_words( wp_strip_all_tags( $excerpt ), 24 ),
			'img'      => function_exists( 'altijara_get_thumb_url' ) ? altijara_get_thumb_url( $post->ID, 'medium' ) : '',
			'date'     => get_post_time( 'c', true, $post ),
			'dateText' => function_exists( 'altijara_format_date_ar' ) ? altijara_format_date_ar( $post->ID ) : get_the_date( '', $post ),
			'category' => html_entity_decode( (string) $category, ENT_QUOTES | ENT_HTML5, $charset ),
			'type'     => $post->post_type,
		);
	}

	$response = rest_ensure_response( $items );
	$response->header( 'X-WP-Total', (string) (int) $query->found_posts );
	$response->header( 'X-WP-TotalPages', (string) max( 1, (int) $query->max_num_pages ) );
	return $response;
}
