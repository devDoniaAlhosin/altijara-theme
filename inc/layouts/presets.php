<?php
/**
 * Default layout presets (current hardcoded UIs as editable rows).
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Home layout preset from theme default sections.
 *
 * @return array{sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_layout_preset_home() {
	$sections = function_exists( 'altijara_default_sections' ) ? altijara_default_sections() : array();
	$order    = function_exists( 'altijara_default_body_order' ) ? altijara_default_body_order() : array();
	return array(
		'sections' => $sections,
		'order'    => $order,
	);
}

/**
 * Category archive body preset (modules below chrome).
 *
 * @param int $term_id Category term ID (injected as default category).
 * @return array{sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_layout_preset_category( $term_id = 0 ) {
	$term_id = absint( $term_id );
	$cat     = $term_id ? (string) $term_id : '';

	$sections = array(
		'cat_hero'     => array(
			'zone'         => 'body',
			'widget'       => 'hero_featured',
			'layout'       => 'personality_card',
			'label'        => __( 'أبرز مقال', 'altijara' ),
			'visible'      => true,
			'category'     => $cat,
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 1,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'view_period'  => 'all',
			'pair_with'    => '',
		),
		'cat_featured' => array(
			'zone'         => 'body',
			'widget'       => 'article_grid',
			'layout'       => 'news_grid',
			'label'        => __( 'مقالات مميزة', 'altijara' ),
			'visible'      => true,
			'category'     => $cat,
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 8,
			'offset'       => 1,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'view_period'  => 'all',
			'pair_with'    => '',
		),
		'cat_list'     => array(
			'zone'         => 'body',
			'widget'       => 'article_list',
			'layout'       => 'interviews_column',
			'label'        => __( 'أحدث الأخبار', 'altijara' ),
			'visible'      => true,
			'category'     => $cat,
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 10,
			'offset'       => 9,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'view_period'  => 'all',
			'pair_with'    => '',
		),
		'cat_most_read'=> array(
			'zone'         => 'body',
			'widget'       => 'most_read',
			'layout'       => 'most_read',
			'label'        => __( 'الأكثر قراءة', 'altijara' ),
			'visible'      => true,
			'category'     => $cat,
			'tag'          => 0,
			'manual_posts' => array(),
			'count'        => 7,
			'offset'       => 0,
			'orderby'      => 'comment_count',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'posts',
			'view_period'  => 'all',
			'pair_with'    => '',
		),
		'cat_magazine' => array(
			'zone'         => 'body',
			'widget'       => 'magazine_slider',
			'layout'       => 'magazine_slider',
			'label'        => __( 'من إصدارات المجلة', 'altijara' ),
			'visible'      => true,
			'category'     => '',
			'tag'          => 0,
			'manual_posts' => array(),
			'issue_ids'    => array(),
			'count'        => 6,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'issues',
			'view_period'  => 'all',
			'pair_with'    => '',
		),
	);

	return array(
		'sections' => $sections,
		'order'    => array_keys( $sections ),
	);
}

/**
 * Issues archive preset (lead + carousels).
 *
 * @return array{sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_layout_preset_issues() {
	$sections = array(
		'issues_lead'     => array(
			'zone'         => 'body',
			'widget'       => 'issues_lead',
			'layout'       => 'issues_lead',
			'label'        => __( 'العدد الأحدث', 'altijara' ),
			'visible'      => true,
			'category'     => '',
			'tag'          => 0,
			'manual_posts' => array(),
			'issue_ids'    => array(),
			'count'        => 1,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'issues',
			'view_period'  => 'all',
			'pair_with'    => '',
			'band_min'     => 0,
			'band_max'     => 0,
		),
		'issues_recent'   => array(
			'zone'         => 'body',
			'widget'       => 'issues_carousel',
			'layout'       => 'issues_carousel',
			'label'        => __( 'إصدارات مجلة التجارة — الأحدث', 'altijara' ),
			'visible'      => true,
			'category'     => '',
			'tag'          => 0,
			'manual_posts' => array(),
			'issue_ids'    => array(),
			'count'        => 24,
			'offset'       => 1,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'issues',
			'view_period'  => 'all',
			'pair_with'    => '',
			'band_min'     => 718,
			'band_max'     => 0,
		),
		'issues_mid'      => array(
			'zone'         => 'body',
			'widget'       => 'issues_carousel',
			'layout'       => 'issues_carousel',
			'label'        => __( 'إصدارات مجلة التجارة — أرشيف (704–717)', 'altijara' ),
			'visible'      => true,
			'category'     => '',
			'tag'          => 0,
			'manual_posts' => array(),
			'issue_ids'    => array(),
			'count'        => 24,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'issues',
			'view_period'  => 'all',
			'pair_with'    => '',
			'band_min'     => 704,
			'band_max'     => 717,
		),
		'issues_historic' => array(
			'zone'         => 'body',
			'widget'       => 'issues_carousel',
			'layout'       => 'issues_carousel',
			'label'        => __( 'إصدارات مجلة التجارة — أعداد سابقة', 'altijara' ),
			'visible'      => true,
			'category'     => '',
			'tag'          => 0,
			'manual_posts' => array(),
			'issue_ids'    => array(),
			'count'        => 24,
			'offset'       => 0,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'variant'      => 'default',
			'source'       => 'issues',
			'view_period'  => 'all',
			'pair_with'    => '',
			'band_min'     => 0,
			'band_max'     => 703,
		),
	);

	return array(
		'sections' => $sections,
		'order'    => array_keys( $sections ),
	);
}

/**
 * Palette for a surface (home / category / issues).
 *
 * @param string $surface Surface key.
 * @return array{cards:array,blocks:array}
 */
