<?php
/**
 * Settings panel: issues_archive.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render issues_archive settings panel.
 *
 * @param array<string, mixed> $ctx Panel context.
 */
function altijara_render_settings_panel_issues_archive( array $ctx ) {
	$opts    = $ctx['opts'];
	$defs    = $ctx['defs'];
	$header  = $ctx['header'];
	$footer  = $ctx['footer'];
	$article = $ctx['article'];
	$social  = $ctx['social'];
	$map     = $ctx['map'];

	?>
				<div class="altijara-callout">
					<strong><?php esc_html_e( 'Issues archive', 'altijara' ); ?></strong>
					<p><?php esc_html_e( 'Modules for /issues/. The lead hero always shows the highest issue number when Specific issues is empty — publish 731 and it replaces 730 automatically. Carousels follow the same number order (offset 1 on Recent skips the lead). Removing every module and saving keeps the page empty; use Reset to restore the theme preset. Saving here does not change category archives.', 'altijara' ); ?></p>
				</div>
				<?php
				altijara_render_issues_archive_builder( 'issues_archive_page' );
				?>
				<div class="altijara-panel-actions">
					<?php submit_button( __( 'Save Issues Archive', 'altijara' ), 'primary', 'submit', false ); ?>
				</div>


<?php
}
