<?php
/**
 * Issue / magazine read model helpers.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sort issue posts by parsed issue number.
 *
 * @param WP_Post[] $posts Issues.
 * @param string    $order ASC|DESC.
 * @return WP_Post[]
 */
function altijara_sort_issues_by_number( $posts, $order = 'DESC' ) {
	$posts = array_values(
		array_filter(
			(array) $posts,
			static function ( $p ) {
				return $p instanceof WP_Post;
			}
		)
	);
	$order = ( 'ASC' === strtoupper( (string) $order ) ) ? 'ASC' : 'DESC';
	usort(
		$posts,
		static function ( $a, $b ) use ( $order ) {
			$na = function_exists( 'altijara_issue_number_int' ) ? altijara_issue_number_int( $a ) : 0;
			$nb = function_exists( 'altijara_issue_number_int' ) ? altijara_issue_number_int( $b ) : 0;
			if ( $na === $nb ) {
				// Stable-ish tie-break: newer publish date first when numbers match.
				$ta = $a instanceof WP_Post ? strtotime( (string) $a->post_date_gmt ) : 0;
				$tb = $b instanceof WP_Post ? strtotime( (string) $b->post_date_gmt ) : 0;
				if ( $ta === $tb ) {
					return 0;
				}
				return ( $ta > $tb ) ? -1 : 1;
			}
			if ( 'ASC' === $order ) {
				return ( $na < $nb ) ? -1 : 1;
			}
			return ( $na > $nb ) ? -1 : 1;
		}
	);
	return $posts;
}

/**
 * Query published magazine issues (highest issue number first).
 *
 * @param int $count Max posts.
 * @param int $offset Offset after number sort.
 * @return WP_Post[]
 */
function altijara_get_issue_posts( $count = 24, $offset = 0 ) {
	$count  = max( 1, min( 100, (int) $count ) );
	$offset = max( 0, (int) $offset );
	// Wide pool so title/meta number order is not skewed by a short date query.
	$pool   = min( 100, max( $count + $offset, 80 ) );
	$posts  = altijara_query_posts(
		array(
			'post_type'      => 'issue',
			'posts_per_page' => $pool,
			'offset'         => 0,
			'orderby'        => 'date',
			'order'          => 'DESC',
		),
		'issues_archive'
	);
	$posts = altijara_sort_issues_by_number( $posts, 'DESC' );
	if ( $offset > 0 ) {
		$posts = array_slice( $posts, $offset );
	}
	return array_slice( $posts, 0, $count );
}

/**
 * Issue meta helper (flipbook / PDF / number / stats).
 *
 * @param int    $post_id Post ID.
 * @param string $key     Logical key.
 * @return string
 */
function altijara_get_issue_meta( $post_id, $key ) {
	$post_id = absint( $post_id );
	$map     = array(
		'flipbook'  => array( '_altijara_flipbook_url', 'flipbook_url', 'flipbook' ),
		'pdf'       => array( '_altijara_pdf_url', '_altijara_pdf_id', 'pdf_url', 'pdf', 'pdfLink' ),
		'number'    => array( '_altijara_issue_number', 'issue_number', 'number' ),
		'pages'     => array( '_altijara_pages', 'pages' ),
		'files'     => array( '_altijara_files', 'files' ),
		'talks'     => array( '_altijara_interviews', 'interviews', 'talks' ),
		'tag'       => array( '_altijara_issue_tag', 'issue_tag' ),
		'tag_color' => array( '_altijara_issue_tag_color', 'tag_color', 'tagColor' ),
	);
	$keys = isset( $map[ $key ] ) ? $map[ $key ] : array( $key );
	foreach ( $keys as $meta_key ) {
		$val = get_post_meta( $post_id, $meta_key, true );
		if ( '' !== $val && null !== $val ) {
			$out = is_scalar( $val ) ? (string) $val : '';
			if ( 'flipbook' === $key && $out && function_exists( 'altijara_normalize_flipbook_url' ) ) {
				return altijara_normalize_flipbook_url( $out );
			}
			if ( 'pdf' === $key && $out && function_exists( 'altijara_normalize_pdf_url' ) ) {
				$resolved = altijara_normalize_pdf_url( $out );
				if ( $resolved ) {
					return $resolved;
				}
			}
			return $out;
		}
	}
	return '';
}

/**
 * Absolute cover URL for an issue — featured image only (no theme placeholder).
 *
 * @param int $post_id Issue ID.
 * @return string
 */
