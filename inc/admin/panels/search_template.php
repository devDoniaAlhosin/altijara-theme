<?php
/**
 * Settings panel: search_template.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render search_template settings panel.
 *
 * @param array<string, mixed> $ctx Panel context.
 */
function altijara_render_settings_panel_search_template( array $ctx ) {
	$opts    = $ctx['opts'];
	$defs    = $ctx['defs'];
	$header  = $ctx['header'];
	$footer  = $ctx['footer'];
	$article = $ctx['article'];
	$social  = $ctx['social'];
	$map     = $ctx['map'];

	?>
				<div class="altijara-callout">
					<strong><?php esc_html_e( 'Search template', 'altijara' ); ?></strong>
					<p><?php esc_html_e( 'Control the sidebar on search results: advertisement, magazine issues, and most read. For Most read, pick a category or leave empty for the whole site. Show or hide the column, then add, remove, or reorder widgets. Saving here does not change category or issues archives.', 'altijara' ); ?></p>
				</div>
				<?php
				altijara_render_search_sidebar_builder();
				?>
				<div class="altijara-panel-actions">
					<?php submit_button( __( 'Save Search Template', 'altijara' ), 'primary', 'submit', false ); ?>
				</div>


<?php
}
