<?php
/**
 * Widget Registry — reusable homepage widget types.
 *
 * Maps widget IDs → templates under template-parts/home/, supported settings,
 * query strategies, and optional layout variants. No redesign; preserves UI.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical widget type definitions.
 *
 * @return array<string, array<string, mixed>>
 */
function altijara_get_widget_registry() {
	static $registry = null;
	if ( null !== $registry ) {
		return $registry;
	}

	$registry = array(
		'chamber_column'  => array(
			'label'    => __( 'عمود أخبار الغرفة', 'altijara' ),
			'template' => 'template-parts/home/section-chamber-column',
			'query'    => 'posts',
			'supports' => array( 'visible', 'label', 'category', 'tag', 'manual_posts', 'count', 'offset', 'orderby', 'order', 'cache' ),
			'variants' => array(),
		),
		'magazine_slider' => array(
			'label'    => __( 'سلايدر الغلاف', 'altijara' ),
			'template' => 'template-parts/home/section-magazine-slider',
			'query'    => 'issues', // respects section source: posts|issues
			'supports' => array( 'visible', 'label', 'category', 'manual_posts', 'count', 'offset', 'orderby', 'order', 'issue_ids', 'source', 'cache' ),
			'variants' => array(),
		),
		'article_list'    => array(
			'label'    => __( 'قائمة مقالات', 'altijara' ),
			'template' => 'template-parts/home/section-interviews-column',
			'query'    => 'posts',
			'supports' => array( 'visible', 'label', 'category', 'tag', 'manual_posts', 'count', 'offset', 'orderby', 'order', 'cache' ),
			'variants' => array(),
		),
		'hero_featured'   => array(
			'label'    => __( 'مقالة هيرو بارزة', 'altijara' ),
			'template' => 'template-parts/home/section-personality-card',
			'query'    => 'posts',
			'supports' => array( 'visible', 'label', 'category', 'tag', 'manual_posts', 'count', 'offset', 'orderby', 'order', 'issue_ids', 'source', 'cache' ),
			'variants' => array(),
		),
		'lead_stack'      => array(
			'label'    => __( 'بطاقة + قائمة', 'altijara' ),
			'template' => 'template-parts/home/section-logistics-stack',
			'query'    => 'posts',
			'supports' => array( 'visible', 'label', 'category', 'tag', 'manual_posts', 'count', 'offset', 'orderby', 'order', 'cache' ),
			'variants' => array(),
		),
		'article_grid'    => array(
			'label'    => __( 'شبكة بطاقات', 'altijara' ),
			'template' => 'template-parts/home/section-news-grid',
			'query'    => 'posts',
			'supports' => array( 'visible', 'label', 'category', 'tag', 'manual_posts', 'count', 'offset', 'orderby', 'order', 'variant', 'source', 'band', 'cache' ),
			'variants' => array(
				'default'  => array(
					'label'    => __( 'شبكة 4 أعمدة', 'altijara' ),
					'template' => 'template-parts/home/section-news-grid',
				),
				'cars_row' => array(
					'label'    => __( 'صف بطاقات (تقنية)', 'altijara' ),
					'template' => 'template-parts/home/section-tech-cards',
				),
			),
		),
		'split_sector'    => array(
			'label'    => __( 'عمود قطاعي (lead + sub)', 'altijara' ),
			'template' => 'template-parts/home/section-split-sector',
			'query'    => 'posts',
			'supports' => array( 'visible', 'label', 'category', 'tag', 'manual_posts', 'count', 'offset', 'orderby', 'order', 'cache' ),
			'variants' => array(),
		),
		'most_read'       => array(
			'label'    => __( 'الأكثر قراءة', 'altijara' ),
			'template' => 'template-parts/home/section-most-read',
			'query'    => 'most_read',
			'supports' => array( 'visible', 'label', 'category', 'tag', 'manual_posts', 'count', 'view_period', 'cache' ),
			'variants' => array(),
		),
		'issues_lead'     => array(
			'label'    => __( 'غلاف العدد الأحدث', 'altijara' ),
			'template' => 'template-parts/widgets/section-issues-lead',
			'query'    => 'issues',
			'supports' => array( 'visible', 'label', 'offset', 'issue_ids', 'source', 'cache' ),
			'variants' => array(),
		),
		'issues_carousel' => array(
			'label'    => __( 'شريط أعداد المجلة', 'altijara' ),
			'template' => 'template-parts/widgets/section-issues-carousel',
			'query'    => 'issues',
			'supports' => array( 'visible', 'label', 'count', 'offset', 'issue_ids', 'source', 'band', 'cache' ),
			'variants' => array(),
		),
		'cat_archive_hero' => array(
			'label'    => __( 'البطل والمقالات المقترحة', 'altijara' ),
			'template' => 'template-parts/widgets/section-cat-archive-hero',
			'query'    => 'posts',
			// Full-width is the Category Archives checklist; posts always come from the viewed category.
			'supports' => array( 'visible', 'label', 'label_admin_only', 'sidebar_count', 'orderby', 'order' ),
			'variants' => array(
				'default' => array(
					'label' => __( 'Hero with recommended list', 'altijara' ),
				),
				'full'    => array(
					'label' => __( 'Full-width hero', 'altijara' ),
				),
			),
		),
		'cat_archive_featured' => array(
			'label'    => __( 'بطاقات مميزة', 'altijara' ),
			'template' => 'template-parts/widgets/section-cat-archive-featured',
			'query'    => 'posts',
			'supports' => array( 'visible', 'label', 'label_admin_only', 'featured_rows', 'offset', 'orderby', 'order' ),
			'variants' => array(),
		),
		'cat_archive_news' => array(
			'label'    => __( 'أحدث الأخبار', 'altijara' ),
			'template' => 'template-parts/widgets/section-cat-archive-news',
			'query'    => 'posts',
			'supports' => array( 'visible', 'label', 'news_size', 'offset', 'orderby', 'order' ),
			'variants' => array(),
		),
		'cat_archive_lower' => array(
			'label'    => __( 'العمود السفلي', 'altijara' ),
			'template' => 'template-parts/widgets/section-cat-archive-lower',
			'query'    => 'most_read',
			// Shared category template — always scopes to the viewed category.
			'supports' => array( 'visible', 'label', 'count', 'view_period', 'lower_order' ),
			'variants' => array(),
		),
		'cat_sidebar_ad' => array(
			'label'    => __( 'إعلان', 'altijara' ),
			'template' => 'template-parts/widgets/section-cat-sidebar-ad',
			'query'    => 'posts',
			'supports' => array( 'visible', 'ad_creative' ),
			'variants' => array(),
		),
		'cat_sidebar_magazine' => array(
			'label'    => __( 'إصدارات المجلة', 'altijara' ),
			'template' => 'template-parts/widgets/section-cat-sidebar-magazine',
			'query'    => 'posts',
			'supports' => array( 'visible', 'label', 'count', 'band', 'order' ),
			'variants' => array(),
		),
		'cat_sidebar_most_read' => array(
			'label'    => __( 'الأكثر قراءة', 'altijara' ),
			'template' => 'template-parts/widgets/section-cat-sidebar-most-read',
			'query'    => 'most_read',
			// Optional category: empty inherits viewed archive category, or sitewide on search/tag.
			'supports' => array( 'visible', 'label', 'category', 'count', 'view_period' ),
			'variants' => array(),
		),
		'article_sidebar_latest' => array(
			'label'    => __( 'أحدث الأخبار', 'altijara' ),
			'template' => 'template-parts/widgets/section-article-sidebar-latest',
			'query'    => 'posts',
			'supports' => array( 'visible', 'label', 'article_source', 'category', 'manual_posts', 'count' ),
			'variants' => array(),
		),
		'article_sidebar_most_read' => array(
			'label'    => __( 'الأكثر قراءة', 'altijara' ),
			'template' => 'template-parts/widgets/section-cat-sidebar-most-read',
			'query'    => 'most_read',
			'supports' => array( 'visible', 'label', 'article_source', 'category', 'manual_posts', 'count', 'view_period' ),
			'variants' => array(),
		),
		'article_related' => array(
			'label'    => __( 'اقرأ ايضاً', 'altijara' ),
			'template' => 'template-parts/article/section-related',
			'query'    => 'posts',
			'supports' => array( 'visible', 'label', 'article_source', 'category', 'manual_posts', 'tag', 'count', 'per_load', 'button_label' ),
			'variants' => array(),
		),
	);

	/**
	 * Filter registered Altijara widgets.
	 *
	 * @param array<string, array<string, mixed>> $registry Registry.
	 */
	$registry = apply_filters( 'altijara_widget_registry', $registry );

	return $registry;
}

