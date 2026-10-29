<?php
/**
 * Single-article sidebar settings and queries.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
			$tpl = preg_replace( '#[^a-zA-Z0-9\-._~:/?\#\[\]@!$&\'()*+,;=%{}]#', '', $tpl );
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

	// Related defaults live on the اقرأ ايضاً widget. Keep stored theme values when
	// those fields are absent from POST (removed from the Article screen).
	$prev = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'article', array() ) : array();
	$prev = is_array( $prev ) ? $prev : array();

	$related_source = array_key_exists( 'related_source', $raw )
		? sanitize_key( (string) $raw['related_source'] )
		: ( isset( $prev['related_source'] ) ? sanitize_key( (string) $prev['related_source'] ) : 'same' );
	if ( ! in_array( $related_source, array( 'same', 'selected' ), true ) ) {
		$related_source = 'same';
	}
	$related_cats = array();
	if ( array_key_exists( 'related_category_ids', $raw ) && is_array( $raw['related_category_ids'] ) ) {
		foreach ( $raw['related_category_ids'] as $cid ) {
			$cid = absint( $cid );
			if ( $cid ) {
				$related_cats[] = $cid;
			}
		}
		$related_cats = array_values( array_unique( $related_cats ) );
	} elseif ( isset( $prev['related_category_ids'] ) && is_array( $prev['related_category_ids'] ) ) {
		$related_cats = array_values( array_unique( array_filter( array_map( 'absint', $prev['related_category_ids'] ) ) ) );
	}

	$more_label = array_key_exists( 'related_more_label', $raw )
		? sanitize_text_field( (string) $raw['related_more_label'] )
		: ( isset( $prev['related_more_label'] ) ? sanitize_text_field( (string) $prev['related_more_label'] ) : '' );
	$article_defs = ( isset( $defs['article'] ) && is_array( $defs['article'] ) ) ? $defs['article'] : array();
	$empty_default = isset( $article_defs['sidebar_empty_message'] )
		? (string) $article_defs['sidebar_empty_message']
		: 'لا توجد مقالات في تصنيف هذا المقال حالياً';
	$empty_msg = array_key_exists( 'sidebar_empty_message', $raw )
		? sanitize_text_field( (string) $raw['sidebar_empty_message'] )
		: ( isset( $prev['sidebar_empty_message'] ) ? sanitize_text_field( (string) $prev['sidebar_empty_message'] ) : $empty_default );
	if ( '' === trim( $empty_msg ) ) {
		$empty_msg = $empty_default;
	}

	$show_related = array_key_exists( 'show_related', $raw )
		? ! empty( $raw['show_related'] )
		: ( ! array_key_exists( 'show_related', $prev ) || ! empty( $prev['show_related'] ) );
	$related_count = array_key_exists( 'related_count', $raw )
		? max( 1, min( 12, absint( $raw['related_count'] ) ) )
		: ( isset( $prev['related_count'] ) ? max( 1, min( 12, absint( $prev['related_count'] ) ) ) : 2 );
	$related_per_load = array_key_exists( 'related_per_load', $raw )
		? max( 1, min( 12, absint( $raw['related_per_load'] ) ) )
		: ( isset( $prev['related_per_load'] ) ? max( 1, min( 12, absint( $prev['related_per_load'] ) ) ) : 2 );

	return array(
		'show_related'          => $show_related,
		'show_save'             => array_key_exists( 'show_save', $raw ) ? ! empty( $raw['show_save'] ) : true,
		'show_author'           => array_key_exists( 'show_author', $raw ) ? ! empty( $raw['show_author'] ) : true,
		'related_count'         => $related_count,
		'related_per_load'      => $related_per_load,
		'related_more_label'    => $more_label,
		'related_source'        => $related_source,
		'related_category_ids'  => $related_cats,
		'sidebar_empty_message' => $empty_msg,
		'share_platforms'  => $share,
		'share_extra'      => $share_extra,
		'latest'           => altijara_sanitize_article_sidebar_widget( $raw['latest'] ?? array(), 'latest' ),
		'most_read'        => altijara_sanitize_article_sidebar_widget( $raw['most_read'] ?? array(), 'most_read' ),
	);
}

/**
 * Empty-state copy for article sidebar list widgets (latest / most read).
 *
 * @return string
 */
function altijara_get_article_sidebar_empty_message() {
	$settings = altijara_get_article_settings();
	$msg      = isset( $settings['sidebar_empty_message'] ) ? trim( (string) $settings['sidebar_empty_message'] ) : '';
	if ( '' === $msg ) {
		return __( 'لا توجد مقالات في تصنيف هذا المقال حالياً', 'altijara' );
	}
	return $msg;
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
 * Preferred category ID for article card labels (sidebar / related).
 *
 * Uses admin “selected categories” when that source is active; otherwise the
 * article’s first category.
 *
 * @param int    $post_id Current article.
 * @param string $slot    latest|most_read|related.
 * @return int
 */
function altijara_article_preferred_category_id( $post_id, $slot = 'latest' ) {
	$post_id  = absint( $post_id );
	$settings = function_exists( 'altijara_get_article_settings' ) ? altijara_get_article_settings() : array();
	$cfg      = array();

	if ( 'related' === $slot ) {
		$cfg = array(
			'source'       => isset( $settings['related_source'] ) ? (string) $settings['related_source'] : 'same',
			'category_ids' => isset( $settings['related_category_ids'] ) && is_array( $settings['related_category_ids'] )
				? $settings['related_category_ids']
				: array(),
		);
	} elseif ( isset( $settings[ $slot ] ) && is_array( $settings[ $slot ] ) ) {
		$cfg = $settings[ $slot ];
	}

	$ids = altijara_resolve_article_sidebar_cats( $post_id, $cfg );
	return ! empty( $ids ) ? (int) $ids[0] : 0;
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
