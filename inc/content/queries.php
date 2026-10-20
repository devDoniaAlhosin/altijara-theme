<?php
/**
 * Reusable WordPress query helpers
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve a section/category key to a WP category term ID via slug (not hardcoded IDs in callers).
 *
 * Mapping lives in theme options (section manager / general settings).
 *
 * @param string $key Section key e.g. chamber, business.
 * @return int Term ID or 0.
 */
function altijara_resolve_category_id( $key ) {
	// Allow direct term IDs from the section picker.
	if ( is_numeric( $key ) ) {
		$tid = (int) $key;
		$t   = get_term( $tid, 'category' );
		return ( $t && ! is_wp_error( $t ) ) ? $tid : 0;
	}

	$key = sanitize_key( $key );
	if ( ! $key ) {
		return 0;
	}

	$map = altijara_get_option( 'category_map', array() );
	if ( ! empty( $map[ $key ] ) ) {
		$val = $map[ $key ];
		if ( is_numeric( $val ) ) {
			$tid = (int) $val;
			$t   = get_term( $tid, 'category' );
			return ( $t && ! is_wp_error( $t ) ) ? $tid : 0;
		}
		$term = get_term_by( 'slug', sanitize_title( $val ), 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			return (int) $term->term_id;
		}
	}

	// Fallback: try the key itself as a category slug.
	$term = get_term_by( 'slug', $key, 'category' );
	if ( $term && ! is_wp_error( $term ) ) {
		return (int) $term->term_id;
	}

	return 0;
}

/**
 * Alias candidates for seeding category_map from a live site.
 *
 * @return array<string, string[]>
 */
function altijara_category_map_aliases() {
	return array(
		'chamber'     => array( 'chamber', 'akhbar-al-ghurfa', 'اخبار-الغرفة', 'أخبار-الغرفة' ),
		'exhibitions' => array( 'exhibitions', 'maarid', 'المعارض' ),
		'followups'   => array( 'followups', 'mutabaat', 'متابعات' ),
		'interviews'  => array( 'interviews', 'liqaatz', 'لقاءات' ),
		'personality' => array( 'personality', 'issue-cover-personality', 'shakhsiyat', 'شخصية-الغلاف' ),
		'business'    => array( 'business', 'mal-waamal', 'mal-wa-aamal', 'مال-واعمال', 'مال-وأعمال' ),
		'logistics'   => array( 'logistics', 'لوجستيات' ),
		'industry'    => array( 'industry', 'الصناعة', 'sinaa' ),
		'energy'      => array( 'energy', 'الطاقة', 'taqa' ),
		'automotive'  => array( 'automotive', 'cars', 'سيارات' ),
		'health'      => array( 'health', 'صحة', 'siha' ),
		'tech'        => array( 'tech', 'technology', 'تقنية', 'taqniya' ),
		'tourism'     => array( 'tourism', 'سياحة-وترفيه', 'سياحة', 'siyaha' ),
		'misc'        => array( 'misc', 'منوعات', 'munawwaat' ),
	);
}

/**
 * Detect / seed category_map from live WP categories (slug + name aliases).
 *
 * @param bool $overwrite When true, replace existing map values that resolve; keep unresolved keys.
 * @return array<string, string> Map key => term ID string (preferred) or slug.
 */
function altijara_detect_category_map( $overwrite = true ) {
	$aliases = altijara_category_map_aliases();
	$current = altijara_get_option( 'category_map', array() );
	if ( ! is_array( $current ) ) {
		$current = array();
	}
	$defaults = function_exists( 'altijara_default_options' ) ? ( altijara_default_options()['category_map'] ?? array() ) : array();
	$keys     = array_unique( array_merge( array_keys( $defaults ), array_keys( $aliases ), array_keys( $current ) ) );
	$out      = $current;

	$terms = get_categories(
		array(
			'hide_empty' => false,
			'number'     => 0,
		)
	);
	if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
		return $out;
	}

	$lower = static function ( $str ) {
		$str = (string) $str;
		return function_exists( 'mb_strtolower' ) ? mb_strtolower( $str ) : strtolower( $str );
	};
	$has = static function ( $haystack, $needle ) {
		if ( function_exists( 'mb_strpos' ) ) {
			return false !== mb_strpos( $haystack, $needle );
		}
		return false !== strpos( $haystack, $needle );
	};

	$by_slug = array();
	$by_name = array();
	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		$by_slug[ $term->slug ] = (int) $term->term_id;
		$by_name[ $lower( $term->name ) ] = (int) $term->term_id;
	}

	foreach ( $keys as $key ) {
		$key = sanitize_key( $key );
		if ( ! $key ) {
			continue;
		}
		$existing = isset( $out[ $key ] ) ? $out[ $key ] : '';
		if ( ! $overwrite && $existing !== '' && $existing !== null ) {
			continue;
		}

		$found = 0;
		$try   = isset( $aliases[ $key ] ) ? $aliases[ $key ] : array( $key );
		foreach ( $try as $candidate ) {
			$slug = sanitize_title( $candidate );
			if ( $slug && isset( $by_slug[ $slug ] ) ) {
				$found = $by_slug[ $slug ];
				break;
			}
			$n = $lower( $candidate );
			if ( isset( $by_name[ $n ] ) ) {
				$found = $by_name[ $n ];
				break;
			}
			// Fuzzy: name contains alias.
			foreach ( $by_name as $name => $tid ) {
				if ( $has( $name, $n ) || $has( $n, $name ) ) {
					$found = $tid;
					break 2;
				}
			}
		}
		if ( $found ) {
			$out[ $key ] = (string) $found;
		} elseif ( ! isset( $out[ $key ] ) ) {
			$out[ $key ] = isset( $defaults[ $key ] ) ? (string) $defaults[ $key ] : $key;
		}
	}

	return $out;
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
		$args['posts_per_page'] = max( 1, min( 50, (int) $args['posts_per_page'] ) );
	}
	if ( isset( $args['offset'] ) ) {
		$args['offset'] = max( 0, (int) $args['offset'] );
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

	return altijara_query_posts(
		array(
			'cat'            => $term_id,
			'posts_per_page' => $count,
			'offset'         => $offset,
		),
		'category_' . $term_id
	);
}

/**
 * Related posts from same categories, excluding current.
 *
 * @param int $post_id Current post.
 * @param int $count   Count.
 * @return WP_Post[]
 */
function altijara_get_related_posts( $post_id, $count = 4 ) {
	$post_id = absint( $post_id );
	$cats    = wp_get_post_categories( $post_id );
	if ( empty( $cats ) ) {
		return altijara_query_posts(
			array(
				'posts_per_page' => $count,
				'post__not_in'   => array( $post_id ),
			),
			'related_fallback'
		);
	}

	return altijara_query_posts(
		array(
			'category__in'   => array_map( 'absint', $cats ),
			'posts_per_page' => $count,
			'post__not_in'   => array( $post_id ),
		),
		'related_' . $post_id
	);
}