/**
 * Legacy layout slug → widget ID.
 *
 * @param string $layout Layout key from Phase 2.
 * @return string
 */
function altijara_layout_to_widget( $layout ) {
	$map = array(
		'chamber_column'    => 'chamber_column',
		'magazine_slider'   => 'magazine_slider',
		'interviews_column' => 'article_list',
		'personality_card'  => 'hero_featured',
		'logistics_stack'   => 'lead_stack',
		'news_grid'         => 'article_grid',
		'split_sector'      => 'split_sector',
		'most_read'         => 'most_read',
		'tech_cards'           => 'article_grid',
		'cat_archive_hero'     => 'cat_archive_hero',
		'cat_archive_featured' => 'cat_archive_featured',
		'cat_archive_news'     => 'cat_archive_news',
		'cat_archive_lower'    => 'cat_archive_lower',
		'cat_sidebar_ad'       => 'cat_sidebar_ad',
		'cat_sidebar_magazine' => 'cat_sidebar_magazine',
		'cat_sidebar_most_read'=> 'cat_sidebar_most_read',
		'article_sidebar_latest'    => 'article_sidebar_latest',
		'article_sidebar_most_read' => 'article_sidebar_most_read',
		'article_related'           => 'article_related',
	);
	$layout = sanitize_key( $layout );
	return isset( $map[ $layout ] ) ? $map[ $layout ] : 'article_grid';
}

