<?php
/**
 * Canonical default theme configuration (UI-derived).
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Full default options tree (never wipe user values — merge missing only).
 *
 * @return array<string, mixed>
 */
function altijara_default_options() {
	$logo_default = 'https://altijaramagazine.com/wp-content/uploads/2026/09/Asset-1@4x.png';
	$logo_hover   = 'https://altijaramagazine.com/wp-content/uploads/2026/09/Logo-%D8%A3%D8%B2%D8%B1%D9%82.png';
	$logo_footer  = 'https://altijaramagazine.com/wp-content/uploads/2026/09/Asset-14@4x.png';

	return array(
		'general'      => array(
			'site_tagline_ar' => 'تصدر عن غرفة جدة منذ عام 1948',
		),
		'design'       => function_exists( 'altijara_default_design' ) ? altijara_default_design() : array(
			'font_main'         => 'ibm_plex',
			'font_serif'        => 'playfair',
			'font_main_custom'  => '',
			'font_serif_custom' => '',
			'bg_salmon'         => '#FFF1E5',
			'bg_salmon_light'   => '#F2DFCE',
			'bg_card'           => '#FFF7EF',
			'ink_black'         => '#262A33',
			'ink_secondary'     => '#3b424e',
			'ink_muted'         => '#6e7787',
			'accent_brand'      => '#1897B8',
			'accent_hover'      => '#12748e',
			'accent_amber'      => '#d97706',
			'footer_bg'         => '',
			'footer_text'       => '',
		),
		'header'       => array(
			'logo_url'            => $logo_default,
			'logo_hover_url'      => $logo_hover,
			'logo_width'          => 0,
			'logo_height'         => 0,
			'background'          => '',
			'layout'              => 'default',
			'sticky'              => false,
			'show_top_bar'        => true,
			'top_bar_text'        => '',
			'ad_shortcode'        => '',
			'ad_image'            => '',
			'ad_url'              => '',
			'show_search'         => true,
			'show_login'          => true,
			'login_text'          => 'تسجيل الدخول',
			'login_url'           => '',
			'show_subscribe'      => true,
			'subscribe_text'      => 'اشترك',
			'subscribe_url'       => '',
			'show_social'         => true,
			'show_date'           => true,
			'mobile_show_search'  => true,
			'mobile_show_subscribe' => true,
		),
		'footer'       => array(
			'logo_url'           => $logo_footer,
			'description'        => 'تصدر عن غرفة جدة منذ عام 1948',
			'sections_heading'   => 'الأقسام',
			'quick_heading'      => 'روابط تهمك',
			'social_heading'     => 'تابعونا على',
			'show_brand'         => true,
			'show_sections'      => true,
			'show_quick'         => true,
			'show_social'        => true,
			'contact_address'    => '',
			'contact_email'      => '',
			'contact_phone'      => '',
			'copyright'          => '{year}@ التجارة جميع الحقوق محفوظة',
			'background'         => '',
			'text_color'         => '',
			'layout'             => 'default',
		),
		'social'       => array(
			'items' => array(
				array(
					'platform' => 'x',
					'label'    => 'منصة X',
					'url'      => 'https://x.com/altijaramag',
					'enabled'  => true,
					'order'    => 10,
				),
				array(
					'platform' => 'linkedin',
					'label'    => 'لينكد إن',
					'url'      => 'https://www.linkedin.com/company/altijara-magazine',
					'enabled'  => true,
					'order'    => 20,
				),
				array(
					'platform' => 'youtube',
					'label'    => 'يوتيوب',
					'url'      => 'https://www.youtube.com/@JeddahChamber',
					'enabled'  => true,
					'order'    => 30,
				),
				array(
					'platform' => 'whatsapp',
					'label'    => 'واتساب للأعمال',
					'url'      => 'https://whatsapp.com/channel/altijaramag',
					'enabled'  => true,
					'order'    => 40,
				),
				array(
					'platform' => 'facebook',
					'label'    => 'Facebook',
					'url'      => 'https://www.facebook.com/altijaramag',
					'enabled'  => true,
					'order'    => 50,
				),
				array(
					'platform' => 'instagram',
					'label'    => 'Instagram',
					'url'      => 'https://instagram.com/altijaramag',
					'enabled'  => true,
					'order'    => 60,
				),
				array(
					'platform' => 'tiktok',
					'label'    => 'TikTok',
					'url'      => '',
					'enabled'  => false,
					'order'    => 70,
				),
			),
		),
		'article'      => array(
			'show_related'     => true,
			'related_count'    => 2,
			'related_per_load' => 2,
			'share_platforms'  => array( 'whatsapp', 'x', 'linkedin', 'facebook', 'telegram', 'copy' ),
			'share_extra'      => array(),
			'latest'           => array(
				'count'        => 5,
				'source'       => 'same',
				'category_ids' => array(),
			),
			'most_read'    => array(
				'count'        => 7,
				'source'       => 'same',
				'category_ids' => array(),
			),
		),
		'magazine'     => array(
			'flip_base' => 'https://online.fliphtml5.com/',
		),
		'advertising'  => array(
			'sidebar_enabled'   => true,
			'sidebar_shortcode' => '',
			'sidebar_image'     => 'https://altijaramagazine.com/wp-content/uploads/angwp/items/31121/%D8%AE%D8%AF%D9%85%D8%A7%D8%AA%20%D9%85%D9%8F%D8%AA%D9%85%D9%8A%D8%B2%D8%A9_%20300x600%20copy.png',
			'sidebar_url'       => '',
		),
		'auth'         => array(
			'google_enabled'       => false,
			'google_client_id'     => '',
			'google_client_secret' => '',
			'facebook_enabled'     => false,
			'facebook_app_id'      => '',
			'facebook_app_secret'  => '',
		),
		'integrations' => array(
			'rest_prefer_local' => true,
		),
		'advanced'     => array(
			'cache_ttl' => 900,
		),
		// Prefer live production slugs (مجلة التجارة); detect_category_map may replace with term IDs.
		'category_map'   => array(
			'chamber'     => 'the-chamber-news',
			'exhibitions' => 'exhibitions',
			'followups'   => 'follow-ups',
			'interviews'  => 'interviews',
			'personality' => 'issue-cover-personality',
			'business'    => 'money-and-business',
			'logistics'   => 'logistic',
			'industry'    => 'industry',
			'energy'      => 'energy',
			'automotive'  => 'cars',
			'health'      => 'health',
			'tech'        => 'technique',
			'tourism'     => 'tourism-and-entertainment',
			'misc'        => 'miscellaneous',
		),
		'sections'        => array(),
		// sections_order omitted from defaults — only stored after Homepage save.
		'archive_layouts'      => array(),
		'archive_magazine_ids' => array(),
		'archive_counts'       => array(
			'sidebar_count' => 3,
			'featured_rows' => 2,
			'news_size'     => 10,
		),
		'issues_archive_layout' => array(),
		// archive_full_hero intentionally omitted until saved (legacy keys apply until then).
		'utility_page_ids'     => array(),
	);
}

