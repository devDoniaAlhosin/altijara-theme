<?php
/**
 * Settings panel: social.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render social settings panel.
 *
 * @param array<string, mixed> $ctx Panel context.
 */
function altijara_render_settings_panel_social( array $ctx ) {
	$opts    = $ctx['opts'];
	$defs    = $ctx['defs'];
	$header  = $ctx['header'];
	$footer  = $ctx['footer'];
	$article = $ctx['article'];
	$social  = $ctx['social'];
	$map     = $ctx['map'];

	?>
				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Social links', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Shared profile links for header and footer. Disable a platform to hide it.', 'altijara' ); ?></p>
				<table class="widefat striped altijara-admin-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Enabled', 'altijara' ); ?></th>
							<th><?php esc_html_e( 'Platform', 'altijara' ); ?></th>
							<th><?php esc_html_e( 'Label', 'altijara' ); ?></th>
							<th><?php esc_html_e( 'URL', 'altijara' ); ?></th>
							<th><?php esc_html_e( 'Order', 'altijara' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$platforms = altijara_social_platform_choices();
						foreach ( $social as $i => $item ) :
							?>
							<tr>
								<td><input type="checkbox" name="altijara_social[items][<?php echo (int) $i; ?>][enabled]" value="1" <?php checked( ! empty( $item['enabled'] ) ); ?>></td>
								<td>
									<select name="altijara_social[items][<?php echo (int) $i; ?>][platform]">
										<?php foreach ( $platforms as $pk => $pl ) : ?>
											<option value="<?php echo esc_attr( $pk ); ?>" <?php selected( $item['platform'] ?? '', $pk ); ?>><?php echo esc_html( $pl ); ?></option>
										<?php endforeach; ?>
									</select>
								</td>
								<td><input type="text" name="altijara_social[items][<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr( $item['label'] ?? '' ); ?>" class="regular-text"></td>
								<td><input type="url" name="altijara_social[items][<?php echo (int) $i; ?>][url]" value="<?php echo esc_attr( $item['url'] ?? '' ); ?>" class="regular-text"></td>
								<td><input type="number" name="altijara_social[items][<?php echo (int) $i; ?>][order]" value="<?php echo esc_attr( (string) (int) ( $item['order'] ?? 10 ) ); ?>" style="width:70px"></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<div class="altijara-panel-actions" style="margin:0 16px 14px;border-top:0;padding-top:8px;background:transparent;">
					<?php submit_button( __( 'Save Social Links', 'altijara' ), 'primary', 'submit', false ); ?>
					<button type="submit" name="altijara_reset_group" value="social" class="button" onclick="return confirm('Reset social links to defaults?');"><?php esc_html_e( 'Reset to defaults', 'altijara' ); ?></button>
				</div>
				</div>

				<?php
				$auth = function_exists( 'altijara_get_auth_settings' ) ? altijara_get_auth_settings() : array();
				$cb   = function_exists( 'altijara_oauth_callback_url' ) ? altijara_oauth_callback_url() : home_url( '/?altijara_oauth_callback=1' );
				?>
				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Social login (Google, Facebook & Apple)', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'A provider’s button is hidden on the login form while its checkbox is off. It becomes a working button after you enable it and save its keys.', 'altijara' ); ?></p>
					<p class="description"><strong><?php esc_html_e( 'Authorized redirect / Return URL (use in every console):', 'altijara' ); ?></strong><br><code><?php echo esc_html( $cb ); ?></code></p>
					<p class="description"><?php esc_html_e( 'Facebook: add that exact URL under Facebook Login → Settings → Valid OAuth Redirect URIs. App must be Live (or your Facebook user added as a tester). Enable the email permission. Google can succeed while Facebook fails if only the Facebook redirect URI / mode is wrong.', 'altijara' ); ?></p>
					<table class="form-table">
						<tr>
							<th><?php esc_html_e( 'Google', 'altijara' ); ?></th>
							<td>
								<label style="display:block;margin-bottom:10px;">
									<input type="checkbox" name="altijara_auth[google_enabled]" value="1" <?php checked( ! empty( $auth['google_enabled'] ) ); ?>>
									<?php esc_html_e( 'Enable Google login', 'altijara' ); ?>
								</label>
								<p>
									<label><?php esc_html_e( 'Client ID', 'altijara' ); ?><br>
										<input class="large-text" type="text" name="altijara_auth[google_client_id]" value="<?php echo esc_attr( $auth['google_client_id'] ?? '' ); ?>" autocomplete="off">
									</label>
								</p>
								<p>
									<label><?php esc_html_e( 'Client Secret', 'altijara' ); ?><br>
										<input class="large-text" type="password" name="altijara_auth[google_client_secret]" value="<?php echo esc_attr( $auth['google_client_secret'] ?? '' ); ?>" autocomplete="new-password">
									</label>
								</p>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Facebook', 'altijara' ); ?></th>
							<td>
								<label style="display:block;margin-bottom:10px;">
									<input type="checkbox" name="altijara_auth[facebook_enabled]" value="1" <?php checked( ! empty( $auth['facebook_enabled'] ) ); ?>>
									<?php esc_html_e( 'Enable Facebook login', 'altijara' ); ?>
								</label>
								<p>
									<label><?php esc_html_e( 'App ID', 'altijara' ); ?><br>
										<input class="large-text" type="text" name="altijara_auth[facebook_app_id]" value="<?php echo esc_attr( $auth['facebook_app_id'] ?? '' ); ?>" autocomplete="off">
									</label>
								</p>
								<p>
									<label><?php esc_html_e( 'App Secret', 'altijara' ); ?><br>
										<input class="large-text" type="password" name="altijara_auth[facebook_app_secret]" value="<?php echo esc_attr( $auth['facebook_app_secret'] ?? '' ); ?>" autocomplete="new-password">
									</label>
								</p>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Apple', 'altijara' ); ?></th>
							<td>
								<label style="display:block;margin-bottom:10px;">
									<input type="checkbox" name="altijara_auth[apple_enabled]" value="1" <?php checked( ! empty( $auth['apple_enabled'] ) ); ?>>
									<?php esc_html_e( 'Enable Apple login', 'altijara' ); ?>
								</label>
								<p class="description"><?php esc_html_e( 'From Apple Developer: a Services ID (this is the client ID), your Team ID, a Sign in with Apple key, and the contents of its .p8 file. Register the return URL above on that Services ID.', 'altijara' ); ?></p>
								<p>
									<label><?php esc_html_e( 'Services ID', 'altijara' ); ?><br>
										<input class="large-text" type="text" name="altijara_auth[apple_client_id]" value="<?php echo esc_attr( $auth['apple_client_id'] ?? '' ); ?>" autocomplete="off">
									</label>
								</p>
								<p>
									<label><?php esc_html_e( 'Team ID', 'altijara' ); ?><br>
										<input class="regular-text" type="text" name="altijara_auth[apple_team_id]" value="<?php echo esc_attr( $auth['apple_team_id'] ?? '' ); ?>" autocomplete="off">
									</label>
								</p>
								<p>
									<label><?php esc_html_e( 'Key ID', 'altijara' ); ?><br>
										<input class="regular-text" type="text" name="altijara_auth[apple_key_id]" value="<?php echo esc_attr( $auth['apple_key_id'] ?? '' ); ?>" autocomplete="off">
									</label>
								</p>
								<p>
									<label><?php esc_html_e( 'Private key (.p8)', 'altijara' ); ?><br>
										<textarea class="large-text code" name="altijara_auth[apple_private_key]" rows="8" spellcheck="false" autocomplete="off"><?php echo esc_textarea( $auth['apple_private_key'] ?? '' ); ?></textarea>
									</label>
								</p>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Save Social Login', 'altijara' ) ); ?>
				</div>

				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Cloudflare Turnstile (bot protection)', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Protects login, register, reset, and subscribe forms. Leave disabled until you paste Site Key and Secret Key from the Cloudflare Turnstile dashboard.', 'altijara' ); ?></p>
					<table class="form-table">
						<tr>
							<th><?php esc_html_e( 'Turnstile', 'altijara' ); ?></th>
							<td>
								<label style="display:block;margin-bottom:10px;">
									<input type="checkbox" name="altijara_auth[turnstile_enabled]" value="1" <?php checked( ! empty( $auth['turnstile_enabled'] ) ); ?>>
									<?php esc_html_e( 'Enable Turnstile on auth forms', 'altijara' ); ?>
								</label>
								<p>
									<label><?php esc_html_e( 'Site Key', 'altijara' ); ?><br>
										<input class="large-text" type="text" name="altijara_auth[turnstile_site_key]" value="<?php echo esc_attr( $auth['turnstile_site_key'] ?? '' ); ?>" autocomplete="off">
									</label>
								</p>
								<p>
									<label><?php esc_html_e( 'Secret Key', 'altijara' ); ?><br>
										<input class="large-text" type="password" name="altijara_auth[turnstile_secret_key]" value="<?php echo esc_attr( $auth['turnstile_secret_key'] ?? '' ); ?>" autocomplete="new-password">
									</label>
								</p>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Save Turnstile', 'altijara' ) ); ?>
				</div>


<?php
}
