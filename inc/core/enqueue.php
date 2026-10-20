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
		'altijara-main',
		altijara_asset( 'assets/css/style.css' ),
		array( 'altijara-fonts' ),
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
		wp_localize_script(
			'altijara-article',
			'AltijaraArticle',
			array(
				'id'   => get_queried_object_id(),
				'slug' => get_post_field( 'post_name', get_queried_object_id() ),
				'ssr'  => true,
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