/**
 * Deep-fill missing keys only (never overwrite existing scalars/arrays that are set).
 *
 * @param array<string, mixed> $defaults Defaults.
 * @param array<string, mixed> $saved    Saved.
 * @return array<string, mixed>
 */
function altijara_fill_missing_recursive( $defaults, $saved ) {
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	foreach ( $defaults as $key => $value ) {
		if ( ! array_key_exists( $key, $saved ) ) {
			$saved[ $key ] = $value;
			continue;
		}
		if ( is_array( $value ) && is_array( $saved[ $key ] ) ) {
			// Associative config branches: fill missing children.
			$is_list = array_keys( $value ) === range( 0, count( $value ) - 1 );
			if ( ! $is_list ) {
				$saved[ $key ] = altijara_fill_missing_recursive( $value, $saved[ $key ] );
			}
			// Indexed lists (e.g. social items): keep user list as-is when non-empty.
			if ( $is_list && empty( $saved[ $key ] ) ) {
				$saved[ $key ] = $value;
			}
		}
	}
	return $saved;
}

/**
 * Primary nav blueprint (labels + resolvers) from existing UI.
 *
 * @return array<int, array<string, string>>
 */
function altijara_default_primary_nav_blueprint() {
	$cats = array(
		'chamber'     => 'أخبار الغرفة',
		'exhibitions' => 'المعارض',
		'followups'   => 'متابعات',
		'interviews'  => 'لقاءات',
		'business'    => 'مال وأعمال',
		'logistics'   => 'لوجستيات',
		'industry'    => 'الصناعة',
		'energy'      => 'الطاقة',
		'automotive'  => 'سيارات',
		'health'      => 'صحة',
		'tech'        => 'تقنية',
		'tourism'     => 'سياحة وترفيه',
		'misc'        => 'منوعات',
	);
	$items = array(
		array(
			'type'  => 'home',
			'label' => 'الرئيسية',
		),
	);
	foreach ( $cats as $key => $label ) {
		$items[] = array(
			'type'  => 'category_key',
			'key'   => $key,
			'label' => $label,
		);
	}
	$items[] = array(
		'type'  => 'issues',
		'label' => 'أعداد المجلة',
	);
	return $items;
}

