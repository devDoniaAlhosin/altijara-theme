<?php
/**
 * Settings panel dispatcher — loads per-tab panel modules.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$altijara_panel_dir = ALTIJARA_THEME_DIR . '/inc/admin/panels';
require_once $altijara_panel_dir . '/_helpers.php';
require_once $altijara_panel_dir . '/design.php';
require_once $altijara_panel_dir . '/header.php';
require_once $altijara_panel_dir . '/footer.php';
require_once $altijara_panel_dir . '/archives.php';
require_once $altijara_panel_dir . '/category_archives.php';
require_once $altijara_panel_dir . '/issues_archive.php';
require_once $altijara_panel_dir . '/search_template.php';
require_once $altijara_panel_dir . '/tag_archives.php';
require_once $altijara_panel_dir . '/social.php';
require_once $altijara_panel_dir . '/article.php';
require_once $altijara_panel_dir . '/subscriptions.php';
require_once $altijara_panel_dir . '/emails.php';
require_once $altijara_panel_dir . '/integrations.php';

/**
 * Tab id → render callback registry (filterable).
 *
 * @return array<string, callable>
 */
function altijara_settings_panel_registry() {
	$registry = array(
		'design'            => 'altijara_render_settings_panel_design',
		'header'            => 'altijara_render_settings_panel_header',
		'footer'            => 'altijara_render_settings_panel_footer',
		'archives'          => 'altijara_render_settings_panel_archives',
		'category_archives' => 'altijara_render_settings_panel_category_archives',
		'issues_archive'    => 'altijara_render_settings_panel_issues_archive',
		'search_template'   => 'altijara_render_settings_panel_search_template',
		'tag_archives'      => 'altijara_render_settings_panel_tag_archives',
		'social'            => 'altijara_render_settings_panel_social',
		'article'           => 'altijara_render_settings_panel_article',
		'subscriptions'     => 'altijara_render_settings_panel_subscriptions',
		'emails'            => 'altijara_render_settings_panel_emails',
		'integrations'      => 'altijara_render_settings_panel_integrations',
	);

	/**
	 * Filter settings panel render callbacks.
	 *
	 * @param array<string, callable> $registry Tab id => callback.
	 */
	return apply_filters( 'altijara_settings_panel_registry', $registry );
}

/**
 * Render one settings tab panel body (inside form).
 *
 * @param string               $id  Tab id.
 * @param array<string, mixed> $ctx Panel context.
 */
function altijara_render_settings_panel( $id, array $ctx ) {
	$id       = sanitize_key( (string) $id );
	$registry = altijara_settings_panel_registry();

	if ( isset( $registry[ $id ] ) && is_callable( $registry[ $id ] ) ) {
		call_user_func( $registry[ $id ], $ctx );
		return;
	}

	// Parent group tabs (general, archives hub already has its own panel) — fall back to tools.
	if ( isset( $registry['integrations'] ) && is_callable( $registry['integrations'] ) ) {
		call_user_func( $registry['integrations'], $ctx );
	}
}
