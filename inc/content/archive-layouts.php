<?php
/**
 * Category archive layout assignment (posts vs magazine issues).
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed archive layout keys.
 *
 * @return string[]
 */
function altijara_archive_layout_choices() {
	return array(
		'posts'  => __( 'Posts archive', 'altijara' ),
		'issues' => __( 'Magazine issues', 'altijara' ),
	);
}

/**
 * Sanitize archive_layouts map (term_id => layout).
 *
 * @param mixed $raw Raw input.
 * @return array<string, string>
 */
function altijara_sanitize_archive_layouts( $raw ) {
	$out     = array();
	$allowed = array_keys( altijara_archive_layout_choices() );
	if ( ! is_array( $raw ) ) {
		return $out;
	}
	foreach ( $raw as $term_id => $layout ) {
		$tid = absint( $term_id );
		if ( ! $tid ) {
			continue;
		}
		$layout = sanitize_key( $layout );
		if ( ! in_array( $layout, $allowed, true ) ) {
			$layout = 'posts';
		}
		$out[ (string) $tid ] = $layout;
	}
	return $out;
}

/**
 * Sanitize magazine-layout category IDs (multi-select).
 *
 * @param mixed $raw Raw POST.
 * @return int[]
 */
function altijara_sanitize_archive_magazine_ids( $raw ) {
	return altijara_sanitize_archive_full_hero( $raw );
}

/**
 * Default posts-archive count controls.
 *
 * @return array{sidebar_count:int,featured_rows:int,news_size:int}
 */
function altijara_default_archive_counts() {
	return array(
		'sidebar_count' => 3,
		'featured_rows' => 2,
		'news_size'     => 10,
	);
}

/**
 * Sanitize posts-archive counts.
 *
 * @param mixed $raw Raw input.
 * @return array{sidebar_count:int,featured_rows:int,news_size:int}
 */
function altijara_sanitize_archive_counts( $raw ) {
	$d = altijara_default_archive_counts();
	if ( ! is_array( $raw ) ) {
		return $d;
	}
	$sidebar = isset( $raw['sidebar_count'] ) ? absint( $raw['sidebar_count'] ) : $d['sidebar_count'];
	$rows    = isset( $raw['featured_rows'] ) ? absint( $raw['featured_rows'] ) : $d['featured_rows'];
	$news    = isset( $raw['news_size'] ) ? absint( $raw['news_size'] ) : $d['news_size'];
	if ( ! in_array( $sidebar, array( 3, 4 ), true ) ) {
		$sidebar = 3;
	}
	if ( ! in_array( $rows, array( 1, 2, 3 ), true ) ) {
		$rows = 2;
	}
	if ( ! in_array( $news, array( 10, 15 ), true ) ) {
		$news = 10;
	}
	return array(
		'sidebar_count' => $sidebar,
		'featured_rows' => $rows,
		'news_size'     => $news,
	);
}

/**
 * Get posts-archive count settings.
 *
 * @return array{sidebar_count:int,featured_rows:int,news_size:int,featured_count:int,spotlight:int}
 */
function altijara_get_archive_counts() {
	$counts = altijara_sanitize_archive_counts( altijara_get_option( 'archive_counts', array() ) );
	$counts['featured_count'] = (int) $counts['featured_rows'] * 4;
	return $counts;
}

/**
 * Magazine category term IDs (issues layout). Migrates legacy archive_layouts map.
 *
 * @return int[]
 */
function altijara_get_archive_magazine_ids() {
	$raw = get_option( defined( 'ALTIJARA_OPTION_KEY' ) ? ALTIJARA_OPTION_KEY : 'altijara_theme_settings', array() );
	if ( is_array( $raw ) && array_key_exists( 'archive_magazine_ids', $raw ) && is_array( $raw['archive_magazine_ids'] ) ) {
		return array_values( array_unique( array_filter( array_map( 'absint', $raw['archive_magazine_ids'] ) ) ) );
	}
	// Legacy: term_id => issues in archive_layouts.
	$map = altijara_get_option( 'archive_layouts', array() );
	$ids = array();
	if ( is_array( $map ) ) {
		foreach ( $map as $tid => $layout ) {
			if ( 'issues' === $layout ) {
				$tid = absint( $tid );
				if ( $tid ) {
					$ids[] = $tid;
				}
			}
		}
	}
	return array_values( array_unique( $ids ) );
}

