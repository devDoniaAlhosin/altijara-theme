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
			if ( $t && ! is_wp_error( $t ) ) {
				return $tid;
			}
		} else {
			$term = get_term_by( 'slug', sanitize_title( (string) $val ), 'category' );
			if ( $term && ! is_wp_error( $term ) ) {
				return (int) $term->term_id;
			}
		}
	}

	// Live-site aliases (e.g. chamber → the-chamber-news) when map is stale/empty.
	$aliases = function_exists( 'altijara_category_map_aliases' ) ? altijara_category_map_aliases() : array();
	if ( ! empty( $aliases[ $key ] ) && is_array( $aliases[ $key ] ) ) {
		foreach ( $aliases[ $key ] as $candidate ) {
			$slug = sanitize_title( (string) $candidate );
			if ( ! $slug ) {
				continue;
			}
			$term = get_term_by( 'slug', $slug, 'category' );
			if ( $term && ! is_wp_error( $term ) ) {
				return (int) $term->term_id;
			}
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
 * Reverse-resolve a WP category term to a theme section key (via category_map / aliases).
 *
 * @param int|WP_Term|null $term Term ID or object.
 * @return string Theme key or empty string.
 */
function altijara_resolve_category_key( $term ) {
	if ( is_numeric( $term ) ) {
		$term = get_term( (int) $term, 'category' );
	}
	if ( ! $term instanceof WP_Term || is_wp_error( $term ) ) {
		return '';
	}

	$map = altijara_get_option( 'category_map', array() );
	if ( ! is_array( $map ) ) {
		$map = array();
	}

	foreach ( $map as $key => $val ) {
		$key = sanitize_key( $key );
		if ( ! $key ) {
			continue;
		}
		if ( is_numeric( $val ) && (int) $val === (int) $term->term_id ) {
			return $key;
		}
		if ( ! is_numeric( $val ) && sanitize_title( (string) $val ) === $term->slug ) {
			return $key;
		}
	}

	$aliases = function_exists( 'altijara_category_map_aliases' ) ? altijara_category_map_aliases() : array();
	foreach ( $aliases as $key => $cands ) {
		foreach ( (array) $cands as $cand ) {
			if ( sanitize_title( (string) $cand ) === $term->slug ) {
				return sanitize_key( $key );
			}
		}
	}

	return '';
}

/**
 * Alias candidates for seeding category_map from a live site.
 *
 * @return array<string, string[]>
 */
function altijara_category_map_aliases() {
	return array(
		'chamber'     => array( 'the-chamber-news', 'chamber', 'akhbar-al-ghurfa', 'اخبار-الغرفة', 'أخبار-الغرفة', 'أخبار الغرفة' ),
		'exhibitions' => array( 'exhibitions', 'maarid', 'المعارض' ),
		'followups'   => array( 'follow-ups', 'followups', 'mutabaat', 'متابعات' ),
		'interviews'  => array( 'interviews', 'liqaatz', 'لقاءات' ),
		'personality' => array( 'issue-cover-personality', 'personality', 'shakhsiyat', 'شخصية-الغلاف', 'شخصية العدد' ),
		'business'    => array( 'money-and-business', 'business', 'mal-waamal', 'mal-wa-aamal', 'مال-واعمال', 'مال-وأعمال', 'مال وأعمال' ),
		'logistics'   => array( 'logistic', 'logistics', 'لوجستيات' ),
		'industry'    => array( 'industry', 'الصناعة', 'sinaa' ),
		'energy'      => array( 'energy', 'الطاقة', 'taqa' ),
		'automotive'  => array( 'cars', 'automotive', 'سيارات' ),
		'health'      => array( 'health', 'صحة', 'صحّة', 'siha' ),
		'tech'        => array( 'technique', 'tech', 'technology', 'تقنية', 'taqniya' ),
		'tourism'     => array( 'tourism-and-entertainment', 'tourism', 'سياحة-وترفيه', 'سياحة وترفيه', 'سياحة', 'siyaha' ),
		'misc'        => array( 'miscellaneous', '_miscellaneous', 'misc', 'منوعات', 'munawwaat' ),
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
			'posts_per_page' => $count,
			'offset'         => $offset,
			'tax_query'      => array(
				array(
					'taxonomy'         => 'category',
					'field'            => 'term_id',
					'terms'            => array( $term_id ),
					'include_children' => true,
					'operator'         => 'IN',
				),
			),
		),
		'category_v2_' . $term_id
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
	$cache_key = altijara_cache_key( 'category_count_v2_' . $term_id, array( 'v' => 2 ) );
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
				array(
					'taxonomy'         => 'category',
					'field'            => 'term_id',
					'terms'            => array( $term_id ),
					'include_children' => true,
					'operator'         => 'IN',
				),
			),
		)
	);
	$total = (int) $q->found_posts;
	wp_reset_postdata();
	altijara_cache_set( $cache_key, $total );
	return $total;
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

