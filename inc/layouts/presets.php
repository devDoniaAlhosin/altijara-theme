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
			'orderby'      => 'issue_number',
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
			'label'        => '',
			'visible'      => true,
			'category'     => '',
			'tag'          => 0,
			'manual_posts' => array(),
			'issue_ids'    => array(),
			'count'        => 24,
			'offset'       => 1,
			'orderby'      => 'issue_number',
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
			'label'        => '',
			'visible'      => true,
			'category'     => '',
			'tag'          => 0,
			'manual_posts' => array(),
			'issue_ids'    => array(),
			'count'        => 24,
			'offset'       => 0,
			'orderby'      => 'issue_number',
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
			'label'        => '',
			'visible'      => true,
			'category'     => '',
			'tag'          => 0,
			'manual_posts' => array(),
			'issue_ids'    => array(),
			'count'        => 24,
			'offset'       => 0,
			'orderby'      => 'issue_number',
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
 * Blank category-archive module, used when a block is added in the editor.
 *
 * @param string $widget Widget id.
 * @param string $label  Editor label.
 * @return array<string, mixed>
 */
function altijara_category_archive_widget_section( $widget, $label = '' ) {
	$widget = sanitize_key( $widget );
	$label  = '' !== $label ? $label : $widget;
	$row    = array(
		'zone'          => 'body',
		'widget'        => $widget,
		'layout'        => function_exists( 'altijara_widget_to_layout' ) ? altijara_widget_to_layout( $widget ) : $widget,
		'label'         => $label,
		'visible'       => true,
		'category'      => '',
		'tag'           => 0,
		'manual_posts'  => array(),
		'issue_ids'     => array(),
		'count'         => 4,
		'offset'        => 0,
		'orderby'       => 'date',
		'order'         => 'DESC',
		'variant'       => 'default',
		'source'        => 'posts',
		'view_period'   => 'all',
		'pair_with'     => '',
		'band_min'      => 0,
		'band_max'      => 0,
		'sidebar_order' => '',
	);
	if ( 'cat_archive_hero' === $widget ) {
		$row['count'] = 3; // Recommended beside the hero (sidebar_count).
		$row['label'] = $label;
	} elseif ( 'cat_archive_featured' === $widget ) {
		$row['count'] = 8;
	} elseif ( 'cat_archive_news' === $widget ) {
		$row['count'] = 10;
	} elseif ( 'cat_archive_lower' === $widget ) {
		$row['count']         = 7;
		$row['sidebar_order'] = 'ad,magazine,most_read';
	} elseif ( 'cat_sidebar_ad' === $widget ) {
		$row['count']        = 1;
		$row['label']        = __( 'Advertisement', 'altijara' );
		// Leave creative empty so the Advertisement card controls the front end
		// (theme fallback image is applied only when shortcode + image are blank).
		$row['ad_shortcode'] = '';
		$row['ad_image']     = '';
		$row['ad_url']       = '';
	} elseif ( 'cat_sidebar_magazine' === $widget ) {
		$row['count'] = 8;
		$row['label'] = __( 'من إصدارات المجلة', 'altijara' );
	} elseif ( 'cat_sidebar_most_read' === $widget || 'article_sidebar_most_read' === $widget ) {
		$row['count'] = 7;
		$row['label'] = __( 'الأكثر قراءة', 'altijara' );
	} elseif ( 'article_sidebar_latest' === $widget ) {
		$row['count'] = 5;
		$row['label'] = __( 'أحدث الأخبار', 'altijara' );
	}
	if ( function_exists( 'altijara_widget_supports' ) && altijara_widget_supports( $widget, 'article_source' ) ) {
		$row['category_source'] = 'same';
		$row['categories']      = array();
		$row['category_filter'] = 'include';
	}
	return $row;
}

