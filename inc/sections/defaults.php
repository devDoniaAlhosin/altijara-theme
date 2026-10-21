<?php
/**
 * Homepage sections — defaults and palette
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fixed hero section IDs (locked zone).
 *
 * @return string[]
 */
function altijara_hero_section_ids() {
	return array( 'hero_chamber', 'hero_magazine', 'hero_interviews', 'hero_personality', 'hero_logistics' );
}

/**
 * Strip legacy hero prefixes from labels.
 *
 * @param string $label Label.
 * @return string
 */
function altijara_strip_hero_label_prefix( $label ) {
	$label = (string) $label;
	$label = preg_replace( '/^هيرو\s*[—\-\x{2013}\x{2014}:：]\s*/u', '', $label );
	return is_string( $label ) ? trim( $label ) : '';
}

/**
 * Canonical homepage section IDs (hero + default body matrix).
 *
 * @return array<string, array<string, mixed>>
 */
function altijara_default_sections() {
	$base = array(
		'visible'      => true,
		'tag'          => 0,
		'manual_posts' => array(),
		'offset'       => 0,
		'orderby'      => 'date',
		'order'        => 'DESC',
		'variant'      => 'default',
		'source'       => 'posts',
		'issue_ids'    => array(),
		'view_period'  => 'all',
		'pair_with'    => '',
	);

	return array(
		'hero_chamber'     => array_merge( $base, array(
			'zone'     => 'hero',
			'widget'   => 'chamber_column',
			'layout'   => 'chamber_column',
			'label'    => 'أخبار الغرفة',
			'category' => 'chamber',
			'count'    => 7,
		) ),
		'hero_magazine'    => array_merge( $base, array(
			'zone'     => 'hero',
			'widget'   => 'magazine_slider',
			'layout'   => 'magazine_slider',
			'label'    => 'شخصية العدد',
			'category' => 'personality',
			'count'    => 6,
			'source'   => 'posts',
		) ),
		'hero_interviews'  => array_merge( $base, array(
			'zone'     => 'hero',
			'widget'   => 'article_list',
			'layout'   => 'interviews_column',
			'label'    => 'لقائات',
			'category' => 'interviews',
			'count'    => 3,
		) ),
		'hero_personality' => array_merge( $base, array(
			'zone'     => 'hero',
			'widget'   => 'hero_featured',
			'layout'   => 'personality_card',
			'label'    => 'شخصية العدد',
			'category' => 'personality',
			'count'    => 1,
		) ),
		'hero_logistics'   => array_merge( $base, array(
			'zone'     => 'hero',
			'widget'   => 'lead_stack',
			'layout'   => 'logistics_stack',
			'label'    => 'لوجستيات',
			'category' => 'logistics',
			'count'    => 3,
		) ),
		'followups'        => array_merge( $base, array(
			'zone'     => 'body',
			'widget'   => 'article_grid',
			'layout'   => 'news_grid',
			'label'    => 'متابعات',
			'category' => 'followups',
			'count'    => 4,
		) ),
		'business'         => array_merge( $base, array(
			'zone'      => 'body',
			'widget'    => 'split_sector',
			'layout'    => 'split_sector',
			'label'     => 'مال وأعمال',
			'category'  => 'business',
			'count'     => 3,
			'pair_with' => 'energy',
		) ),
		'energy'           => array_merge( $base, array(
			'zone'      => 'body',
			'widget'    => 'split_sector',
			'layout'    => 'split_sector',
			'label'     => 'الطاقة',
			'category'  => 'energy',
			'count'     => 3,
			'pair_with' => 'business',
		) ),
		'automotive'       => array_merge( $base, array(
			'zone'     => 'body',
			'widget'   => 'article_grid',
			'layout'   => 'news_grid',
			'label'    => 'سيارات',
			'category' => 'automotive',
			'count'    => 4,
		) ),
		'most_read'        => array_merge( $base, array(
			'zone'         => 'body',
			'widget'       => 'most_read',
			'layout'       => 'most_read',
			'label'        => 'الأكثر قراءة في قطاع الأعمال',
			'category'     => '',
			'count'        => 5,
			'orderby'      => 'comment_count',
			'view_period'  => 'month',
			'pair_with'    => 'tech',
		) ),
		'tech'             => array_merge( $base, array(
			'zone'      => 'body',
			'widget'    => 'article_grid',
			'layout'    => 'news_grid',
			'label'     => 'تقنية',
			'category'  => 'tech',
			'count'     => 3,
			'variant'   => 'cars_row',
			'pair_with' => 'most_read',
		) ),
		'health'           => array_merge( $base, array(
			'zone'      => 'body',
			'widget'    => 'split_sector',
			'layout'    => 'split_sector',
			'label'     => 'صحة',
			'category'  => 'health',
			'count'     => 3,
			'pair_with' => 'tourism',
		) ),
		'tourism'          => array_merge( $base, array(
			'zone'      => 'body',
			'widget'    => 'split_sector',
			'layout'    => 'split_sector',
			'label'     => 'سياحة وترفيه',
			'category'  => 'tourism',
			'count'     => 3,
			'pair_with' => 'health',
		) ),
	);
}

