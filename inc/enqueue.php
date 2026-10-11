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
		'altijara-main',
		altijara_asset( 'assets/css/style.css' ),
		array(),
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
		altijara_asset( 'assets/js/site-chrome.js' ),
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
		altijara_asset( 'assets/js/wp-api.js' ),
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
			altijara_asset( 'assets/js/app.js' ),
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

	if ( is_category() ) {
		wp_enqueue_script(
			'altijara-issues-recent',
			altijara_asset( 'assets/js/issues-recent.js' ),
			array(),
			$ver,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		wp_enqueue_script(
			'altijara-category',
			altijara_asset( 'assets/js/category.js' ),
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
			altijara_asset( 'assets/js/issues-recent.js' ),
			array(),
			$ver,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		wp_enqueue_script(
			'altijara-article',
			altijara_asset( 'assets/js/article.js' ),
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

	if ( is_post_type_archive( 'issue' ) || is_singular( 'issue' ) || is_page_template( 'page-issues.php' ) ) {
		wp_enqueue_script(
			'altijara-issues',
			altijara_asset( 'assets/js/issues.js' ),
			array(),
			$ver,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'altijara_enqueue_assets' );
