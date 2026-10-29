<?php
/**
 * Settings panel: category_archives.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render category_archives settings panel.
 *
 * @param array<string, mixed> $ctx Panel context.
 */
function altijara_render_settings_panel_category_archives( array $ctx ) {
	$opts    = $ctx['opts'];
	$defs    = $ctx['defs'];
	$header  = $ctx['header'];
	$footer  = $ctx['footer'];
	$article = $ctx['article'];
	$social  = $ctx['social'];
	$map     = $ctx['map'];

	?>
				<div class="altijara-callout">
					<strong><?php esc_html_e( 'Category archives', 'altijara' ); ?></strong>
					<p><?php esc_html_e( 'Edit counts inside each module. Full-width hero is chosen in Posts hero layout below. Modules always pull posts from the category being viewed. The lower sidebar is the column beside the news list. Saving here does not change the issues archive.', 'altijara' ); ?></p>
				</div>
				<?php
				$include_children = function_exists( 'altijara_archive_include_children' ) ? altijara_archive_include_children() : true;
				?>
				<div class="altijara-admin-block" style="margin-top:28px;">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Subcategories', 'altijara' ); ?></h2>
					<input type="hidden" name="altijara_archive_include_children" value="0">
					<label>
						<input type="checkbox" name="altijara_archive_include_children" value="1" <?php checked( $include_children ); ?>>
						<?php esc_html_e( 'Include subcategories', 'altijara' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'On: the category archive also shows posts from child categories. Off: only posts in that category itself. This is not the Include/Exclude category filter on builder modules — each archive module below can override this subcategory setting with its own checkbox.', 'altijara' ); ?></p>
				</div>
				<?php
				altijara_render_archive_full_hero_fields();
				altijara_render_category_archives_builder();
				altijara_render_category_sidebar_builder();
				?>
				<div class="altijara-panel-actions">
					<?php submit_button( __( 'Save Category Archives', 'altijara' ), 'primary', 'submit', false ); ?>
				</div>


<?php
}