function altijara_get_issue_featured_cover_url( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return '';
	}
	$url = get_the_post_thumbnail_url( $post_id, 'full' );
	if ( ! $url ) {
		$url = get_the_post_thumbnail_url( $post_id, 'large' );
	}
	return $url ? esc_url_raw( $url ) : '';
}

/**
 * Static cover catalog from issues_data.json (staging absolute URLs).
 *
 * @return array<int, array{number:int,cover:string,flipbook:string,title:string}>
 */
function altijara_get_static_issue_covers() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$cache = array();
	$path  = trailingslashit( ALTIJARA_THEME_DIR ) . 'assets/data/issues_data.json';
	if ( ! file_exists( $path ) ) {
		return $cache;
	}
	$raw = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_array( $raw ) ) {
		return $cache;
	}
	foreach ( $raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$num   = isset( $row['number'] ) ? (int) $row['number'] : 0;
		$cover = isset( $row['cover'] ) ? esc_url_raw( (string) $row['cover'] ) : '';
		if ( ! $num || ! $cover ) {
			continue;
		}
		$cache[ $num ] = array(
			'number'   => $num,
			'cover'    => $cover,
			'flipbook' => isset( $row['flipbook'] ) ? esc_url_raw( (string) $row['flipbook'] ) : '',
			'title'    => isset( $row['title'] ) ? sanitize_text_field( (string) $row['title'] ) : '',
		);
	}
	return $cache;
}

/**
 * Resolve cover URL: featured image first, then static JSON by issue number.
 *
 * @param int $post_id Issue ID.
 * @param int $number  Issue number.
 * @return string Absolute URL or empty.
 */
function altijara_resolve_issue_cover_url( $post_id, $number = 0 ) {
	$cover = altijara_get_issue_featured_cover_url( $post_id );
	if ( $cover ) {
		return $cover;
	}
	$number = absint( $number );
	if ( ! $number && $post_id ) {
		$raw = altijara_get_issue_meta( $post_id, 'number' );
		if ( $raw && preg_match( '/(\d+)/', (string) $raw, $m ) ) {
			$number = (int) $m[1];
		} else {
			$number = altijara_parse_issue_number_from_text( get_the_title( $post_id ) );
		}
	}
	if ( $number ) {
		$static = altijara_get_static_issue_covers();
		if ( ! empty( $static[ $number ]['cover'] ) ) {
			return $static[ $number ]['cover'];
		}
	}
	return '';
}

/**
 * Items for sidebar «من إصدارات المجلة» slider (unique covers).
 *
 * @param int    $count    Max items (1–20).
 * @param int    $band_min Inclusive issue number lower bound (0 = none).
 * @param int    $band_max Inclusive issue number upper bound (0 = none).
 * @param string $order    ASC = lowest issue # first (From → To); DESC = highest first (To → From).
 * @return array<int, array<string, mixed>>
 */
