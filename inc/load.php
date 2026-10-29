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
	'/inc/auth/oauth.php',
	'/inc/subscriptions/tiers.php',
	'/inc/mail/template.php',
	'/inc/auth/turnstile.php',
	'/inc/auth/email-verify.php',
	'/inc/auth/handlers.php',
	'/inc/auth/admin-login.php',
	'/inc/integrations/woocommerce.php',
	'/inc/content/nav.php',
	'/inc/content/archive-layouts.php',
	'/inc/content/issue-meta.php',
	'/inc/content/featured-video.php',
	'/inc/content/issue-ingest.php',
	'/inc/content/bookmarks.php',
	'/inc/content/author.php',
	// Shared layout builder (Home / Category / Issues).
	'/inc/layouts/context.php',
	'/inc/layouts/sanitize.php',
	'/inc/layouts/presets.php',
	'/inc/layouts/render.php',
	'/inc/layouts/admin.php',
	'/inc/layouts/home.php',
	'/inc/layouts/category.php',
	'/inc/layouts/article.php',
	'/inc/layouts/search.php',
	'/inc/layouts/tag.php',
	'/inc/layouts/issues.php',
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
	// Page meta CMS.
	'/inc/page-meta/schema.php',
	'/inc/page-meta/defaults.php',
	'/inc/page-meta/home.php',
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
	'/inc/admin/chrome.php',
	// Activation + setup + assets (last).
	'/inc/activation.php',
	'/inc/core/setup.php',
	'/inc/core/enqueue.php',
);

foreach ( $altijara_includes as $file ) {
	$path = ALTIJARA_THEME_DIR . $file;
	if ( file_exists( $path ) ) {
		require_once $path;
		continue;
	}
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error -- intentional missing-module signal.
		trigger_error(
			sprintf( 'Altijara theme include missing: %s', $file ),
			E_USER_WARNING
		);
	}
}
