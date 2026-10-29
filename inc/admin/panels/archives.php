<?php
/**
 * Settings panel: archives.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render archives settings panel.
 *
 * @param array<string, mixed> $ctx Panel context.
 */
function altijara_render_settings_panel_archives( array $ctx ) {
	$opts    = $ctx['opts'];
	$defs    = $ctx['defs'];
	$header  = $ctx['header'];
	$footer  = $ctx['footer'];
	$article = $ctx['article'];
	$social  = $ctx['social'];
	$map     = $ctx['map'];

	?>
				<div class="altijara-callout">
					<strong><?php esc_html_e( 'Archives', 'altijara' ); ?></strong>
					<p><?php esc_html_e( 'This screen does not change the site. Category pages are edited in Category Archives. The issues page is edited in Issues Archive. Search results are edited in Search Template. Tag pages are edited in Tag Archives.', 'altijara' ); ?></p>
				</div>
				<p>
					<a class="button button-primary" href="<?php echo esc_url( function_exists( 'altijara_theme_options_tab_url' ) ? altijara_theme_options_tab_url( 'category_archives' ) : admin_url( 'admin.php?page=altijara-theme&tab=category_archives' ) ); ?>"><?php esc_html_e( 'Category Archives', 'altijara' ); ?></a>
					<a class="button" href="<?php echo esc_url( function_exists( 'altijara_theme_options_tab_url' ) ? altijara_theme_options_tab_url( 'issues_archive' ) : admin_url( 'admin.php?page=altijara-theme&tab=issues_archive' ) ); ?>"><?php esc_html_e( 'Issues Archive', 'altijara' ); ?></a>
					<a class="button" href="<?php echo esc_url( function_exists( 'altijara_theme_options_tab_url' ) ? altijara_theme_options_tab_url( 'search_template' ) : admin_url( 'admin.php?page=altijara-theme&tab=search_template' ) ); ?>"><?php esc_html_e( 'Search Template', 'altijara' ); ?></a>
					<a class="button" href="<?php echo esc_url( function_exists( 'altijara_theme_options_tab_url' ) ? altijara_theme_options_tab_url( 'tag_archives' ) : admin_url( 'admin.php?page=altijara-theme&tab=tag_archives' ) ); ?>"><?php esc_html_e( 'Tag Archives', 'altijara' ); ?></a>
				</p>

<?php
}
