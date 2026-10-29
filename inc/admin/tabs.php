<?php
/**
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings tabs — Theme Options architecture (groups + panels).
 *
 * @return array<string, array{label: string, hint: string, icon: string, children?: string[], parent?: string}>
 */
function altijara_settings_tabs() {
	return array(
		'general'      => array(
			'label'    => __( 'General', 'altijara' ),
			'hint'     => __( 'Site look and chrome: design, header, and footer.', 'altijara' ),
			'icon'     => 'dashicons-admin-settings',
			'children' => array( 'design', 'header', 'footer' ),
		),
		'design'       => array(
			'label'  => __( 'Design', 'altijara' ),
			'hint'   => __( 'Fonts and colors used across the site.', 'altijara' ),
			'icon'   => 'dashicons-art',
			'parent' => 'general',
		),
		'header'       => array(
			'label'  => __( 'Header', 'altijara' ),
			'hint'   => __( 'Logo, top bar, search, login, subscribe, and header billboard.', 'altijara' ),
			'icon'   => 'dashicons-table-row-before',
			'parent' => 'general',
		),
		'footer'       => array(
			'label'  => __( 'Footer', 'altijara' ),
			'hint'   => __( 'Footer logo, column headings, contact, copyright, and colors.', 'altijara' ),
			'icon'   => 'dashicons-table-row-after',
			'parent' => 'general',
		),
		'archives'     => array(
			'label'    => __( 'Archives', 'altijara' ),
			'hint'     => __( 'Category pages, the issues page, search results, and tag archives.', 'altijara' ),
			'icon'     => 'dashicons-category',
			'children' => array( 'category_archives', 'issues_archive', 'search_template', 'tag_archives' ),
		),
		'category_archives' => array(
			'label'  => __( 'Category Archives', 'altijara' ),
			'hint'   => __( 'Counts, hero layout, lower column, and category page modules.', 'altijara' ),
			'icon'   => 'dashicons-category',
			'parent' => 'archives',
		),
		'issues_archive' => array(
			'label'  => __( 'Issues Archive', 'altijara' ),
			'hint'   => __( 'Modules for the magazine issues archive at /issues/.', 'altijara' ),
			'icon'   => 'dashicons-book-alt',
			'parent' => 'archives',
		),
		'search_template' => array(
			'label'  => __( 'Search Template', 'altijara' ),
			'hint'   => __( 'Sidebar widgets on search results: ad, magazine, and most read.', 'altijara' ),
			'icon'   => 'dashicons-search',
			'parent' => 'archives',
		),
		'tag_archives' => array(
			'label'  => __( 'Tag Archives', 'altijara' ),
			'hint'   => __( 'Sidebar widgets on a tag page. The list shows every post with that tag.', 'altijara' ),
			'icon'   => 'dashicons-tag',
			'parent' => 'archives',
		),
		'social'       => array(
			'label' => __( 'Social', 'altijara' ),
			'hint'  => __( 'Profile links for header/footer, plus Google, Facebook, and Apple login.', 'altijara' ),
			'icon'  => 'dashicons-share',
		),
		'article'      => array(
			'label' => __( 'Article', 'altijara' ),
			'hint'  => __( 'Single article options, the modules under the article, and the article sidebar.', 'altijara' ),
			'icon'  => 'dashicons-media-document',
		),
		'subscriptions' => array(
			'label' => __( 'Subscriptions', 'altijara' ),
			'hint'  => __( 'Tier product mapping for WooCommerce checkout. Paid UI hides when Woo is inactive.', 'altijara' ),
			'icon'  => 'dashicons-tickets-alt',
		),
		'emails'       => array(
			'label' => __( 'Emails', 'altijara' ),
			'hint'  => __( 'Brand the transactional shell, edit reset/verify copy, and preview the HTML email live.', 'altijara' ),
			'icon'  => 'dashicons-email-alt',
		),
		'integrations' => array(
			'label' => __( 'Tools', 'altijara' ),
			'hint'  => __( 'Cache, setup helpers, and optional menu rebuilds. Menus themselves are edited under Appearance → Menus.', 'altijara' ),
			'icon'  => 'dashicons-admin-tools',
		),
	);
}