/**
 * Widget ID → legacy layout slug (backward compatibility).
 *
 * @param string $widget Widget ID.
 * @return string
 */
function altijara_widget_to_layout( $widget ) {
	$map = array(
		'chamber_column'  => 'chamber_column',
		'magazine_slider' => 'magazine_slider',
		'article_list'    => 'interviews_column',
		'hero_featured'   => 'personality_card',
		'lead_stack'      => 'logistics_stack',
		'article_grid'    => 'news_grid',
		'split_sector'    => 'split_sector',
		'most_read'       => 'most_read',
		'issues_lead'          => 'issues_lead',
		'issues_carousel'      => 'issues_carousel',
		'cat_archive_hero'     => 'cat_archive_hero',
		'cat_archive_featured' => 'cat_archive_featured',
		'cat_archive_news'     => 'cat_archive_news',
		'cat_archive_lower'    => 'cat_archive_lower',
		'cat_sidebar_ad'       => 'cat_sidebar_ad',
		'cat_sidebar_magazine' => 'cat_sidebar_magazine',
		'cat_sidebar_most_read'=> 'cat_sidebar_most_read',
		'article_sidebar_latest'    => 'article_sidebar_latest',
		'article_sidebar_most_read' => 'article_sidebar_most_read',
		'article_related'           => 'article_related',
	);
	$widget = sanitize_key( $widget );
	return isset( $map[ $widget ] ) ? $map[ $widget ] : 'news_grid';
}

/**
 * @param string $widget_id Widget type.
 * @return array<string, mixed>|null
 */
function altijara_get_widget_definition( $widget_id ) {
	$registry = altijara_get_widget_registry();
	$id       = sanitize_key( $widget_id );
	return isset( $registry[ $id ] ) ? $registry[ $id ] : null;
}

/**
 * Whether a widget supports a settings field.
 *
 * @param string $widget_id Widget type.
 * @param string $field     Field key.
 */
function altijara_widget_supports( $widget_id, $field ) {
	$def = altijara_get_widget_definition( $widget_id );
	if ( ! $def || empty( $def['supports'] ) || ! is_array( $def['supports'] ) ) {
		return false;
	}
	return in_array( $field, $def['supports'], true );
}

/**
 * Whether a widget scopes posts by category (picker or viewed archive category).
 *
 * @param string $widget_id Widget type.
 * @return bool
 */
function altijara_widget_uses_category_scope( $widget_id ) {
	$widget_id = sanitize_key( (string) $widget_id );
	if ( altijara_widget_supports( $widget_id, 'category' ) ) {
		return true;
	}
	return in_array(
		$widget_id,
		array(
			'cat_archive_hero',
			'cat_archive_featured',
			'cat_archive_news',
			'cat_archive_lower',
			'cat_sidebar_most_read',
		),
		true
	);
}

/**
 * Resolve template path for a section config (widget + variant).
 *
 * @param array<string, mixed> $config Section config.
 * @return string Relative template path without .php.
 */
function altijara_widget_template_path( $config ) {
	$widget = ! empty( $config['widget'] ) ? sanitize_key( $config['widget'] ) : altijara_layout_to_widget( $config['layout'] ?? 'news_grid' );
	$def    = altijara_get_widget_definition( $widget );
	$path   = $def ? $def['template'] : 'template-parts/home/section-news-grid';

	$variant = ! empty( $config['variant'] ) ? sanitize_key( $config['variant'] ) : 'default';
	if ( $def && ! empty( $def['variants'][ $variant ]['template'] ) ) {
		$path = $def['variants'][ $variant ]['template'];
	}

	$full = ALTIJARA_THEME_DIR . '/' . $path . '.php';
	if ( ! file_exists( $full ) ) {
		return 'template-parts/home/section-news-grid';
	}
	return $path;
}

/**
 * Admin labels for widget select (locked slots still show type).
 *
 * @return array<string, string>
 */
function altijara_widget_choices() {
	$out = array();
	foreach ( altijara_get_widget_registry() as $id => $def ) {
		$out[ $id ] = $def['label'];
	}
	return $out;
}

/**
 * Legacy helper — layout labels for BC callers.
 *
 * @return array<string, string>
 */
function altijara_section_layouts() {
	$out = array();
	foreach ( altijara_get_widget_registry() as $id => $def ) {
		$out[ altijara_widget_to_layout( $id ) ] = $def['label'];
		// Also expose widget id as selectable layout key for old saves.
		$out[ $id ] = $def['label'];
	}
	return $out;
}