function altijara_layout_palette_for( $surface = 'home' ) {
	$base = function_exists( 'altijara_section_element_palette' ) ? altijara_section_element_palette() : array(
		'cards'  => array(),
		'blocks' => array(),
	);

	if ( 'home' === $surface ) {
		$base['blocks'][] = array(
			'widget'  => 'hero_featured',
			'variant' => 'default',
			'label'   => __( 'Featured hero card', 'altijara' ),
			'desc'    => __( 'Single large featured article.', 'altijara' ),
		);
		$base['blocks'][] = array(
			'widget'  => 'magazine_slider',
			'variant' => 'default',
			'label'   => __( 'Magazine cover slider', 'altijara' ),
			'desc'    => __( 'Issue covers or personality slides.', 'altijara' ),
		);
		return $base;
	}

	if ( 'category' === $surface ) {
		$base['blocks'][] = array(
			'widget'  => 'hero_featured',
			'variant' => 'default',
			'label'   => __( 'Featured hero card', 'altijara' ),
			'desc'    => __( 'Single large featured article.', 'altijara' ),
		);
		$base['blocks'][] = array(
			'widget'  => 'magazine_slider',
			'variant' => 'default',
			'label'   => __( 'Magazine cover slider', 'altijara' ),
			'desc'    => __( 'Issue covers carousel.', 'altijara' ),
		);
		return $base;
	}

	// Issues surface.
	return array(
		'cards'  => array(),
		'blocks' => array(
			array(
				'widget'  => 'issues_lead',
				'variant' => 'default',
				'label'   => __( 'Issues lead hero', 'altijara' ),
				'desc'    => __( 'Featured latest magazine issue.', 'altijara' ),
			),
			array(
				'widget'  => 'issues_carousel',
				'variant' => 'default',
				'label'   => __( 'Issues carousel band', 'altijara' ),
				'desc'    => __( 'Horizontal carousel of issue covers.', 'altijara' ),
			),
			array(
				'widget'  => 'magazine_slider',
				'variant' => 'default',
				'label'   => __( 'Magazine cover slider', 'altijara' ),
				'desc'    => __( 'Compact cover slider widget.', 'altijara' ),
			),
			array(
				'widget'  => 'article_grid',
				'variant' => 'default',
				'label'   => __( 'Card grid (4 columns)', 'altijara' ),
				'desc'    => __( 'News cards grid (posts).', 'altijara' ),
			),
			array(
				'widget'  => 'most_read',
				'variant' => 'default',
				'label'   => __( 'Most read', 'altijara' ),
				'desc'    => __( 'Ranked posts list.', 'altijara' ),
			),
		),
	);
}