function altijara_get_magazine_slider_items( $count = 8, $band_min = 0, $band_max = 0, $order = 'DESC' ) {
	$count    = max( 1, min( 20, (int) $count ) );
	$band_min = max( 0, min( 9999, (int) $band_min ) );
	$band_max = max( 0, min( 9999, (int) $band_max ) );
	$order    = ( 'ASC' === strtoupper( (string) $order ) ) ? 'ASC' : 'DESC';
	if ( $band_min > 0 && $band_max > 0 && $band_min > $band_max ) {
		$tmp      = $band_min;
		$band_min = $band_max;
		$band_max = $tmp;
	}
	$apply_band = ( $band_min > 0 || $band_max > 0 );
	// Collect a wide pool so number-order (esp. ASC / banded ranges) is not skewed by date DESC.
	$pool  = ( $apply_band || 'ASC' === $order ) ? 80 : max( $count * 2, 12 );
	$items = array();
	$seen  = array();

	foreach ( altijara_get_issue_posts( $pool, 0 ) as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}
		$num = function_exists( 'altijara_issue_number_int' ) ? altijara_issue_number_int( $post ) : 0;
		if ( $apply_band ) {
			if ( $num < 1 ) {
				continue;
			}
			if ( $band_min > 0 && $num < $band_min ) {
				continue;
			}
			if ( $band_max > 0 && $num > $band_max ) {
				continue;
			}
		}
		$row = altijara_issue_to_js_payload( $post );
		if ( empty( $row['cover'] ) ) {
			continue;
		}
		$key = md5( (string) $row['cover'] );
		if ( isset( $seen[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;
		if ( ! isset( $row['number'] ) || '' === (string) $row['number'] ) {
			$row['number'] = $num;
		}
		$items[] = $row;
	}

	// Fill from static catalog if CPT covers are scarce.
	if ( count( $items ) < $pool ) {
		foreach ( altijara_get_static_issue_covers() as $row ) {
			$num = isset( $row['number'] ) ? (int) $row['number'] : 0;
			if ( $apply_band ) {
				if ( $num < 1 ) {
					continue;
				}
				if ( $band_min > 0 && $num < $band_min ) {
					continue;
				}
				if ( $band_max > 0 && $num > $band_max ) {
					continue;
				}
			}
			$key = md5( $row['cover'] );
			if ( isset( $seen[ $key ] ) ) {
				continue;
			}
			$seen[ $key ] = true;
			$items[]      = array(
				'id'       => $row['number'],
				'number'   => $row['number'],
				'title'    => $row['title'] ? $row['title'] : sprintf( /* translators: %d issue number */ __( 'العدد %d', 'altijara' ), $row['number'] ),
				'cover'    => $row['cover'],
				'src'      => $row['cover'],
				'flipbook' => $row['flipbook'],
				'link'     => $row['flipbook'] ? $row['flipbook'] : '',
			);
			if ( count( $items ) >= $pool ) {
				break;
			}
		}
	}

	usort(
		$items,
		static function ( $a, $b ) use ( $order ) {
			$na = isset( $a['number'] ) ? (int) $a['number'] : 0;
			$nb = isset( $b['number'] ) ? (int) $b['number'] : 0;
			if ( $na === $nb ) {
				return 0;
			}
			if ( 'ASC' === $order ) {
				return ( $na < $nb ) ? -1 : 1;
			}
			return ( $na > $nb ) ? -1 : 1;
		}
	);

	if ( count( $items ) > $count ) {
		$items = array_slice( $items, 0, $count );
	}

	return $items;
}

/**
 * Build a payload array for JS (issues.js / modal) from a WP issue post.
 *
 * @param WP_Post $post Issue post.
 * @return array<string, mixed>
 */
function altijara_issue_to_js_payload( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return array();
	}
	$pid    = (int) $post->ID;
	$number = altijara_get_issue_meta( $pid, 'number' );
	if ( ! $number ) {
		$parsed = altijara_parse_issue_number_from_text( get_the_title( $post ) );
		$number = $parsed ? (string) $parsed : '';
	}
	$cover     = altijara_resolve_issue_cover_url( $pid, $number ? (int) $number : 0 );
	$flipbook  = function_exists( 'altijara_resolve_issue_flipbook_url' ) ? altijara_resolve_issue_flipbook_url( $pid ) : altijara_get_issue_meta( $pid, 'flipbook' );
	if ( ! $flipbook && $number ) {
		$static = altijara_get_static_issue_covers();
		if ( ! empty( $static[ (int) $number ]['flipbook'] ) ) {
			$flipbook = $static[ (int) $number ]['flipbook'];
		}
	}
	$pdf       = function_exists( 'altijara_resolve_issue_pdf_url' )
		? altijara_resolve_issue_pdf_url( $pid )
		: altijara_get_issue_meta( $pid, 'pdf' );
	$can_access = ! function_exists( 'altijara_user_can_access_issue' ) || altijara_user_can_access_issue( $pid );
	if ( ! $can_access ) {
		$flipbook = '';
		$pdf      = '';
	}
	$archive = get_post_type_archive_link( 'issue' );
	$archive = $archive ? $archive : home_url( '/issues/' );
	// Locked: archive only (do not leak Flipbook via post_type_link). Else Flipbook or archive.
	if ( $can_access && function_exists( 'altijara_get_issue_public_url' ) ) {
		$link = altijara_get_issue_public_url( $pid );
	} else {
		$link = $archive;
	}
	$tag_color = altijara_get_issue_meta( $pid, 'tag_color' );
	return array(
		'id'        => $pid,
		'number'    => $number ? (int) $number : $pid,
		'title'     => get_the_title( $post ),
		'caption'   => $number ? sprintf( /* translators: %s issue number */ __( 'Issue %s', 'altijara' ), $number ) : get_the_title( $post ),
		'date'      => function_exists( 'altijara_format_date_ar' ) ? altijara_format_date_ar( $pid ) : get_the_date( '', $post ),
		'year'      => (int) get_the_date( 'Y', $post ),
		'pages'     => (int) altijara_get_issue_meta( $pid, 'pages' ),
		'tag'       => altijara_get_issue_meta( $pid, 'tag' ),
		'tagColor'  => $tag_color ? $tag_color : '',
		'link'      => $link,
		'flipbook'  => $flipbook,
		'pdfLink'   => $pdf,
		'locked'    => ! $can_access,
		'cover'     => $cover,
		'src'       => $cover,
		'excerpt'   => get_the_excerpt( $post ),
		'permalink' => $link,
		'group'     => 'recent',
	);
}

/**
 * First 3–4 digit run in free text (titles, filenames, meta).
 *
 * @param string $text Raw text.
 * @return int Issue number or 0.
 */
function altijara_parse_issue_number_from_text( $text ) {
	$text = (string) $text;
	if ( '' === $text ) {
		return 0;
	}
	if ( preg_match( '/(\d{3,4})/', $text, $m ) ) {
		return (int) $m[1];
	}
	return 0;
}

/**
 * Numeric issue number for banding carousels.
 *
 * @param WP_Post $post Issue post.
 * @return int
 */
function altijara_issue_number_int( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return 0;
	}
	$raw = altijara_get_issue_meta( (int) $post->ID, 'number' );
	if ( $raw && preg_match( '/(\d+)/', (string) $raw, $m ) ) {
		return (int) $m[1];
	}
	return altijara_parse_issue_number_from_text( get_the_title( $post ) );
}