/**
 * Default single-article sidebar widget settings.
 *
 * @return array{latest:array<string,mixed>,most_read:array<string,mixed>}
 */
function altijara_default_article_sidebar_settings() {
	return array(
		'latest'    => array(
			'count'        => 5,
			'source'       => 'same',
			'category_ids' => array(),
		),
		'most_read' => array(
			'count'        => 7,
			'source'       => 'same',
			'category_ids' => array(),
		),
	);
}

/**
 * Sanitize one sidebar widget block (latest / most_read).
 *
 * @param mixed  $raw     Raw input.
 * @param string $widget  latest|most_read.
 * @return array{count:int,source:string,category_ids:int[]}
 */
function altijara_sanitize_article_sidebar_widget( $raw, $widget = 'latest' ) {
	$defaults = altijara_default_article_sidebar_settings();
	$d        = isset( $defaults[ $widget ] ) ? $defaults[ $widget ] : $defaults['latest'];
	$max      = ( 'most_read' === $widget ) ? 15 : 12;
	$min      = 1;
	if ( ! is_array( $raw ) ) {
		return $d;
	}
	$count  = isset( $raw['count'] ) ? absint( $raw['count'] ) : (int) $d['count'];
	$count  = max( $min, min( $max, $count ) );
	$source = isset( $raw['source'] ) ? sanitize_key( $raw['source'] ) : $d['source'];
	if ( ! in_array( $source, array( 'same', 'selected' ), true ) ) {
		$source = 'same';
	}
	$ids = array();
	if ( ! empty( $raw['category_ids'] ) && is_array( $raw['category_ids'] ) ) {
		foreach ( $raw['category_ids'] as $id ) {
			$id = absint( $id );
			if ( $id ) {
				$ids[] = $id;
			}
		}
	}
	$ids = array_values( array_unique( $ids ) );
	return array(
		'count'        => $count,
		'source'       => $source,
		'category_ids' => $ids,
	);
}

/**
 * Sanitize full article options (related + sidebars).
 *
 * @param mixed $raw Raw POST article array.
 * @return array<string, mixed>
 */
