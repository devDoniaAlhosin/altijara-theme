<?php
/**
 * Category map resolution and aliases.
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
