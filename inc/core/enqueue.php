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
 * Enqueue front-end assets (preserve prototype CSS/JS).
 */
function altijara_enqueue_assets() {
	$ver = ALTIJARA_THEME_VERSION;

	wp_enqueue_style(
		'altijara-fonts',
		altijara_asset( 'assets/css/fonts.css' ),
		array(),
		$ver
	);

	wp_enqueue_style(
		'altijara-fontawesome',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
		array(),
		'6.5.2'
	);

	wp_enqueue_style(
		'altijara-main',
		altijara_asset( 'assets/css/style.css' ),
		array( 'altijara-fonts', 'altijara-fontawesome' ),
		$ver
	);

	if ( is_singular( 'post' ) ) {
		wp_enqueue_style(
			'altijara-article',
			altijara_asset( 'assets/css/article-page.css' ),
			array( 'altijara-main' ),
			$ver
		);
	}

	// Site chrome early — fills nav after PHP header is present.
	wp_enqueue_script(
		'altijara-site-chrome',
		altijara_asset( 'assets/js/chrome/site-chrome.js' ),
		array(),
		$ver,
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

	wp_localize_script(
		'altijara-site-chrome',
		'AltijaraTheme',
		array(
			'urls'          => altijara_theme_urls(),
			'categoryLinks' => $category_links,
			'version'       => $ver,
			'ssr'           => true,
		)
	);

	wp_enqueue_script(
		'altijara-wp-api',
		altijara_asset( 'assets/js/shared/wp-api.js' ),
		array(),
		$ver,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	if ( is_front_page() ) {
		wp_enqueue_script(
			'altijara-app',
			altijara_asset( 'assets/js/home/app.js' ),
			array( 'altijara-wp-api', 'altijara-site-chrome' ),
			$ver,
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

		wp_enqueue_script(
			'altijara-category',
			altijara_asset( 'assets/js/category/category.js' ),
			array( 'altijara-wp-api', 'altijara-issues-recent' ),
			$ver,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		$full_hero   = ( $term_id && function_exists( 'altijara_category_uses_full_hero' ) && altijara_category_uses_full_hero( $term_id ) );
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
				'sidebarCount'  => $sidebar_n,
				'featuredCount' => $featured_n,
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
	}
}
add_action( 'wp_enqueue_scripts', 'altijara_enqueue_assets' );