/**
 * Preset for the category archive blocks.
 *
 * Used when Category Archives has no saved layout.
 *
 * @return array{sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_layout_preset_category_archive() {
	$hero     = altijara_category_archive_widget_section( 'cat_archive_hero', __( 'Hero and recommended', 'altijara' ) );
	$featured = altijara_category_archive_widget_section( 'cat_archive_featured', __( 'Featured cards', 'altijara' ) );
	$news     = altijara_category_archive_widget_section( 'cat_archive_news', __( 'أحدث الأخبار', 'altijara' ) );
	$featured['offset'] = 4;
	$news['offset']     = 12;

	$sections = array(
		'cat_archive_hero'     => $hero,
		'cat_archive_featured' => $featured,
		'cat_archive_news'     => $news,
	);

	return array(
		'sections' => $sections,
		'order'    => array_keys( $sections ),
	);
}

/**
 * Default lower sidebar: advertisement, magazine slider, most read.
 *
 * @return array{visible:bool,sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_layout_preset_category_sidebar() {
	$ad  = altijara_category_archive_widget_section( 'cat_sidebar_ad', __( 'Advertisement', 'altijara' ) );
	$mag = altijara_category_archive_widget_section( 'cat_sidebar_magazine', __( 'من إصدارات المجلة', 'altijara' ) );
	$mr  = altijara_category_archive_widget_section( 'cat_sidebar_most_read', __( 'الأكثر قراءة', 'altijara' ) );
	$sections = array(
		'cat_sidebar_ad'        => $ad,
		'cat_sidebar_magazine'  => $mag,
		'cat_sidebar_most_read' => $mr,
	);
	$order = array( 'cat_sidebar_ad', 'cat_sidebar_magazine', 'cat_sidebar_most_read' );
	if ( function_exists( 'altijara_get_archive_sidebar' ) ) {
		$saved = altijara_get_archive_sidebar();
		$map   = array(
			'ad'        => 'cat_sidebar_ad',
			'magazine'  => 'cat_sidebar_magazine',
			'most_read' => 'cat_sidebar_most_read',
		);
		$next = array();
		if ( ! empty( $saved['order'] ) && is_array( $saved['order'] ) ) {
			foreach ( $saved['order'] as $key ) {
				$key = sanitize_key( (string) $key );
				if ( isset( $map[ $key ] ) ) {
					$next[] = $map[ $key ];
				}
			}
		}
		if ( $next ) {
			$order = $next;
		}
		$visible = ! empty( $saved['visible'] );
	} else {
		$visible = true;
	}

	return array(
		'visible'  => $visible,
		'sections' => $sections,
		'order'    => $order,
	);
}

/**
 * Default search results sidebar: advertisement, magazine slider, most read.
 *
 * @return array{visible:bool,sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_layout_preset_search_sidebar() {
	$ad  = altijara_category_archive_widget_section( 'cat_sidebar_ad', __( 'Advertisement', 'altijara' ) );
	$mag = altijara_category_archive_widget_section( 'cat_sidebar_magazine', __( 'من إصدارات المجلة', 'altijara' ) );
	$mr  = altijara_category_archive_widget_section( 'cat_sidebar_most_read', __( 'الأكثر قراءة', 'altijara' ) );

	$sections = array(
		'search_side_ad'       => $ad,
		'search_side_magazine' => $mag,
		'search_side_most'     => $mr,
	);

	return array(
		'visible'  => true,
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

	if ( 'category_archive' === $surface ) {
		return array(
			'cards'  => array(),
			'blocks' => array(
				array(
					'widget'  => 'cat_archive_hero',
					'variant' => 'default',
					'label'   => __( 'Hero and recommended', 'altijara' ),
					'desc'    => __( 'Top hero, with the recommended list or full width.', 'altijara' ),
				),
				array(
					'widget'  => 'cat_archive_featured',
					'variant' => 'default',
					'label'   => __( 'Featured cards', 'altijara' ),
					'desc'    => __( 'Four cards across, one to three rows.', 'altijara' ),
				),
				array(
					'widget'  => 'cat_archive_news',
					'variant' => 'default',
					'label'   => __( 'أحدث الأخبار', 'altijara' ),
					'desc'    => __( 'Latest news list and pagination.', 'altijara' ),
				),
			),
		);
	}

	if ( 'article_body' === $surface ) {
		return array(
			'cards'  => array(),
			'blocks' => array(
				array(
					'widget'  => 'article_related',
					'variant' => 'default',
					'label'   => __( 'اقرأ ايضاً', 'altijara' ),
					'desc'    => __( 'Related articles under the story. Set the title, source, and how many show first.', 'altijara' ),
				),
				array(
					'widget'  => 'article_grid',
					'variant' => 'default',
					'label'   => __( 'Card grid', 'altijara' ),
					'desc'    => __( 'A row of article cards. Set the title, source, and count.', 'altijara' ),
				),
				array(
					'widget'  => 'article_list',
					'variant' => 'default',
					'label'   => __( 'Article list', 'altijara' ),
					'desc'    => __( 'A list of articles. Set the title, source, and how many.', 'altijara' ),
				),
				array(
					'widget'  => 'most_read',
					'variant' => 'default',
					'label'   => __( 'الأكثر قراءة', 'altijara' ),
					'desc'    => __( 'Ranked list. Set the title, source, and posts.', 'altijara' ),
				),
				array(
					'widget'  => 'article_sidebar_latest',
					'variant' => 'default',
					'label'   => __( 'أحدث الأخبار', 'altijara' ),
					'desc'    => __( 'Latest posts. Set the title, source, and how many.', 'altijara' ),
				),
			),
		);
	}

	if ( 'article_sidebar' === $surface ) {
		return array(
			'cards'  => array(),
			'blocks' => array(
				array(
					'widget'  => 'article_sidebar_latest',
					'variant' => 'default',
					'label'   => __( 'أحدث الأخبار', 'altijara' ),
					'desc'    => __( 'Latest posts. Set the title, source, and how many.', 'altijara' ),
				),
				array(
					'widget'  => 'cat_sidebar_ad',
					'variant' => 'default',
					'label'   => __( 'Advertisement', 'altijara' ),
					'desc'    => __( 'Sidebar ad: shortcode, fallback image, and link — configured on this widget.', 'altijara' ),
				),
				array(
					'widget'  => 'cat_sidebar_magazine',
					'variant' => 'default',
					'label'   => __( 'Magazine issues slider', 'altijara' ),
					'desc'    => __( 'Cover slider. Set the title and how many covers to load.', 'altijara' ),
				),
				array(
					'widget'  => 'article_sidebar_most_read',
					'variant' => 'default',
					'label'   => __( 'الأكثر قراءة', 'altijara' ),
					'desc'    => __( 'Ranked list. Set the title, source, and posts.', 'altijara' ),
				),
			),
		);
	}

	if ( 'category_sidebar' === $surface || 'search_sidebar' === $surface || 'tag_sidebar' === $surface ) {
		return array(
			'cards'  => array(),
			'blocks' => array(
				array(
					'widget'  => 'cat_sidebar_ad',
					'variant' => 'default',
					'label'   => __( 'Advertisement', 'altijara' ),
					'desc'    => __( 'Sidebar ad: shortcode, fallback image, and link — configured on this widget.', 'altijara' ),
				),
				array(
					'widget'  => 'cat_sidebar_magazine',
					'variant' => 'default',
					'label'   => __( 'Magazine issues slider', 'altijara' ),
					'desc'    => __( 'Cover slider. Set the title and how many covers to load.', 'altijara' ),
				),
				array(
					'widget'  => 'cat_sidebar_most_read',
					'variant' => 'default',
					'label'   => __( 'الأكثر قراءة', 'altijara' ),
					'desc'    => __( 'Ranked list. Set the title, source, and posts.', 'altijara' ),
				),
			),
		);
	}

	// Issues surface.
	return array(
		'cards'  => array(),
		'blocks' => array(
			array(
				'widget'  => 'issues_lead',
				'variant' => 'default',
				'label'   => __( 'Issues lead hero', 'altijara' ),
				'desc'    => __( 'Auto-latest by issue number when Specific issues is empty.', 'altijara' ),
			),
			array(
				'widget'  => 'issues_carousel',
				'variant' => 'default',
				'label'   => __( 'Issues carousel band', 'altijara' ),
				'desc'    => __( 'Horizontal covers by issue number; use offset 1 to skip the lead.', 'altijara' ),
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
				'desc'    => __( 'Card grid of posts or magazine issues.', 'altijara' ),
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
