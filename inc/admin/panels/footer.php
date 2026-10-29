<?php
/**
 * Settings panel: footer.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render footer settings panel.
 *
 * @param array<string, mixed> $ctx Panel context.
 */
function altijara_render_settings_panel_footer( array $ctx ) {
	$opts    = $ctx['opts'];
	$defs    = $ctx['defs'];
	$header  = $ctx['header'];
	$footer  = $ctx['footer'];
	$article = $ctx['article'];
	$social  = $ctx['social'];
	$map     = $ctx['map'];

	?>
				<div class="altijara-callout">
					<strong><?php esc_html_e( 'Footer', 'altijara' ); ?></strong>
					<p><?php esc_html_e( 'The bottom bar on every page — logo, columns, social icons, and copyright.', 'altijara' ); ?></p>
				</div>
				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Brand', 'altijara' ); ?></h2>
					<table class="form-table">
						<?php
						altijara_admin_media_field( 'altijara_footer[logo_url]', $footer['logo_url'] ?? '', __( 'Arabic logo', 'altijara' ), __( 'Light logo for the dark footer on the Arabic site.', 'altijara' ) );
						altijara_admin_media_field( 'altijara_footer[logo_url_en]', $footer['logo_url_en'] ?? '', __( 'English logo', 'altijara' ), __( 'Light logo for the dark footer on the English site. If empty, the Arabic footer logo is used.', 'altijara' ) );
						?>
						<tr>
							<th><?php esc_html_e( 'About blurb', 'altijara' ); ?></th>
							<td>
								<textarea class="large-text" rows="3" name="altijara_footer[description]"><?php echo esc_textarea( $footer['description'] ?? '' ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Short line under the logo.', 'altijara' ); ?></p>
							</td>
						</tr>
					</table>
				</div>
				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'What appears in the footer', 'altijara' ); ?></h2>
					<div class="altijara-toggle-grid">
						<label class="altijara-toggle-card"><input type="checkbox" name="altijara_footer[show_brand]" value="1" <?php checked( ! empty( $footer['show_brand'] ) ); ?>><span><?php esc_html_e( 'Logo & blurb column', 'altijara' ); ?></span></label>
						<label class="altijara-toggle-card"><input type="checkbox" name="altijara_footer[show_sections]" value="1" <?php checked( ! empty( $footer['show_sections'] ) ); ?>><span><?php esc_html_e( 'Sections column', 'altijara' ); ?></span></label>
						<label class="altijara-toggle-card"><input type="checkbox" name="altijara_footer[show_quick]" value="1" <?php checked( ! empty( $footer['show_quick'] ) ); ?>><span><?php esc_html_e( 'Quick links column', 'altijara' ); ?></span></label>
						<label class="altijara-toggle-card"><input type="checkbox" name="altijara_footer[show_social]" value="1" <?php checked( ! empty( $footer['show_social'] ) ); ?>><span><?php esc_html_e( 'Social icons', 'altijara' ); ?></span></label>
					</div>
					<table class="form-table" style="margin-top:12px;">
						<tr>
							<th><?php esc_html_e( 'Column headings', 'altijara' ); ?></th>
							<td class="altijara-inline-fields">
								<label><?php esc_html_e( 'Sections', 'altijara' ); ?>
									<input class="regular-text" name="altijara_footer[sections_heading]" value="<?php echo esc_attr( $footer['sections_heading'] ?? '' ); ?>" placeholder="Sections">
								</label>
								<label><?php esc_html_e( 'Quick links', 'altijara' ); ?>
									<input class="regular-text" name="altijara_footer[quick_heading]" value="<?php echo esc_attr( $footer['quick_heading'] ?? '' ); ?>" placeholder="Useful links">
								</label>
								<label><?php esc_html_e( 'Social', 'altijara' ); ?>
									<input class="regular-text" name="altijara_footer[social_heading]" value="<?php echo esc_attr( $footer['social_heading'] ?? '' ); ?>" placeholder="Follow us">
								</label>
							</td>
						</tr>
					</table>
				</div>
				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Contact, copyright & colors', 'altijara' ); ?></h2>
					<table class="form-table">
						<tr>
							<th><?php esc_html_e( 'Contact details', 'altijara' ); ?></th>
							<td>
								<textarea class="large-text" rows="2" name="altijara_footer[contact_address]" placeholder="<?php esc_attr_e( 'Address', 'altijara' ); ?>"><?php echo esc_textarea( $footer['contact_address'] ?? '' ); ?></textarea><br>
								<input class="regular-text" type="email" name="altijara_footer[contact_email]" value="<?php echo esc_attr( $footer['contact_email'] ?? '' ); ?>" placeholder="email@example.com">
								<input class="regular-text" type="text" name="altijara_footer[contact_phone]" value="<?php echo esc_attr( $footer['contact_phone'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Phone', 'altijara' ); ?>">
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Copyright', 'altijara' ); ?></th>
							<td>
								<input class="large-text" type="text" name="altijara_footer[copyright]" value="<?php echo esc_attr( $footer['copyright'] ?? '' ); ?>">
								<p class="description"><?php esc_html_e( 'Use {year} to insert the current year automatically.', 'altijara' ); ?></p>
							</td>
						</tr>
						<?php
						altijara_admin_color_field( 'altijara_footer[background]', $footer['background'] ?? '', __( 'Footer background', 'altijara' ), '#1a1a1a', 'footer-bg' );
						altijara_admin_color_field( 'altijara_footer[text_color]', $footer['text_color'] ?? '', __( 'Footer text color', 'altijara' ), '#ffffff', 'footer-text' );
						?>
						<tr>
							<th><?php esc_html_e( 'Layout style', 'altijara' ); ?></th>
							<td>
								<select name="altijara_footer[layout]">
									<option value="default" <?php selected( $footer['layout'] ?? '', 'default' ); ?>><?php esc_html_e( 'Default', 'altijara' ); ?></option>
								</select>
							</td>
						</tr>
					</table>
				</div>
				<p class="description"><?php esc_html_e( 'Footer column links: Appearance → Menus → Manage Locations.', 'altijara' ); ?></p>
				<?php submit_button( __( 'Save Footer', 'altijara' ) ); ?>
				<button type="submit" name="altijara_reset_group" value="footer" class="button" onclick="return confirm('Reset footer settings to defaults?');"><?php esc_html_e( 'Reset to defaults', 'altijara' ); ?></button>


<?php
}
