<?php
/**
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings tabs — English labels + short hints for editors.
 *
 * @return array<string, array{label: string, hint: string, icon: string}>
 */
function altijara_settings_tabs() {
	return array(
		'general'      => array(
			'label' => __( 'General', 'altijara' ),
			'hint'  => __( 'Site tagline and category slug map used by homepage sections.', 'altijara' ),
			'icon'  => 'dashicons-admin-generic',
		),
		'design'       => array(
			'label' => __( 'Design', 'altijara' ),
			'hint'  => __( 'Fonts and colors used across the site.', 'altijara' ),
			'icon'  => 'dashicons-art',
		),
		'header'       => array(
			'label' => __( 'Header', 'altijara' ),
			'hint'  => __( 'Logo, top bar, search, login, and subscribe controls at the top of every page.', 'altijara' ),
			'icon'  => 'dashicons-arrow-up-alt',
		),
		'navigation'   => array(
			'label' => __( 'Navigation', 'altijara' ),
			'hint'  => __( 'Primary, top bar, mobile, and footer menus — edited in Appearance → Menus.', 'altijara' ),
			'icon'  => 'dashicons-menu',
		),
		'homepage'     => array(
			'label' => __( 'Homepage', 'altijara' ),
			'hint'  => __( 'Homepage section sources, counts, order, and visibility for the custom magazine layout.', 'altijara' ),
			'icon'  => 'dashicons-admin-home',
		),
		'archives'     => array(
			'label' => __( 'Archives', 'altijara' ),
			'hint'  => __( 'Assign Posts or Magazine issues layout to each category archive.', 'altijara' ),
			'icon'  => 'dashicons-category',
		),
		'footer'       => array(
			'label' => __( 'Footer', 'altijara' ),
			'hint'  => __( 'Bottom of every page: logo, columns, copyright, and social links.', 'altijara' ),
			'icon'  => 'dashicons-arrow-down-alt',
		),
		'social'       => array(
			'label' => __( 'Social', 'altijara' ),
			'hint'  => __( 'Shared social profile links shown in the header and footer.', 'altijara' ),
			'icon'  => 'dashicons-share',
		),
		'article'      => array(
			'label' => __( 'Article', 'altijara' ),
			'hint'  => __( 'Single article options such as related posts.', 'altijara' ),
			'icon'  => 'dashicons-media-document',
		),
		'magazine'     => array(
			'label' => __( 'Magazine', 'altijara' ),
			'hint'  => __( 'Digital issue archive and flipbook viewer settings.', 'altijara' ),
			'icon'  => 'dashicons-book',
		),
		'advertising'  => array(
			'label' => __( 'Advertising', 'altijara' ),
			'hint'  => __( 'Enable or configure ad units on the site.', 'altijara' ),
			'icon'  => 'dashicons-megaphone',
		),
		'integrations' => array(
			'label' => __( 'Tools', 'altijara' ),
			'hint'  => __( 'Cache purge and safe theme setup re-run.', 'altijara' ),
			'icon'  => 'dashicons-admin-tools',
		),
	);
}
