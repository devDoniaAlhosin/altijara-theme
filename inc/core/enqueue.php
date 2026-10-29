<?php
/**
 * Styles and scripts
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue shared bookmarks script when save buttons or saved list appear.
 */
function altijara_enqueue_bookmarks_script() {
	static $done = false;
	if ( $done || ! function_exists( 'altijara_bookmarks_js_config' ) ) {
		return;
	}
	$done = true;

	$js_path = get_template_directory() . '/assets/js/shared/bookmarks.js';
	$ver     = file_exists( $js_path ) ? (string) filemtime( $js_path ) : ALTIJARA_THEME_VERSION;

	wp_enqueue_script(
		'altijara-bookmarks',
		altijara_asset( 'assets/js/shared/bookmarks.js' ),
		array(),
		$ver,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
	wp_localize_script( 'altijara-bookmarks', 'AltijaraBookmarks', altijara_bookmarks_js_config() );
}

/**
 * Whether current view needs the bookmarks script.
 *
 * @return bool
 */
function altijara_needs_bookmarks_script() {
	if ( is_category() || is_search() || is_tag() || is_author() || is_singular( 'post' ) ) {
		return true;
	}
	if ( is_page_template( 'page-saved.php' ) ) {
		return true;
	}
	if ( function_exists( 'is_account_page' ) && is_account_page() ) {
		return true;
	}
	return false;
}

/**
 * filemtime-based version for a theme CSS file.
 *
 * @param string $relative Path under theme root (e.g. assets/css/base.css).
 * @param string $fallback Fallback version string.
 * @return string
 */
function altijara_css_ver( $relative, $fallback ) {
	$path = get_template_directory() . '/' . ltrim( $relative, '/' );
	return file_exists( $path ) ? (string) filemtime( $path ) : $fallback;
}

/**
 * Whether the current view is a static utility page template.
 *
 * @return bool
 */
function altijara_is_static_page_view() {
	$templates = array(
		'page-about.php',
		'page-advertise.php',
		'page-contact.php',
		'page-subscribe.php',
		'page-login.php',
		'page-register.php',
		'page-reset.php',
		'page-saved.php',
	);
	foreach ( $templates as $tpl ) {
		if ( is_page_template( $tpl ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Enqueue front-end assets (modular CSS + scripts).
 */
function altijara_enqueue_assets() {
	$ver = ALTIJARA_THEME_VERSION;

	wp_enqueue_style(
		'altijara-fonts',
		altijara_asset( 'assets/css/fonts.css' ),
		array(),
		altijara_css_ver( 'assets/css/fonts.css', $ver )
	);

	// Font Awesome removed — theme uses inline SVGs (was only pagination chevrons).

	wp_enqueue_style(
		'altijara-main',
		altijara_asset( 'assets/css/base.css' ),
		array( 'altijara-fonts' ),
		altijara_css_ver( 'assets/css/base.css', $ver )
	);

	$issues_view_early = function_exists( 'altijara_is_issues_archive_view' ) && altijara_is_issues_archive_view();

	// Home widgets (card grid, most read, etc.) also render on the issues archive.
	if ( is_front_page() || is_home() || $issues_view_early ) {
		wp_enqueue_style(
			'altijara-home',
			altijara_asset( 'assets/css/home.css' ),
			array( 'altijara-main' ),
			altijara_css_ver( 'assets/css/home.css', $ver )
		);
	}

	$needs_category_css = ( is_category() && ! $issues_view_early )
		|| is_search()
		|| is_tag()
		|| is_author()
		|| is_page_template( 'page-saved.php' )
		|| ( function_exists( 'is_account_page' ) && is_account_page() );
	if ( $needs_category_css ) {
		wp_enqueue_style(
			'altijara-category',
			altijara_asset( 'assets/css/category.css' ),
			array( 'altijara-main' ),
			altijara_css_ver( 'assets/css/category.css', $ver )
		);
	}

	if ( altijara_is_static_page_view() ) {
		wp_enqueue_style(
			'altijara-static-pages',
			altijara_asset( 'assets/css/static-pages.css' ),
			array( 'altijara-main' ),
			altijara_css_ver( 'assets/css/static-pages.css', $ver )
		);
	}

	// Quform shortcode layout fix (any form id) — Contact / Advertise / meta shortcode pages.
	if ( function_exists( 'altijara_needs_quform_compat' ) && altijara_needs_quform_compat() ) {
		$quform_js  = get_template_directory() . '/assets/js/shared/quform-compat.js';
		$quform_ver = file_exists( $quform_js ) ? (string) filemtime( $quform_js ) : $ver;
		wp_enqueue_script(
			'altijara-quform-compat',
			altijara_asset( 'assets/js/shared/quform-compat.js' ),
			array( 'jquery' ),
			$quform_ver,
			true
		);
	}

	if ( is_singular( 'post' ) ) {
		wp_enqueue_style(
			'altijara-category',
			altijara_asset( 'assets/css/category.css' ),
			array( 'altijara-main' ),
			altijara_css_ver( 'assets/css/category.css', $ver )
		);
		wp_enqueue_style(
			'altijara-home',
			altijara_asset( 'assets/css/home.css' ),
			array( 'altijara-main' ),
			altijara_css_ver( 'assets/css/home.css', $ver )
		);
		wp_enqueue_style(
			'altijara-article',
			altijara_asset( 'assets/css/article-page.css' ),
			array( 'altijara-main', 'altijara-category', 'altijara-home' ),
			altijara_css_ver( 'assets/css/article-page.css', $ver )
		);
	}

	// Site chrome early — fills nav after PHP header is present.
	$chrome_js  = get_template_directory() . '/assets/js/chrome/site-chrome.js';
	$chrome_ver = file_exists( $chrome_js ) ? (string) filemtime( $chrome_js ) : $ver;
	wp_enqueue_script(
		'altijara-site-chrome',
		altijara_asset( 'assets/js/chrome/site-chrome.js' ),
		array(),
		$chrome_ver,
		array(
			'in_footer' => false,
			'strategy'  => 'defer',
		)
	);

	$category_links = array();
	$map            = altijara_get_option( 'category_map', array() );
	if ( is_array( $map ) ) {
		foreach ( array_keys( $map ) as $key ) {
			$category_links[ $key ] = altijara_get_section_archive_url( $key );
		}
	}

	$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();

	wp_localize_script(
		'altijara-site-chrome',
		'AltijaraTheme',
		array(
			'urls'          => altijara_theme_urls(),
			'fallbackImg'   => function_exists( 'altijara_fallback_thumb_url' ) ? altijara_fallback_thumb_url() : '',
			'categoryLinks' => $category_links,
			'version'       => $ver,
			'ssr'           => true,
			'locale'        => $locale,
			'isRtl'         => is_rtl(),
		)
	);

	// REST helpers: skip on SSR front page (home app is slider/truncation only; chrome search has its own fetch).
	$needs_wp_api = ! is_front_page();
	if ( $needs_wp_api ) {
		$wp_api_js  = get_template_directory() . '/assets/js/shared/wp-api.js';
		$wp_api_ver = file_exists( $wp_api_js ) ? (string) filemtime( $wp_api_js ) : $ver;
		wp_enqueue_script(
			'altijara-wp-api',
			altijara_asset( 'assets/js/shared/wp-api.js' ),
			array(),
			$wp_api_ver,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	if ( altijara_needs_bookmarks_script() ) {
		altijara_enqueue_bookmarks_script();
	}

	if ( is_front_page() ) {
		$app_js  = get_template_directory() . '/assets/js/home/app.js';
		$app_ver = file_exists( $app_js ) ? (string) filemtime( $app_js ) : $ver;
		wp_enqueue_script(
			'altijara-app',
			altijara_asset( 'assets/js/home/app.js' ),
			array( 'altijara-site-chrome' ),
			$app_ver,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		// Homepage is PHP-rendered; expose flag only (no duplicate content payload).
		wp_localize_script(
			'altijara-app',
			'AltijaraSSR',
			array(
				'mode' => 'php',
				'home' => true,
			)
		);
	}

	$issues_view = function_exists( 'altijara_is_issues_archive_view' ) && altijara_is_issues_archive_view();

	if ( is_category() && ! $issues_view ) {
		wp_enqueue_script(
			'altijara-issues-recent',
			altijara_asset( 'assets/js/issues/issues-recent.js' ),
			array(),
			$ver,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		$term_obj = get_queried_object();
		$term_id  = ( $term_obj && isset( $term_obj->term_id ) ) ? (int) $term_obj->term_id : 0;
		$cat_key  = ( $term_id && function_exists( 'altijara_resolve_category_key' ) )
			? altijara_resolve_category_key( $term_id )
			: '';

		// Magazine sidebar slider — featured covers (absolute staging URLs).
		if ( function_exists( 'altijara_get_magazine_slider_items' ) ) {
			$live_issues = altijara_get_magazine_slider_items( 8 );
			if ( $live_issues ) {
				wp_add_inline_script(
					'altijara-issues-recent',
					'window.AltijaraRecentIssues=' . wp_json_encode( array_values( $live_issues ) ) . ';',
					'after'
				);
			}
		}

		$cat_js  = get_template_directory() . '/assets/js/category/category.js';
		$cat_ver = file_exists( $cat_js ) ? (string) filemtime( $cat_js ) : $ver;
		wp_enqueue_script(
			'altijara-category',
			altijara_asset( 'assets/js/category/category.js' ),
			array( 'altijara-wp-api', 'altijara-issues-recent', 'altijara-bookmarks' ),
			$cat_ver,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		$full_hero   = function_exists( 'altijara_category_archive_is_full_hero' )
			? altijara_category_archive_is_full_hero( $term_id )
			: ( $term_id && function_exists( 'altijara_category_uses_full_hero' ) && altijara_category_uses_full_hero( $term_id ) );
		$counts      = function_exists( 'altijara_get_archive_counts' )
			? altijara_get_archive_counts()
			: array(
				'sidebar_count'  => 3,
				'featured_count' => 8,
				'news_size'      => 10,
			);
		$sidebar_n   = $full_hero ? 0 : (int) $counts['sidebar_count'];
		$featured_n  = (int) $counts['featured_count'];
		$news_size   = (int) $counts['news_size'];
		$spotlight   = 1 + $sidebar_n + $featured_n;
		$total_posts = ( $term_id && function_exists( 'altijara_count_category_posts' ) ) ? altijara_count_category_posts( $term_id ) : 0;
		$news_remain = max( 0, $total_posts - $spotlight );
		$total_pages = $news_remain > 0 ? (int) max( 1, (int) ceil( $news_remain / $news_size ) ) : 1;
		$paged_now   = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

		wp_localize_script(
			'altijara-category',
			'AltijaraCategory',
			array(
				'termId'        => $term_id,
				'key'           => $cat_key,
				'slug'          => ( $term_obj && ! empty( $term_obj->slug ) ) ? (string) $term_obj->slug : '',
				'name'          => ( $term_obj && ! empty( $term_obj->name ) ) ? (string) $term_obj->name : '',
				'ssr'           => true,
				'fullHero'      => $full_hero,
				'total'         => $total_posts,
				'totalPages'    => $total_pages,
				'page'          => $paged_now,
				'newsSize'      => $news_size,
				'spotlight'     => $spotlight,
				'sidebarCount'    => $sidebar_n,
				'featuredCount'   => $featured_n,
				'includeChildren' => function_exists( 'altijara_archive_include_children' ) && altijara_archive_include_children() ? 1 : 0,
			)
		);
	}

	if ( is_singular( 'post' ) ) {
		wp_enqueue_script(
			'altijara-issues-recent',
			altijara_asset( 'assets/js/issues/issues-recent.js' ),
			array(),
			$ver,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		if ( function_exists( 'altijara_get_magazine_slider_items' ) ) {
			$article_issues = altijara_get_magazine_slider_items( 8 );
			if ( $article_issues ) {
				wp_add_inline_script(
					'altijara-issues-recent',
					'window.AltijaraRecentIssues=' . wp_json_encode( array_values( $article_issues ) ) . ';',
					'after'
				);
			}
		}
		wp_enqueue_script(
			'altijara-article',
			altijara_asset( 'assets/js/article/article.js' ),
			array( 'altijara-wp-api', 'altijara-issues-recent', 'altijara-site-chrome' ),
			$ver,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		$article_opts = function_exists( 'altijara_get_article_settings' ) ? altijara_get_article_settings() : array();
		wp_localize_script(
			'altijara-article',
			'AltijaraArticle',
			array(
				'id'              => get_queried_object_id(),
				'slug'            => get_post_field( 'post_name', get_queried_object_id() ),
				'ssr'             => true,
				'relatedInitial'  => isset( $article_opts['related_count'] ) ? (int) $article_opts['related_count'] : 2,
				'relatedPerLoad'  => isset( $article_opts['related_per_load'] ) ? (int) $article_opts['related_per_load'] : 2,
			)
		);
	}

	if ( is_search() ) {
		$search_js  = get_template_directory() . '/assets/js/search/search.js';
		$search_ver = file_exists( $search_js ) ? (string) filemtime( $search_js ) : $ver;
		$counts     = function_exists( 'altijara_get_archive_counts' )
			? altijara_get_archive_counts()
			: array( 'news_size' => 10 );
		$per_page   = max( 1, (int) ( $counts['news_size'] ?? 10 ) );
		$total      = (int) $GLOBALS['wp_query']->found_posts;
		$total_pages = $total > 0 ? (int) max( 1, (int) ceil( $total / $per_page ) ) : 1;
		$paged_now  = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

		wp_enqueue_script(
			'altijara-issues-recent',
			altijara_asset( 'assets/js/issues/issues-recent.js' ),
			array(),
			$ver,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		if ( function_exists( 'altijara_get_magazine_slider_items' ) ) {
			$search_issues = altijara_get_magazine_slider_items( 8 );
			if ( $search_issues ) {
				wp_add_inline_script(
					'altijara-issues-recent',
					'window.AltijaraRecentIssues=' . wp_json_encode( array_values( $search_issues ) ) . ';',
					'after'
				);
			}
		}

		wp_enqueue_script(
			'altijara-search',
			altijara_asset( 'assets/js/search/search.js' ),
			array( 'altijara-wp-api', 'altijara-issues-recent', 'altijara-bookmarks' ),
			$search_ver,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		wp_localize_script(
			'altijara-search',
			'AltijaraSearch',
			array(
				'query'      => get_search_query(),
				'page'       => $paged_now,
				'perPage'    => $per_page,
				'total'      => $total,
				'totalPages' => $total_pages,
				'ssr'        => true,
				'fallbackImg'=> function_exists( 'altijara_asset' ) ? altijara_asset( 'assets/images/thumb-logo-fallback.png' ) : '',
				'i18n'       => array(
					'titleFor'   => __( 'نتائج البحث لـ “%s”', 'altijara' ),
					'titleEmpty' => __( 'البحث في الأرشيف', 'altijara' ),
					'meta'       => __( '%1$s نتيجة لـ “%2$s”', 'altijara' ),
					'empty'      => __( 'لا توجد نتائج مطابقة. جرّب كلمات أخرى.', 'altijara' ),
					'save'       => __( 'حفظ المقال', 'altijara' ),
					'saveLabel'  => __( 'حفظ', 'altijara' ),
					'savedLabel' => __( 'تم الحفظ', 'altijara' ),
				),
			)
		);
	}

	if ( $issues_view || is_singular( 'issue' ) ) {
		$js_path = ALTIJARA_THEME_DIR . '/assets/js/issues/issues-archive.js';
		wp_enqueue_script(
			'altijara-issues-archive',
			altijara_asset( 'assets/js/issues/issues-archive.js' ),
			array(),
			file_exists( $js_path ) ? (string) filemtime( $js_path ) : $ver,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		$payload = array();
		if ( function_exists( 'altijara_get_issue_posts' ) && function_exists( 'altijara_issue_to_js_payload' ) ) {
			foreach ( altijara_get_issue_posts( 48, 0 ) as $issue_post ) {
				$payload[] = altijara_issue_to_js_payload( $issue_post );
			}
		}
		wp_localize_script(
			'altijara-issues-archive',
			'AltijaraIssuesPayload',
			$payload
		);

		// Adning may re-add _ning_hidden after load (archive ≠ singular issue).
		// Keep the Theme Options header billboard visible on /issues/ only.
		if ( $issues_view ) {
			wp_add_inline_script(
				'altijara-issues-archive',
				'(function(){function u(){var r=document.querySelector(\'.header-sponsor-banner .header-ad-shortcode\');if(!r)return;r.querySelectorAll(\'._ning_hidden\').forEach(function(n){n.classList.remove(\'_ning_hidden\');});}u();if(document.readyState===\'loading\'){document.addEventListener(\'DOMContentLoaded\',u);}window.addEventListener(\'load\',function(){u();setTimeout(u,400);setTimeout(u,1200);});})();',
				'after'
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'altijara_enqueue_assets' );