function altijara_sanitize_article_settings( $raw ) {
	$defs = function_exists( 'altijara_default_options' ) ? altijara_default_options() : array();
	if ( ! is_array( $raw ) ) {
		$raw = array();
	}
	$allowed_share = array_keys( function_exists( 'altijara_article_share_platform_choices' ) ? altijara_article_share_platform_choices() : array() );
	$share         = array();
	if ( array_key_exists( 'share_platforms', $raw ) && is_array( $raw['share_platforms'] ) ) {
		foreach ( $raw['share_platforms'] as $p ) {
			$p = sanitize_key( $p );
			if ( $p && in_array( $p, $allowed_share, true ) ) {
				$share[] = $p;
			}
		}
	} elseif ( array_key_exists( 'share_platforms_submitted', $raw ) ) {
		$share = array();
	} else {
		$share = array( 'whatsapp', 'x', 'linkedin', 'facebook', 'telegram', 'copy' );
	}
	$share = array_values( array_unique( $share ) );

	$allowed_icons = array_keys( function_exists( 'altijara_article_share_icon_choices' ) ? altijara_article_share_icon_choices() : array( 'custom' => 'custom' ) );
	$share_extra   = array();
	if ( array_key_exists( 'share_extra', $raw ) && is_array( $raw['share_extra'] ) ) {
		foreach ( $raw['share_extra'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$label = isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '';
			$tpl   = isset( $row['url_template'] ) ? trim( wp_strip_all_tags( (string) $row['url_template'] ) ) : '';
			// Keep placeholders; only allow URL-safe chars + {url}/{title}.
			$tpl = preg_replace( '#[^a-zA-Z0-9\-._~:/?#\[\]@!$&\'()*+,;=%{}]#', '', $tpl );
			if ( '' === $label && '' === $tpl ) {
				continue;
			}
			if ( $tpl && ! preg_match( '#^(https?:|mailto:)#i', $tpl ) ) {
				continue;
			}
			$icon = isset( $row['icon'] ) ? sanitize_key( $row['icon'] ) : 'custom';
			if ( ! in_array( $icon, $allowed_icons, true ) ) {
				$icon = 'custom';
			}
			$share_extra[] = array(
				'enabled'      => ! empty( $row['enabled'] ),
				'label'        => $label,
				'url_template' => $tpl,
				'icon'         => $icon,
			);
		}
	}

	return array(
		'show_related'     => ! empty( $raw['show_related'] ),
		'related_count'    => isset( $raw['related_count'] ) ? max( 1, min( 12, absint( $raw['related_count'] ) ) ) : 2,
		'related_per_load' => isset( $raw['related_per_load'] ) ? max( 1, min( 12, absint( $raw['related_per_load'] ) ) ) : 2,
		'share_platforms'  => $share,
		'share_extra'      => $share_extra,
		'latest'           => altijara_sanitize_article_sidebar_widget( $raw['latest'] ?? array(), 'latest' ),
		'most_read'        => altijara_sanitize_article_sidebar_widget( $raw['most_read'] ?? array(), 'most_read' ),
	);
}

/**
 * Get article sidebar settings merged with defaults.
 *
 * @return array<string, mixed>
 */
function altijara_get_article_settings() {
	$saved = altijara_get_option( 'article', array() );
	return altijara_sanitize_article_settings( is_array( $saved ) ? $saved : array() );
}

/**
 * Resolve category IDs for an article sidebar widget.
 *
 * @param int                  $post_id Current article.
 * @param array<string, mixed> $cfg     Widget config (source, category_ids).
 * @return int[] Empty = site-wide (no category filter).
 */
function altijara_resolve_article_sidebar_cats( $post_id, $cfg ) {
	$source = isset( $cfg['source'] ) ? (string) $cfg['source'] : 'same';
	if ( 'selected' === $source ) {
		$ids = isset( $cfg['category_ids'] ) && is_array( $cfg['category_ids'] )
			? array_values( array_filter( array_map( 'absint', $cfg['category_ids'] ) ) )
			: array();
		if ( ! empty( $ids ) ) {
			return $ids;
		}
		// Selected but none checked → fall back to article categories.
	}
	// same = article categories.
	$cats = wp_get_post_categories( absint( $post_id ) );
	return array_values( array_filter( array_map( 'absint', (array) $cats ) ) );
}

/**
 * Query posts for single-article sidebar (أحدث الأخبار / الأكثر قراءة).
 *
 * @param int    $post_id Current article ID.
 * @param string $widget  latest|most_read.
 * @return WP_Post[]
 */
function altijara_get_article_sidebar_posts( $post_id, $widget = 'latest' ) {
	$post_id  = absint( $post_id );
	$settings = altijara_get_article_settings();
	$cfg      = isset( $settings[ $widget ] ) && is_array( $settings[ $widget ] )
		? $settings[ $widget ]
		: altijara_sanitize_article_sidebar_widget( array(), $widget );
	$count    = max( 1, (int) ( $cfg['count'] ?? 5 ) );
	$cats     = altijara_resolve_article_sidebar_cats( $post_id, $cfg );

	$args = array(
		'posts_per_page' => $count,
		'post__not_in'   => $post_id ? array( $post_id ) : array(),
	);
	if ( ! empty( $cats ) ) {
		$args['category__in'] = $cats;
	}

	if ( 'most_read' === $widget ) {
		$args['orderby'] = 'comment_count';
		$args['order']   = 'DESC';
		$posts           = altijara_query_posts( $args, 'article_mr_' . $post_id . '_' . $count );
		if ( count( $posts ) < $count ) {
			$fallback         = $args;
			$fallback['orderby'] = 'date';
			$fallback['order']   = 'DESC';
			$more             = altijara_query_posts( $fallback, 'article_mr_fb_' . $post_id );
			$seen             = wp_list_pluck( $posts, 'ID' );
			foreach ( $more as $p ) {
				if ( in_array( (int) $p->ID, $seen, true ) ) {
					continue;
				}
				$posts[] = $p;
				$seen[]  = (int) $p->ID;
				if ( count( $posts ) >= $count ) {
					break;
				}
			}
		}
		return array_slice( $posts, 0, $count );
	}

	$args['orderby'] = 'date';
	$args['order']   = 'DESC';
	return altijara_query_posts( $args, 'article_latest_' . $post_id . '_' . $count );
}
