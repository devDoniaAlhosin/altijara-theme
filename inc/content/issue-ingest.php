<?php
/**
 * Ingest magazine issues from assets/data/issues_data.json into the issue CPT,
 * attaching featured images that already exist in the Media Library.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Arabic month name → month number.
 *
 * @param string $label Date label e.g. "سبتمبر 2026".
 * @return array{year:int,month:int}
 */
function altijara_parse_issue_date_label( $label ) {
	$label = (string) $label;
	$year  = 0;
	if ( preg_match( '/(20\d{2})/', $label, $m ) ) {
		$year = (int) $m[1];
	}
	$months = array(
		'يناير'   => 1,
		'فبراير'  => 2,
		'مارس'    => 3,
		'أبريل'   => 4,
		'ابريل'   => 4,
		'مايو'    => 5,
		'يونيو'   => 6,
		'يوليو'   => 7,
		'أغسطس'   => 8,
		'اغسطس'   => 8,
		'سبتمبر'  => 9,
		'أكتوبر'  => 10,
		'اكتوبر'  => 10,
		'نوفمبر'  => 11,
		'ديسمبر'  => 12,
	);
	$month = 1;
	foreach ( $months as $ar => $num ) {
		if ( false !== mb_strpos( $label, $ar ) ) {
			$month = $num;
			break;
		}
	}
	return array(
		'year'  => $year ? $year : (int) gmdate( 'Y' ),
		'month' => $month,
	);
}

/**
 * Candidate cover URLs rewritten to the current site uploads base.
 *
 * @param string $url Original cover URL.
 * @return string[]
 */
function altijara_issue_cover_url_candidates( $url ) {
	$url = esc_url_raw( (string) $url );
	if ( ! $url ) {
		return array();
	}
	$candidates = array( $url );
	$path       = wp_parse_url( $url, PHP_URL_PATH );
	if ( $path && false !== strpos( $path, '/wp-content/uploads/' ) ) {
		$rel = substr( $path, strpos( $path, '/wp-content/uploads/' ) + strlen( '/wp-content/uploads/' ) );
		$rel = ltrim( rawurldecode( (string) $rel ), '/' );
		$uploads = wp_upload_dir();
		if ( empty( $uploads['error'] ) && ! empty( $uploads['baseurl'] ) ) {
			$candidates[] = trailingslashit( $uploads['baseurl'] ) . $rel;
		}
		$candidates[] = home_url( '/wp-content/uploads/' . $rel );
		$candidates[] = site_url( '/wp-content/uploads/' . $rel );
	}
	$out = array();
	foreach ( $candidates as $c ) {
		$c = esc_url_raw( $c );
		if ( $c ) {
			$out[ $c ] = true;
			$stripped  = preg_replace( '/-\d+x\d+(?=\.(jpe?g|png|gif|webp)$)/i', '', $c );
			if ( $stripped && $stripped !== $c ) {
				$out[ esc_url_raw( $stripped ) ] = true;
			}
		}
	}
	return array_keys( $out );
}

/**
 * Sideload a remote cover into the Media Library and return attachment ID.
 *
 * @param string $url     Remote image URL.
 * @param int    $post_id Parent issue post ID.
 * @return int
 */
