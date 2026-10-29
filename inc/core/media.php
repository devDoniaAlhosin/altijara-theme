<?php
/**
 * Media, dates, and post DTO helpers.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Locale-aware short date.
 *
 * Arabic locales: "16 سبتمبر 2026". Otherwise: date_i18n medium date for the active locale
 * (e.g. English under TranslatePress).
 *
 * @param int|string|null $date Post ID, timestamp, or date string.
 */
function altijara_format_date_ar( $date = null ) {
	if ( null === $date ) {
		$ts = current_time( 'timestamp' );
	} elseif ( is_numeric( $date ) && (int) $date > 200000000 ) {
		$ts = (int) $date;
	} elseif ( is_numeric( $date ) ) {
		$ts = (int) get_post_time( 'U', true, (int) $date );
	} else {
		$ts = strtotime( (string) $date );
	}

	if ( ! $ts ) {
		return '';
	}

	$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
	$is_ar  = ( 0 === strpos( strtolower( str_replace( '_', '-', (string) $locale ) ), 'ar' ) );

	if ( ! $is_ar ) {
		$format = get_option( 'date_format' );
		return date_i18n( $format ? $format : 'j F Y', $ts );
	}

	$months = array(
		1  => 'يناير',
		2  => 'فبراير',
		3  => 'مارس',
		4  => 'أبريل',
		5  => 'مايو',
		6  => 'يونيو',
		7  => 'يوليو',
		8  => 'أغسطس',
		9  => 'سبتمبر',
		10 => 'أكتوبر',
		11 => 'نوفمبر',
		12 => 'ديسمبر',
	);

	$day   = (int) wp_date( 'j', $ts );
	$month = (int) wp_date( 'n', $ts );
	$year  = (int) wp_date( 'Y', $ts );

	return sprintf( '%d %s %d', $day, $months[ $month ] ?? '', $year );
}

/**
 * Trim text at word boundary.
 *
 * @param string $text    Source text.
 * @param int    $max_len Max length.
 */
function altijara_trim_at_word( $text, $max_len = 160 ) {
	$text = wp_strip_all_tags( (string) $text );
	if ( mb_strlen( $text ) <= $max_len ) {
		return $text;
	}
	$cut        = mb_substr( $text, 0, $max_len );
	$last_space = mb_strrpos( $cut, ' ' );
	if ( false !== $last_space && $last_space > 40 ) {
		$cut = mb_substr( $cut, 0, $last_space );
	}
	return rtrim( $cut ) . '...';
}

/**
 * Missing-image placeholder: site logo on the salmon field used by the old cover fallback.
 *
 * @return string
 */
function altijara_fallback_thumb_url() {
	return altijara_asset( 'assets/images/thumb-logo-fallback.png' );
}

/**
 * Featured image URL with fallback.
 *
 * @param int         $post_id Post ID.
 * @param string|int[] $size    Image size.
 */
function altijara_get_thumb_url( $post_id, $size = 'large' ) {
	$url = get_the_post_thumbnail_url( $post_id, $size );
	if ( $url ) {
		return $url;
	}
	if ( function_exists( 'altijara_get_video_provider_thumb_url' ) ) {
		$video_thumb = altijara_get_video_provider_thumb_url( (int) $post_id );
		if ( $video_thumb ) {
			return $video_thumb;
		}
	}
	return altijara_fallback_thumb_url();
}

/**
 * Section title. An empty saved title stays empty.
 *
 * @param mixed  $config   Section config.
 * @param string $fallback Used only when the section has no label key.
 * @return string
 */
function altijara_section_heading( $config, $fallback = '' ) {
	if ( is_array( $config ) && array_key_exists( 'label', $config ) ) {
		return (string) $config['label'];
	}
	return (string) $fallback;
}

/**
 * Inline chevron SVG for pagination / controls (replaces Font Awesome).
 *
 * @param string $direction 'left' or 'right'.
 * @return string Safe SVG markup.
 */
function altijara_chevron_svg( $direction = 'left' ) {
	$flip  = ( 'right' === $direction );
	$style = $flip ? ' style="transform:scaleX(-1)"' : '';
	return '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"' . $style . '><path d="M15 6l-6 6 6 6"></path></svg>';
}

/**
 * Echo a responsive featured image (srcset via WP) or theme fallback.
 *
 * @param int                  $post_id Post ID.
 * @param string|int[]         $size    Image size (prefer medium / medium_large for lists).
 * @param array<string,string> $attr    Extra img attributes (loading, fetchpriority, class, sizes, alt).
 */
function altijara_the_thumb_img( $post_id, $size = 'medium_large', $attr = array() ) {
	$post_id  = (int) $post_id;
	$defaults = array(
		'alt'      => $post_id ? get_the_title( $post_id ) : '',
		'loading'  => 'lazy',
		'decoding' => 'async',
	);
	$attr = wp_parse_args( $attr, $defaults );

	$thumb_id = $post_id ? (int) get_post_thumbnail_id( $post_id ) : 0;
	if ( $thumb_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image escapes.
		echo wp_get_attachment_image( $thumb_id, $size, false, $attr );
		return;
	}

	$src   = '';
	$class = isset( $attr['class'] ) ? (string) $attr['class'] : '';
	if ( function_exists( 'altijara_get_video_provider_thumb_url' ) ) {
		$src = (string) altijara_get_video_provider_thumb_url( $post_id );
		if ( $src ) {
			$class = trim( $class . ' altijara-thumb-video' );
		}
	}
	if ( ! $src ) {
		$src   = altijara_fallback_thumb_url();
		$class = trim( $class . ' altijara-thumb-fallback' );
	}
	$html = sprintf(
		'<img src="%s" alt="%s" class="%s"',
		esc_url( $src ),
		esc_attr( isset( $attr['alt'] ) ? (string) $attr['alt'] : '' ),
		esc_attr( $class )
	);
	foreach ( array( 'loading', 'decoding', 'fetchpriority', 'sizes' ) as $key ) {
		if ( ! empty( $attr[ $key ] ) ) {
			$html .= sprintf( ' %s="%s"', $key, esc_attr( (string) $attr[ $key ] ) );
		}
	}
	$html .= '>';
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_* above.
}

/**
 * Normalize a post into a simple array for templates/JS bridges.
 *
 * @param WP_Post|int $post Post object or ID.
 * @return array<string, mixed>|null
 */
function altijara_normalize_post( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}

	$permalink = get_permalink( $post );

	return array(
		'id'       => (int) $post->ID,
		'title'    => get_the_title( $post ),
		'excerpt'  => altijara_trim_at_word( $post->post_excerpt ?: $post->post_content, 180 ),
		'url'      => $permalink,
		'link'     => $permalink,
		'img'      => altijara_get_thumb_url( $post->ID ),
		'dateText' => altijara_format_date_ar( $post->ID ),
		'timeText' => altijara_format_date_ar( $post->ID ),
		'category' => altijara_get_primary_category_name( $post->ID ),
		'slug'     => $post->post_name,
		'hasVideo' => function_exists( 'altijara_has_featured_video' ) ? altijara_has_featured_video( $post->ID ) : false,
	);
}