/**
 * Build archive_layouts map from magazine ID list (BC for older callers).
 *
 * @param int[] $magazine_ids Term IDs.
 * @return array<string, string>
 */
function altijara_archive_layouts_from_magazine_ids( $magazine_ids ) {
	$out = array();
	foreach ( (array) $magazine_ids as $tid ) {
		$tid = absint( $tid );
		if ( $tid ) {
			$out[ (string) $tid ] = 'issues';
		}
	}
	return $out;
}

/**
 * Resolve layout for a category term.
 *
 * @param int $term_id Category term ID.
 * @return string posts|issues
 */
function altijara_get_category_archive_layout( $term_id ) {
	$term_id = absint( $term_id );
	if ( ! $term_id ) {
		return 'posts';
	}
	if ( in_array( $term_id, altijara_get_archive_magazine_ids(), true ) ) {
		return 'issues';
	}
	return 'posts';
}

/**
 * Sanitize list of category term IDs that use full-width hero (no side recommended column).
 *
 * @param mixed $raw Raw POST (array of term IDs).
 * @return int[]
 */
function altijara_sanitize_archive_full_hero( $raw ) {
	$out = array();
	if ( ! is_array( $raw ) ) {
		return $out;
	}
	foreach ( $raw as $tid ) {
		$tid = absint( $tid );
		if ( $tid ) {
			$out[] = $tid;
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * Category term IDs using hero-only top layout.
 * Until saved from Archives tab, falls back to legacy keys: automotive, interviews, tourism, exhibitions.
 *
 * @return int[]
 */
function altijara_get_full_hero_category_ids() {
	$raw = get_option( defined( 'ALTIJARA_OPTION_KEY' ) ? ALTIJARA_OPTION_KEY : 'altijara_theme_settings', array() );
	if ( is_array( $raw ) && array_key_exists( 'archive_full_hero', $raw ) && is_array( $raw['archive_full_hero'] ) ) {
		// Explicit save (including empty) wins over legacy defaults.
		return array_values( array_unique( array_filter( array_map( 'absint', $raw['archive_full_hero'] ) ) ) );
	}

	$legacy_keys = array( 'automotive', 'interviews', 'tourism', 'exhibitions' );
	$ids         = array();
	foreach ( $legacy_keys as $key ) {
		$id = function_exists( 'altijara_resolve_category_id' ) ? altijara_resolve_category_id( $key ) : 0;
		if ( $id ) {
			$ids[] = (int) $id;
		}
	}
	return array_values( array_unique( $ids ) );
}

/**
 * Whether a category archive should show a single full hero (no side list).
 *
 * @param int $term_id Category term ID.
 * @return bool
 */
function altijara_category_uses_full_hero( $term_id ) {
	$term_id = absint( $term_id );
	if ( ! $term_id ) {
		return false;
	}
	return in_array( $term_id, altijara_get_full_hero_category_ids(), true );
}

/**
 * Whether the current request should use the issues archive UI.
 *
 * @return bool
 */
function altijara_is_issues_archive_view() {
	if ( is_post_type_archive( 'issue' ) || is_page_template( 'page-issues.php' ) ) {
		return true;
	}
	if ( is_category() ) {
		$term = get_queried_object();
		if ( $term && ! empty( $term->term_id ) ) {
			return 'issues' === altijara_get_category_archive_layout( (int) $term->term_id );
		}
	}
	return false;
}

/**
 * Query published magazine issues.
 *
 * @param int $count Max posts.
 * @param int $offset Offset.
 * @return WP_Post[]
 */
function altijara_get_issue_posts( $count = 24, $offset = 0 ) {
	return altijara_query_posts(
		array(
			'post_type'      => 'issue',
			'posts_per_page' => max( 1, min( 100, (int) $count ) ),
			'offset'         => max( 0, (int) $offset ),
			'orderby'        => 'date',
			'order'          => 'DESC',
		),
		'issues_archive'
	);
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
		'pdf'       => array( '_altijara_pdf_url', 'pdf_url', 'pdf' ),
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
		} elseif ( preg_match( '/(\d{3,4})/', get_the_title( $post_id ), $m ) ) {
			$number = (int) $m[1];
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
 * @param int $count Max items.
 * @return array<int, array<string, mixed>>
 */
function altijara_get_magazine_slider_items( $count = 8 ) {
	$count = max( 1, min( 12, (int) $count ) );
	$items = array();
	$seen  = array();

	foreach ( altijara_get_issue_posts( max( $count * 2, 12 ), 0 ) as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
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
		$items[]      = $row;
		if ( count( $items ) >= $count ) {
			break;
		}
	}

	// Fill from static catalog if CPT covers are scarce.
	if ( count( $items ) < $count ) {
		foreach ( altijara_get_static_issue_covers() as $row ) {
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
			if ( count( $items ) >= $count ) {
				break;
			}
		}
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
	if ( ! $number && preg_match( '/(\d{3,4})/', get_the_title( $post ), $m ) ) {
		$number = $m[1];
	}
	$cover     = altijara_resolve_issue_cover_url( $pid, $number ? (int) $number : 0 );
	$flipbook  = function_exists( 'altijara_resolve_issue_flipbook_url' ) ? altijara_resolve_issue_flipbook_url( $pid ) : altijara_get_issue_meta( $pid, 'flipbook' );
	if ( ! $flipbook && $number ) {
		$static = altijara_get_static_issue_covers();
		if ( ! empty( $static[ (int) $number ]['flipbook'] ) ) {
			$flipbook = $static[ (int) $number ]['flipbook'];
		}
	}
	$pdf       = altijara_get_issue_meta( $pid, 'pdf' );
	$link      = $flipbook ? $flipbook : get_permalink( $post );
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
		'cover'     => $cover,
		'src'       => $cover,
		'excerpt'   => get_the_excerpt( $post ),
		'permalink' => get_permalink( $post ),
		'group'     => 'recent',
	);
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
	if ( preg_match( '/(\d{3,4})/', get_the_title( $post ), $m ) ) {
		return (int) $m[1];
	}
	return 0;
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
			'title' => __( 'إصدارات مجلة التجارة — الأحدث', 'altijara' ),
			'items' => $recent,
		),
		array(
			'id'    => 'archive2020CarouselTrack',
			'sec'   => 'secArchive2020',
			'title' => __( 'إصدارات مجلة التجارة — أرشيف (704–717)', 'altijara' ),
			'items' => $mid,
		),
		array(
			'id'    => 'historicCarouselTrack',
			'sec'   => 'secHistoric',
			'title' => __( 'إصدارات مجلة التجارة — أعداد سابقة', 'altijara' ),
			'items' => $historic,
		),
	);

	return array(
		'lead'   => $lead,
		'chunks' => $chunks,
	);
}

/**
 * Render shared issues archive UI (lead + carousels).
 *
 * @param array<string, mixed> $args Optional args.
 */
function altijara_render_issues_archive( $args = array() ) {
	$issues = altijara_get_issue_posts( 80, 0 );

	?>
	<header class="issues-page-header">
		<h1 class="issues-page-title"><?php esc_html_e( 'أعداد مجلة التجارة', 'altijara' ); ?></h1>
		<p class="issues-page-subtitle"><?php esc_html_e( 'أرشيف الإصدارات الرقمية لمجلة غرفة جدة للتصفح والتحميل.', 'altijara' ); ?></p>
	</header>
	<?php

	// Custom layout from Theme Options → Archives, else hardcoded bands.
	if ( function_exists( 'altijara_get_issues_layout' ) && function_exists( 'altijara_render_layout_rows' ) ) {
		$pack = altijara_get_issues_layout( true );
		altijara_with_layout_context(
			array( 'surface' => 'issues' ),
			static function () use ( $pack ) {
				altijara_render_layout_rows( $pack );
			}
		);
		return;
	}

	$bands  = altijara_issues_archive_bands( $issues );
	$lead   = $bands['lead'];
	$chunks = $bands['chunks'];

	get_template_part(
		'template-parts/archive/issues-lead',
		null,
		array(
			'issue' => $lead,
		)
	);

	foreach ( $chunks as $chunk ) {
		if ( empty( $chunk['items'] ) ) {
			continue;
		}
		get_template_part(
			'template-parts/archive/issues-carousel',
			null,
			$chunk
		);
	}
}