function altijara_sideload_issue_cover( $url, $post_id = 0 ) {
	$url = esc_url_raw( (string) $url );
	if ( ! $url || ! preg_match( '#^https?://#i', $url ) ) {
		return 0;
	}
	if ( ! function_exists( 'media_sideload_image' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}
	// Prefer full cover (strip size suffix).
	$url = preg_replace( '/-\d+x\d+(?=\.(jpe?g|png|gif|webp)$)/i', '', $url );
	$att = media_sideload_image( $url, absint( $post_id ), null, 'id' );
	if ( is_wp_error( $att ) ) {
		return 0;
	}
	return absint( $att );
}

/**
 * Find a Media Library attachment for a cover URL (remote or local path).
 *
 * @param string $url Cover URL from JSON.
 * @return int Attachment ID or 0.
 */
function altijara_find_media_attachment_for_cover( $url ) {
	$url = esc_url_raw( (string) $url );
	if ( ! $url ) {
		return 0;
	}

	// 1) Direct WP resolver (+ local uploads rewrite from production domain).
	if ( function_exists( 'attachment_url_to_postid' ) ) {
		foreach ( altijara_issue_cover_url_candidates( $url ) as $candidate ) {
			$id = (int) attachment_url_to_postid( $candidate );
			if ( $id ) {
				return $id;
			}
		}
	}

	$path     = wp_parse_url( $url, PHP_URL_PATH );
	$basename = $path ? wp_basename( $path ) : '';
	$basename = rawurldecode( $basename );
	if ( ! $basename ) {
		return 0;
	}
	$name_no_size = preg_replace( '/-\d+x\d+(?=\.(jpe?g|png|gif|webp)$)/i', '', $basename );
	$stem         = pathinfo( $name_no_size, PATHINFO_FILENAME );

	global $wpdb;

	// 2) Match _wp_attached_file by basename / stem.
	$like_basenames = array_unique(
		array_filter(
			array(
				$basename,
				$name_no_size,
				$stem ? $stem . '.png' : '',
				$stem ? $stem . '.jpg' : '',
				$stem ? $stem . '.jpeg' : '',
				$stem ? $stem . '.webp' : '',
			)
		)
	);
	foreach ( $like_basenames as $bn ) {
		$found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta}
				WHERE meta_key = '_wp_attached_file'
				AND meta_value LIKE %s
				ORDER BY post_id DESC
				LIMIT 1",
				'%' . $wpdb->esc_like( $bn )
			)
		);
		if ( $found ) {
			return (int) $found;
		}
	}

	// 3) Match attachment title / guid containing stem (Issue-730, غلاف, etc.).
	if ( $stem && mb_strlen( $stem ) >= 4 ) {
		$found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts}
				WHERE post_type = 'attachment'
				AND post_mime_type LIKE 'image/%%'
				AND ( post_title LIKE %s OR guid LIKE %s )
				ORDER BY ID DESC
				LIMIT 1",
				'%' . $wpdb->esc_like( $stem ) . '%',
				'%' . $wpdb->esc_like( $stem ) . '%'
			)
		);
		if ( $found ) {
			return (int) $found;
		}
	}

	// 4) Match by issue number in filename (Issue-730, العدد-726, No.728).
	$parsed_num = function_exists( 'altijara_parse_issue_number_from_text' )
		? altijara_parse_issue_number_from_text( $stem )
		: 0;
	if ( $parsed_num ) {
		$num = (string) $parsed_num;
		$patterns = array(
			'%' . $wpdb->esc_like( 'Issue-' . $num ) . '%',
			'%' . $wpdb->esc_like( 'Issue-No.' . $num ) . '%',
			'%' . $wpdb->esc_like( 'No.' . $num ) . '%',
			'%' . $wpdb->esc_like( $num ) . '%',
		);
		foreach ( $patterns as $like ) {
			$found = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT p.ID FROM {$wpdb->posts} p
					INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_wp_attached_file'
					WHERE p.post_type = 'attachment'
					AND p.post_mime_type LIKE 'image/%%'
					AND ( m.meta_value LIKE %s OR p.post_title LIKE %s OR p.guid LIKE %s OR p.post_name LIKE %s )
					ORDER BY p.ID DESC
					LIMIT 1",
					$like,
					$like,
					$like,
					$like
				)
			);
			if ( $found ) {
				return (int) $found;
			}
		}
	}

	return 0;
}

/**
 * Find existing issue post by number meta.
 *
 * @param int $number Issue number.
 * @return int Post ID or 0.
 */
function altijara_find_issue_by_number( $number ) {
	$number = absint( $number );
	if ( ! $number ) {
		return 0;
	}
	$q = new WP_Query(
		array(
			'post_type'      => 'issue',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => '_altijara_issue_number',
					'value' => (string) $number,
				),
			),
			'no_found_rows'  => true,
		)
	);
	return ! empty( $q->posts[0] ) ? (int) $q->posts[0] : 0;
}

/**
 * Ingest / sync issues from theme JSON into CPT + featured images.
 *
 * @param bool $overwrite_meta When true, overwrite existing meta/title/excerpt.
 * @return array{created:int,updated:int,thumbs:int,skipped:int,errors:string[]}
 */
