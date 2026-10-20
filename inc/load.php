<?php
/**
 * Ordered theme module loader.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$altijara_includes = array(
	// Core utilities.
	'/inc/core/helpers.php',
	'/inc/core/design.php',
	'/inc/core/defaults.php',
	'/inc/core/caching.php',
	// Content helpers.
	'/inc/content/queries.php',
	'/inc/content/social.php',
	'/inc/content/nav.php',
	'/inc/content/archive-layouts.php',
	// Widget engine.
	'/inc/widgets/registry.php',
	'/inc/widgets/query-engine.php',
	'/inc/widgets/renderer.php',
	'/inc/widgets/ajax.php',
	// Homepage sections (split from section-manager).
	'/inc/sections/defaults.php',
	'/inc/sections/config.php',
	'/inc/sections/sanitize.php',
	'/inc/sections/front-layout.php',
	'/inc/sections/admin/choices.php',
	'/inc/sections/admin/card.php',
	'/inc/sections/admin/homepage-builder.php',
	// Page meta CMS.
	'/inc/page-meta/schema.php',
	'/inc/page-meta/defaults.php',
	'/inc/page-meta/repository.php',
	'/inc/page-meta/sanitize.php',
	'/inc/page-meta/save.php',
	'/inc/page-meta/helpers.php',
	'/inc/page-meta/admin/fields.php',
	'/inc/page-meta/admin/panels.php',
	'/inc/page-meta/admin/meta-box.php',
	'/inc/page-meta/admin/enqueue.php',
	// Theme settings admin.
	'/inc/admin/options-api.php',
	'/inc/admin/tabs.php',
	'/inc/admin/fields.php',
	'/inc/admin/sanitize-chrome.php',
	'/inc/admin/save.php',
	'/inc/admin/render-shell.php',
	'/inc/admin/panels.php',
	'/inc/admin/bootstrap.php',
	// Activation + setup + assets (last).
	'/inc/activation.php',
	'/inc/core/setup.php',
	'/inc/core/enqueue.php',
);

foreach ( $altijara_includes as $file ) {
	$path = ALTIJARA_THEME_DIR . $file;
	if ( file_exists( $path ) ) {
		require_once $path;
	}
}