/**
 * Utility pages created on theme activation (slug → title + template).
 *
 * @return array<int, array{slug:string,title:string,template:string}>
 */
function altijara_default_utility_pages_blueprint() {
	return array(
		array(
			'slug'     => 'about',
			'title'    => 'من نحن',
			'template' => 'page-about.php',
		),
		array(
			'slug'     => 'contact',
			'title'    => 'اتصل بنا',
			'template' => 'page-contact.php',
		),
		array(
			'slug'     => 'advertise',
			'title'    => 'أعلن معنا',
			'template' => 'page-advertise.php',
		),
		array(
			'slug'     => 'subscribe',
			'title'    => 'اشترك',
			'template' => 'page-subscribe.php',
		),
		array(
			'slug'     => 'login',
			'title'    => 'تسجيل الدخول',
			'template' => 'page-login.php',
		),
	);
}

/**
 * Top bar / utility nav blueprint.
 *
 * @return array<int, array<string, string>>
 */
function altijara_default_utility_nav_blueprint() {
	return array(
		array(
			'type'  => 'page_slug',
			'slug'  => 'about',
			'label' => 'من نحن',
		),
		array(
			'type'  => 'page_slug',
			'slug'  => 'contact',
			'label' => 'اتصل بنا',
		),
		array(
			'type'  => 'page_slug',
			'slug'  => 'advertise',
			'label' => 'أعلن معنا',
		),
	);
}

/**
 * Footer quick links blueprint.
 *
 * @return array<int, array<string, string>>
 */
function altijara_default_footer_quick_blueprint() {
	return array(
		array(
			'type'  => 'page_slug',
			'slug'  => 'about',
			'label' => 'من نحن',
		),
		array(
			'type'  => 'page_slug',
			'slug'  => 'contact',
			'label' => 'اتصل بنا',
		),
		array(
			'type'  => 'page_slug',
			'slug'  => 'advertise',
			'label' => 'أعلن معنا',
		),
		array(
			'type'  => 'page_slug',
			'slug'  => 'subscribe',
			'label' => 'اشترك',
		),
		array(
			'type'  => 'issues',
			'label' => 'أعداد المجلة',
		),
		array(
			'type'   => 'custom',
			'label'  => 'غرفة جدة',
			'url'    => 'https://www.jcci.org.sa/',
			'target' => '_blank',
		),
	);
}
