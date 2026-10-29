<?php
/**
 * Settings panel: header.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render header settings panel.
 *
 * @param array<string, mixed> $ctx Panel context.
 */
function altijara_render_settings_panel_header( array $ctx ) {
	$opts    = $ctx['opts'];
	$defs    = $ctx['defs'];
	$header  = $ctx['header'];
	$footer  = $ctx['footer'];
	$article = $ctx['article'];
	$social  = $ctx['social'];
	$map     = $ctx['map'];

	?>
				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Logos', 'altijara' ); ?></h2>
					<table class="form-table">
						<?php
						altijara_admin_media_field( 'altijara_header[logo_url]', $header['logo_url'] ?? '', __( 'Arabic logo', 'altijara' ), __( 'Shown in the header and side drawer on the Arabic site.', 'altijara' ) );
						altijara_admin_media_field( 'altijara_header[logo_hover_url]', $header['logo_hover_url'] ?? '', __( 'Arabic hover logo', 'altijara' ), __( 'Replaces the Arabic logo on mouse hover.', 'altijara' ) );
						altijara_admin_media_field( 'altijara_header[logo_url_en]', $header['logo_url_en'] ?? '', __( 'English logo', 'altijara' ), __( 'Shown in the header and side drawer on the English site. If empty, the Arabic logo is used.', 'altijara' ) );
						altijara_admin_media_field( 'altijara_header[logo_hover_url_en]', $header['logo_hover_url_en'] ?? '', __( 'English hover logo', 'altijara' ), __( 'Replaces the English logo on mouse hover. If empty, the English logo stays as it is.', 'altijara' ) );
						?>
						<tr>
							<th><?php esc_html_e( 'Logo size (optional)', 'altijara' ); ?></th>
							<td class="altijara-inline-row">
								<label><?php esc_html_e( 'Width', 'altijara' ); ?> <input type="number" min="0" name="altijara_header[logo_width]" value="<?php echo esc_attr( (string) (int) ( $header['logo_width'] ?? 0 ) ); ?>" style="width:80px"></label>
								<label><?php esc_html_e( 'Height', 'altijara' ); ?> <input type="number" min="0" name="altijara_header[logo_height]" value="<?php echo esc_attr( (string) (int) ( $header['logo_height'] ?? 0 ) ); ?>" style="width:80px"></label>
							</td>
						</tr>
					</table>
				</div>

				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Background & layout', 'altijara' ); ?></h2>
					<table class="form-table">
						<?php altijara_admin_color_field( 'altijara_header[background]', $header['background'] ?? '', __( 'Header background', 'altijara' ), '#ffffff', 'header-bg' ); ?>
						<tr>
							<th><?php esc_html_e( 'Layout style', 'altijara' ); ?></th>
							<td>
								<select name="altijara_header[layout]">
									<option value="default" <?php selected( $header['layout'] ?? '', 'default' ); ?>><?php esc_html_e( 'Default', 'altijara' ); ?></option>
								</select>
							</td>
						</tr>
					</table>
				</div>

				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'What appears in the header', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Toggle which elements are visible on desktop and mobile.', 'altijara' ); ?></p>
					<div class="altijara-toggle-grid">
						<label class="altijara-toggle-card"><input type="checkbox" name="altijara_header[sticky]" value="1" <?php checked( ! empty( $header['sticky'] ) ); ?>><span><?php esc_html_e( 'Sticky header on scroll', 'altijara' ); ?></span></label>
						<label class="altijara-toggle-card"><input type="checkbox" name="altijara_header[show_top_bar]" value="1" <?php checked( ! empty( $header['show_top_bar'] ) ); ?>><span><?php esc_html_e( 'Top utility bar', 'altijara' ); ?></span></label>
						<label class="altijara-toggle-card"><input type="checkbox" name="altijara_header[show_date]" value="1" <?php checked( ! empty( $header['show_date'] ) ); ?>><span><?php esc_html_e( 'Today’s date', 'altijara' ); ?></span></label>
						<label class="altijara-toggle-card"><input type="checkbox" name="altijara_header[show_social]" value="1" <?php checked( ! empty( $header['show_social'] ) ); ?>><span><?php esc_html_e( 'Social icons', 'altijara' ); ?></span></label>
						<label class="altijara-toggle-card"><input type="checkbox" name="altijara_header[show_search]" value="1" <?php checked( ! empty( $header['show_search'] ) ); ?>><span><?php esc_html_e( 'Search button', 'altijara' ); ?></span></label>
						<label class="altijara-toggle-card"><input type="checkbox" name="altijara_header[show_login]" value="1" <?php checked( ! empty( $header['show_login'] ) ); ?>><span><?php esc_html_e( 'Login button', 'altijara' ); ?></span></label>
						<label class="altijara-toggle-card"><input type="checkbox" name="altijara_header[show_subscribe]" value="1" <?php checked( ! empty( $header['show_subscribe'] ) ); ?>><span><?php esc_html_e( 'Subscribe button', 'altijara' ); ?></span></label>
						<label class="altijara-toggle-card"><input type="checkbox" name="altijara_header[mobile_show_search]" value="1" <?php checked( ! empty( $header['mobile_show_search'] ) ); ?>><span><?php esc_html_e( 'Search on mobile', 'altijara' ); ?></span></label>
						<label class="altijara-toggle-card"><input type="checkbox" name="altijara_header[mobile_show_subscribe]" value="1" <?php checked( ! empty( $header['mobile_show_subscribe'] ) ); ?>><span><?php esc_html_e( 'Subscribe on mobile', 'altijara' ); ?></span></label>
					</div>
					<p class="description"><?php esc_html_e( 'Subscribe button also requires Theme Options → Subscriptions → Enable subscriptions.', 'altijara' ); ?></p>
				</div>

				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Billboard ad & actions', 'altijara' ); ?></h2>
					<table class="form-table">
						<tr>
							<th><?php esc_html_e( 'Top bar text', 'altijara' ); ?></th>
							<td><input class="regular-text" type="text" name="altijara_header[top_bar_text]" value="<?php echo esc_attr( $header['top_bar_text'] ?? '' ); ?>"></td>
						</tr>
						<tr>
							<th><label for="altijara_header_ad_shortcode"><?php esc_html_e( 'Billboard ad shortcode', 'altijara' ); ?></label></th>
							<td>
								<input class="large-text code" type="text" id="altijara_header_ad_shortcode" name="altijara_header[ad_shortcode]" value="<?php echo esc_attr( $header['ad_shortcode'] ?? '' ); ?>" placeholder='[adning id="6690"]'>
								<p class="description"><?php esc_html_e( 'Paste any ad shortcode. Fresh theme seed starts with [adning id="6690"] — you can clear it or replace it; the theme will not put it back. Runs on every page including /issues/. If the plugin returns nothing, the fallback image or placeholder is shown.', 'altijara' ); ?></p>
							</td>
						</tr>
						<?php altijara_admin_media_field( 'altijara_header[ad_image]', $header['ad_image'] ?? '', __( 'Fallback ad image (optional)', 'altijara' ), __( 'Shown when the shortcode is empty or the ad plugin outputs nothing on that page.', 'altijara' ) ); ?>
						<tr>
							<th><?php esc_html_e( 'Fallback ad link', 'altijara' ); ?></th>
							<td><input class="regular-text" type="url" name="altijara_header[ad_url]" value="<?php echo esc_attr( $header['ad_url'] ?? '' ); ?>"></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Login button', 'altijara' ); ?></th>
							<td class="altijara-inline-row">
								<input class="regular-text" type="text" name="altijara_header[login_text]" value="<?php echo esc_attr( $header['login_text'] ?? '' ); ?>" placeholder="Login">
								<input class="regular-text" type="url" name="altijara_header[login_url]" value="<?php echo esc_attr( $header['login_url'] ?? '' ); ?>" placeholder="<?php echo esc_attr( home_url( '/login/' ) ); ?>">
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Subscribe button', 'altijara' ); ?></th>
							<td class="altijara-inline-row">
								<input class="regular-text" type="text" name="altijara_header[subscribe_text]" value="<?php echo esc_attr( $header['subscribe_text'] ?? '' ); ?>">
								<input class="regular-text" type="url" name="altijara_header[subscribe_url]" value="<?php echo esc_attr( $header['subscribe_url'] ?? '' ); ?>" placeholder="<?php echo esc_attr( home_url( '/subscribe/' ) ); ?>">
							</td>
						</tr>
					</table>
				</div>
				<?php submit_button( __( 'Save Header', 'altijara' ) ); ?>
				<button type="submit" name="altijara_reset_group" value="header" class="button" onclick="return confirm('Reset header settings to defaults?');"><?php esc_html_e( 'Reset to defaults', 'altijara' ); ?></button>

<?php
}