function altijara_ingest_issues_from_json( $overwrite_meta = true ) {
	$report = array(
		'created' => 0,
		'updated' => 0,
		'thumbs'  => 0,
		'skipped' => 0,
		'errors'  => array(),
	);

	$path = trailingslashit( ALTIJARA_THEME_DIR ) . 'assets/data/issues_data.json';
	if ( ! file_exists( $path ) ) {
		$report['errors'][] = 'issues_data.json missing';
		return $report;
	}

	$raw = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_array( $raw ) ) {
		$report['errors'][] = 'issues_data.json invalid';
		return $report;
	}

	foreach ( $raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$number = isset( $row['number'] ) ? absint( $row['number'] ) : 0;
		$title  = isset( $row['title'] ) ? sanitize_text_field( (string) $row['title'] ) : '';
		if ( ! $number || ! $title ) {
			$report['skipped']++;
			continue;
		}

		$excerpt = isset( $row['excerpt'] ) ? sanitize_textarea_field( (string) $row['excerpt'] ) : '';
		$parsed  = altijara_parse_issue_date_label( isset( $row['date'] ) ? (string) $row['date'] : '' );
		$year    = isset( $row['year'] ) ? absint( $row['year'] ) : $parsed['year'];
		$month   = $parsed['month'];
		$day     = 1;
		$post_date = sprintf( '%04d-%02d-%02d 12:00:00', $year, $month, $day );

		$existing_id = altijara_find_issue_by_number( $number );
		$postarr     = array(
			'post_type'    => 'issue',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_excerpt' => $excerpt,
			'post_content' => $excerpt,
			'post_date'    => $post_date,
			'post_date_gmt'=> get_gmt_from_date( $post_date ),
		);

		if ( $existing_id ) {
			if ( $overwrite_meta ) {
				$postarr['ID'] = $existing_id;
				$res           = wp_update_post( $postarr, true );
				if ( is_wp_error( $res ) ) {
					$report['errors'][] = $res->get_error_message();
					continue;
				}
				$report['updated']++;
			} else {
				$report['skipped']++;
			}
			$post_id = $existing_id;
		} else {
			$res = wp_insert_post( $postarr, true );
			if ( is_wp_error( $res ) || ! $res ) {
				$report['errors'][] = is_wp_error( $res ) ? $res->get_error_message() : 'insert failed #' . $number;
				continue;
			}
			$post_id = (int) $res;
			$report['created']++;
		}

		// Meta.
		update_post_meta( $post_id, '_altijara_issue_number', (string) $number );
		if ( ! empty( $row['flipbook'] ) ) {
			update_post_meta( $post_id, '_altijara_flipbook_url', esc_url_raw( (string) $row['flipbook'] ) );
		} elseif ( ! empty( $row['link'] ) ) {
			update_post_meta( $post_id, '_altijara_flipbook_url', esc_url_raw( (string) $row['link'] ) );
		}
		if ( ! empty( $row['pdfLink'] ) && preg_match( '/\.pdf(\?|#|$)/i', (string) $row['pdfLink'] ) ) {
			$pdf_url = function_exists( 'altijara_normalize_pdf_url' )
				? altijara_normalize_pdf_url( (string) $row['pdfLink'] )
				: esc_url_raw( (string) $row['pdfLink'] );
			if ( $pdf_url ) {
				update_post_meta( $post_id, '_altijara_pdf_url', $pdf_url );
			}
		}
		if ( ! empty( $row['pages'] ) ) {
			update_post_meta( $post_id, '_altijara_pages', (string) absint( $row['pages'] ) );
		}
		if ( ! empty( $row['tag'] ) ) {
			update_post_meta( $post_id, '_altijara_issue_tag', sanitize_text_field( (string) $row['tag'] ) );
		}
		if ( ! empty( $row['tagColor'] ) ) {
			update_post_meta( $post_id, '_altijara_issue_tag_color', sanitize_text_field( (string) $row['tagColor'] ) );
		}

		// Featured image: match Media Library first; sideload remote cover only when missing.
		$cover_url = ! empty( $row['cover'] ) ? (string) $row['cover'] : ( ! empty( $row['src'] ) ? (string) $row['src'] : '' );
		$att_id    = $cover_url ? altijara_find_media_attachment_for_cover( $cover_url ) : 0;
		if ( ! $att_id && $number ) {
			$att_id = altijara_find_media_attachment_for_cover( 'Issue-' . $number . '.png' );
		}
		if ( ! $att_id && ! has_post_thumbnail( $post_id ) && $cover_url && preg_match( '#^https?://#i', $cover_url ) ) {
			$att_id = altijara_sideload_issue_cover( $cover_url, $post_id );
		}
		if ( $att_id && ( ! has_post_thumbnail( $post_id ) || (int) get_post_thumbnail_id( $post_id ) !== (int) $att_id ) ) {
			set_post_thumbnail( $post_id, $att_id );
			$report['thumbs']++;
		}
	}

	if ( function_exists( 'altijara_cache_purge_all' ) ) {
		altijara_cache_purge_all();
	}

	return $report;
}
