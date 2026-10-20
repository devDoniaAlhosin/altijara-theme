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
	$map = altijara_get_option( 'archive_layouts', array() );
	if ( ! is_array( $map ) ) {
		$map = array();
	}
	$key = (string) $term_id;
	if ( ! empty( $map[ $key ] ) && 'issues' === $map[ $key ] ) {
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
		'flipbook' => array( '_altijara_flipbook_url', 'flipbook_url', 'flipbook' ),
		'pdf'      => array( '_altijara_pdf_url', 'pdf_url', 'pdf' ),
		'number'   => array( '_altijara_issue_number', 'issue_number', 'number' ),
		'pages'    => array( '_altijara_pages', 'pages' ),
		'files'    => array( '_altijara_files', 'files' ),
		'talks'    => array( '_altijara_interviews', 'interviews', 'talks' ),
		'tag'      => array( '_altijara_issue_tag', 'issue_tag' ),
	);
	$keys = isset( $map[ $key ] ) ? $map[ $key ] : array( $key );
	foreach ( $keys as $meta_key ) {
		$val = get_post_meta( $post_id, $meta_key, true );
		if ( '' !== $val && null !== $val ) {
			return is_scalar( $val ) ? (string) $val : '';
		}
	}
	return '';
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
	$cover    = function_exists( 'altijara_get_thumb_url' ) ? altijara_get_thumb_url( $pid ) : get_the_post_thumbnail_url( $pid, 'large' );
	$flipbook = altijara_get_issue_meta( $pid, 'flipbook' );
	$pdf      = altijara_get_issue_meta( $pid, 'pdf' );
	$link     = $flipbook ? $flipbook : get_permalink( $post );
	return array(
		'id'       => $pid,
		'number'   => $number ? (int) $number : $pid,
		'title'    => get_the_title( $post ),
		'caption'  => $number ? sprintf( /* translators: %s issue number */ __( 'Issue %s', 'altijara' ), $number ) : get_the_title( $post ),
		'date'     => function_exists( 'altijara_format_date_ar' ) ? altijara_format_date_ar( $pid ) : get_the_date( '', $post ),
		'year'     => (int) get_the_date( 'Y', $post ),
		'pages'    => (int) altijara_get_issue_meta( $pid, 'pages' ),
		'tag'      => altijara_get_issue_meta( $pid, 'tag' ),
		'link'     => $link,
		'flipbook' => $flipbook,
		'pdfLink'  => $pdf,
		'cover'    => $cover,
		'src'      => $cover,
		'excerpt'  => get_the_excerpt( $post ),
		'permalink'=> get_permalink( $post ),
		'group'    => 'recent',
	);
}

/**
 * Render shared issues archive UI (lead + carousels + modal).
 *
 * @param array<string, mixed> $args Optional args.
 */
function altijara_render_issues_archive( $args = array() ) {
	$issues = altijara_get_issue_posts( 48, 0 );
	$lead   = ! empty( $issues[0] ) ? $issues[0] : null;
	$rest   = $lead ? array_slice( $issues, 1 ) : $issues;

	// Split into up to 3 carousel groups for prototype parity.
	$chunks = array(
		array(
			'id'    => 'recentCarouselTrack',
			'sec'   => 'secRecent',
			'title' => __( 'إصدارات مجلة التجارة — الأحدث', 'altijara' ),
			'items' => array_slice( $rest, 0, 12 ),
		),
		array(
			'id'    => 'archive2020CarouselTrack',
			'sec'   => 'secArchive2020',
			'title' => __( 'إصدارات مجلة التجارة — أرشيف', 'altijara' ),
			'items' => array_slice( $rest, 12, 12 ),
		),
		array(
			'id'    => 'historicCarouselTrack',
			'sec'   => 'secHistoric',
			'title' => __( 'إصدارات مجلة التجارة — أعداد سابقة', 'altijara' ),
			'items' => array_slice( $rest, 24, 24 ),
		),
	);

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

	get_template_part( 'template-parts/archive/issues-modal' );
}
