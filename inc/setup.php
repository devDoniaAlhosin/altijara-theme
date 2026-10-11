<?php
/**
 * Theme supports, menus, image sizes, CPTs
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme setup.
 */
function altijara_theme_setup() {
	load_theme_textdomain( 'altijara', ALTIJARA_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	// Menu locations registered in inc/nav.php (primary, top_bar, mobile, footer, footer_quick).

	add_image_size( 'altijara-card', 640, 400, true );
	add_image_size( 'altijara-hero', 1200, 675, true );
}
add_action( 'after_setup_theme', 'altijara_theme_setup' );

/**
 * Register Issue custom post type (magazine issues archive).
 */
function altijara_register_issue_cpt() {
	$labels = array(
		'name'          => __( 'أعداد المجلة', 'altijara' ),
		'singular_name' => __( 'عدد', 'altijara' ),
		'add_new_item'  => __( 'إضافة عدد', 'altijara' ),
		'edit_item'     => __( 'تعديل العدد', 'altijara' ),
		'view_item'     => __( 'عرض العدد', 'altijara' ),
		'search_items'  => __( 'بحث في الأعداد', 'altijara' ),
	);

	register_post_type(
		'issue',
		array(
			'labels'       => $labels,
			'public'       => true,
			'has_archive'  => 'issues',
			'rewrite'      => array( 'slug' => 'issue' ),
			'menu_icon'    => 'dashicons-book-alt',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'altijara_register_issue_cpt' );

/**
 * Body classes.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function altijara_body_classes( $classes ) {
	if ( is_page_template( 'page-issues.php' ) || is_post_type_archive( 'issue' ) ) {
		$classes[] = 'issues-page-body';
	}
	if ( is_front_page() || is_category() ) {
		$classes[] = 'altijara-ssr';
	}
	if ( is_front_page() ) {
		$classes[] = 'altijara-home-ssr';
		$classes[] = 'live-ready';
	}
	$header = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'header', array() ) : array();
	if ( ! empty( $header['sticky'] ) ) {
		$classes[] = 'altijara-sticky-header';
	}
	return $classes;
}
add_filter( 'body_class', 'altijara_body_classes' );