/**
 * Auto heading for an issues carousel from the numbers actually shown.
 *
 * @param WP_Post[] $posts Issues in the band.
 * @param string    $order ASC|DESC — higher first when DESC (default).
 * @return string
 */
function altijara_issues_band_auto_title( $posts, $order = 'DESC' ) {
	$nums = array();
	foreach ( (array) $posts as $p ) {
		$n = altijara_issue_number_int( $p );
		if ( $n > 0 ) {
			$nums[] = $n;
		}
	}
	if ( ! $nums ) {
		return __( 'إصدارات مجلة التجارة', 'altijara' );
	}
	$max = max( $nums );
	$min = min( $nums );
	if ( $max === $min ) {
		return sprintf(
			/* translators: %d: issue number */
			__( 'إصدارات مجلة التجارة (العدد %d)', 'altijara' ),
			$max
		);
	}
	$asc = ( 'ASC' === strtoupper( (string) $order ) );
	$hi  = $asc ? $min : $max;
	$lo  = $asc ? $max : $min;
	return sprintf(
		/* translators: 1: first issue # in band order, 2: last */
		__( 'إصدارات مجلة التجارة (الأعداد %1$d – %2$d)', 'altijara' ),
		$hi,
		$lo
	);
}

/**
 * Split issues into screenshot bands by issue number (≥718, 704–717, &lt;704).
 *
 * @param WP_Post[] $issues All issues newest-first.
 * @return array{lead:?WP_Post,chunks:array<int,array<string,mixed>>}
 */
function altijara_issues_archive_bands( $issues ) {
	$issues = array_values( array_filter( (array) $issues, static function ( $p ) {
		return $p instanceof WP_Post;
	} ) );
	$lead = ! empty( $issues[0] ) ? $issues[0] : null;
	$rest = $lead ? array_slice( $issues, 1 ) : $issues;

	$recent   = array();
	$mid      = array();
	$historic = array();
	foreach ( $rest as $post ) {
		$num = altijara_issue_number_int( $post );
		if ( $num >= 718 ) {
			$recent[] = $post;
		} elseif ( $num >= 704 ) {
			$mid[] = $post;
		} else {
			$historic[] = $post;
		}
	}

	// If numbers are missing, fall back to slice bands so the UI is never empty.
	if ( empty( $recent ) && empty( $mid ) && empty( $historic ) && $rest ) {
		$recent   = array_slice( $rest, 0, 12 );
		$mid      = array_slice( $rest, 12, 12 );
		$historic = array_slice( $rest, 24, 24 );
	}

	$chunks = array(
		array(
			'id'    => 'recentCarouselTrack',
			'sec'   => 'secRecent',
			'title' => altijara_issues_band_auto_title( $recent ),
			'items' => $recent,
		),
		array(
			'id'    => 'archive2020CarouselTrack',
			'sec'   => 'secArchive2020',
			'title' => altijara_issues_band_auto_title( $mid ),
			'items' => $mid,
		),
		array(
			'id'    => 'historicCarouselTrack',
			'sec'   => 'secHistoric',
			'title' => altijara_issues_band_auto_title( $historic ),
			'items' => $historic,
		),
	);

	return array(
		'lead'   => $lead,
		'chunks' => $chunks,
	);
}
