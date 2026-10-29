<?php
/**
 * Settings panel: tag_archives.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render tag_archives settings panel.
 *
 * @param array<string, mixed> $ctx Panel context.
 */
function altijara_render_settings_panel_tag_archives( array $ctx ) {
	$opts    = $ctx['opts'];
	$defs    = $ctx['defs'];
	$header  = $ctx['header'];
	$footer  = $ctx['footer'];
	$article = $ctx['article'];
	$social  = $ctx['social'];
	$map     = $ctx['map'];

	?>
				<div class="altijara-callout">
					<strong><?php esc_html_e( 'Tag Archives', 'altijara' ); ?></strong>
					<p><?php esc_html_e( 'A tag page lists every post with that tag, in the same layout as search results. Control the sidebar here: advertisement, magazine issues, and most read.', 'altijara' ); ?></p>
				</div>
				<?php
				altijara_render_tag_sidebar_builder();
				?>
				<div class="altijara-panel-actions">
					<?php submit_button( __( 'Save Tag Archives', 'altijara' ), 'primary', 'submit', false ); ?>
				</div>


<?php
}