/**
 * Default body order (pairs kept adjacent).
 *
 * Flow: متابعات → مال/طاقة → سيارات → تقنية (+ الأكثر قراءة) → صحة | سياحة وترفيه (last row).
 *
 * @return string[]
 */
function altijara_default_body_order() {
	return array(
		'followups',
		'business',
		'energy',
		'automotive', // سيارات
		'tech',       // تقنية (cars_row) — after سيارات
		'most_read',  // paired beside تقنية
		'health',     // صحة — last split row
		'tourism',    // سياحة وترفيه
	);
}

/**
 * Addable element palette: Cards vs Blocks.
 *
 * @return array{cards:array<int,array<string,string>>,blocks:array<int,array<string,string>>}
 */
function altijara_section_element_palette() {
	return array(
		'cards'  => array(
			array(
				'widget'  => 'article_grid',
				'variant' => 'default',
				'label'   => __( 'Card grid (4 columns)', 'altijara' ),
				'desc'    => __( 'News cards in a responsive grid.', 'altijara' ),
			),
			array(
				'widget'  => 'article_grid',
				'variant' => 'cars_row',
				'label'   => __( 'Card row (tech style)', 'altijara' ),
				'desc'    => __( 'Horizontal card row used beside Most Read.', 'altijara' ),
			),
		),
		'blocks' => array(
			array(
				'widget'  => 'split_sector',
				'variant' => 'default',
				'label'   => __( 'Sector column (lead + list)', 'altijara' ),
				'desc'    => __( 'One sector column; pair two for a split row.', 'altijara' ),
			),
			array(
				'widget'    => 'split_sector',
				'variant'   => 'default',
				'label'     => __( 'Paired sector row', 'altijara' ),
				'desc'      => __( 'Adds two sector columns wrapped in a split grid.', 'altijara' ),
				'paired'    => '1',
			),
			array(
				'widget'  => 'article_list',
				'variant' => 'default',
				'label'   => __( 'Article list', 'altijara' ),
				'desc'    => __( 'Compact vertical article list.', 'altijara' ),
			),
			array(
				'widget'  => 'lead_stack',
				'variant' => 'default',
				'label'   => __( 'Lead card + stack', 'altijara' ),
				'desc'    => __( 'Featured lead with stacked items below.', 'altijara' ),
			),
			array(
				'widget'  => 'most_read',
				'variant' => 'default',
				'label'   => __( 'Most read', 'altijara' ),
				'desc'    => __( 'Ranked list by views / comments.', 'altijara' ),
			),
			array(
				'widget'  => 'chamber_column',
				'variant' => 'default',
				'label'   => __( 'Chamber column', 'altijara' ),
				'desc'    => __( 'Chamber-style news column block.', 'altijara' ),
			),
		),
	);
}
