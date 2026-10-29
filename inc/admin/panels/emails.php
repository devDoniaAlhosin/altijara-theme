<?php
/**
 * Settings panel: transactional emails (builder + live preview).
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render emails settings panel.
 *
 * @param array<string, mixed> $ctx Panel context.
 */
function altijara_render_settings_panel_emails( array $ctx ) {
	$opts    = isset( $ctx['opts'] ) && is_array( $ctx['opts'] ) ? $ctx['opts'] : array();
	$defs    = isset( $ctx['defs']['emails'] ) && is_array( $ctx['defs']['emails'] ) ? $ctx['defs']['emails'] : array();
	$emails  = isset( $opts['emails'] ) && is_array( $opts['emails'] ) ? array_merge( $defs, $opts['emails'] ) : $defs;
	$preview = function_exists( 'altijara_mail_build_preview' )
		? altijara_mail_build_preview( 'reset', $emails )
		: array(
			'type'    => 'reset',
			'subject' => (string) ( $emails['reset_subject'] ?? '' ),
			'html'    => '',
		);
	?>
	<div class="altijara-mail-builder" id="altijaraMailBuilder" data-preview-type="reset">
		<div class="altijara-mail-builder__editor">
			<div class="altijara-admin-block altijara-mail-builder__brand">
				<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Branding', 'altijara' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Shared shell for password reset and email verification. Empty logo falls back to the header logo, then the Customizer site logo.', 'altijara' ); ?></p>
				<table class="form-table">
					<?php
					altijara_admin_media_field(
						'altijara_emails[logo_url]',
						$emails['logo_url'] ?? '',
						__( 'Email logo', 'altijara' ),
						__( 'Recommended: Altijara Arabic logo on a light background.', 'altijara' )
					);
					?>
					<tr>
						<th><label for="altijara_emails_from_name"><?php esc_html_e( 'From name', 'altijara' ); ?></label></th>
						<td>
							<input id="altijara_emails_from_name" class="regular-text" type="text" name="altijara_emails[from_name]" value="<?php echo esc_attr( (string) ( $emails['from_name'] ?? '' ) ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" data-mail-field="from_name">
							<p class="description"><?php esc_html_e( 'Optional. Defaults to the site name.', 'altijara' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="altijara_emails_footer_text"><?php esc_html_e( 'Footer text', 'altijara' ); ?></label></th>
						<td>
							<input id="altijara_emails_footer_text" class="large-text" type="text" name="altijara_emails[footer_text]" value="<?php echo esc_attr( (string) ( $emails['footer_text'] ?? '' ) ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" data-mail-field="footer_text">
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'CTA link', 'altijara' ); ?></th>
						<td>
							<label class="altijara-mail-toggle">
								<input type="checkbox" name="altijara_emails[show_raw_link]" value="1" <?php checked( ! empty( $emails['show_raw_link'] ) ); ?> data-mail-field="show_raw_link">
								<?php esc_html_e( 'Also show the plain URL under the button (for email clients that strip buttons)', 'altijara' ); ?>
							</label>
						</td>
					</tr>
				</table>
			</div>

			<div class="altijara-mail-builder__templates" role="tablist" aria-label="<?php esc_attr_e( 'Email templates', 'altijara' ); ?>">
				<button type="button" class="altijara-mail-tab is-active" role="tab" aria-selected="true" data-mail-type="reset">
					<span class="dashicons dashicons-unlock" aria-hidden="true"></span>
					<?php esc_html_e( 'Password reset', 'altijara' ); ?>
				</button>
				<button type="button" class="altijara-mail-tab" role="tab" aria-selected="false" data-mail-type="verify">
					<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
					<?php esc_html_e( 'Email verification', 'altijara' ); ?>
				</button>
			</div>

			<div class="altijara-admin-block altijara-mail-pane is-active" data-mail-pane="reset" role="tabpanel">
				<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Password reset', 'altijara' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Sent when a reader requests a new password from the login / reset form.', 'altijara' ); ?></p>
				<table class="form-table">
					<tr>
						<th><label for="altijara_emails_reset_subject"><?php esc_html_e( 'Subject', 'altijara' ); ?></label></th>
						<td><input id="altijara_emails_reset_subject" class="large-text" type="text" name="altijara_emails[reset_subject]" value="<?php echo esc_attr( (string) ( $emails['reset_subject'] ?? '' ) ); ?>" data-mail-field="reset_subject"></td>
					</tr>
					<tr>
						<th><label for="altijara_emails_reset_heading"><?php esc_html_e( 'Heading', 'altijara' ); ?></label></th>
						<td><input id="altijara_emails_reset_heading" class="large-text" type="text" name="altijara_emails[reset_heading]" value="<?php echo esc_attr( (string) ( $emails['reset_heading'] ?? '' ) ); ?>" data-mail-field="reset_heading"></td>
					</tr>
					<tr>
						<th><label for="altijara_emails_reset_intro"><?php esc_html_e( 'Intro', 'altijara' ); ?></label></th>
						<td><textarea id="altijara_emails_reset_intro" class="large-text" rows="4" name="altijara_emails[reset_intro]" data-mail-field="reset_intro"><?php echo esc_textarea( (string) ( $emails['reset_intro'] ?? '' ) ); ?></textarea></td>
					</tr>
					<tr>
						<th><label for="altijara_emails_reset_cta"><?php esc_html_e( 'Button label', 'altijara' ); ?></label></th>
						<td><input id="altijara_emails_reset_cta" class="regular-text" type="text" name="altijara_emails[reset_cta]" value="<?php echo esc_attr( (string) ( $emails['reset_cta'] ?? '' ) ); ?>" data-mail-field="reset_cta"></td>
					</tr>
				</table>
			</div>

			<div class="altijara-admin-block altijara-mail-pane" data-mail-pane="verify" role="tabpanel" hidden>
				<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Email verification', 'altijara' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Sent after registration when email verification is required.', 'altijara' ); ?></p>
				<table class="form-table">
					<tr>
						<th><label for="altijara_emails_verify_subject"><?php esc_html_e( 'Subject', 'altijara' ); ?></label></th>
						<td><input id="altijara_emails_verify_subject" class="large-text" type="text" name="altijara_emails[verify_subject]" value="<?php echo esc_attr( (string) ( $emails['verify_subject'] ?? '' ) ); ?>" data-mail-field="verify_subject"></td>
					</tr>
					<tr>
						<th><label for="altijara_emails_verify_heading"><?php esc_html_e( 'Heading', 'altijara' ); ?></label></th>
						<td><input id="altijara_emails_verify_heading" class="large-text" type="text" name="altijara_emails[verify_heading]" value="<?php echo esc_attr( (string) ( $emails['verify_heading'] ?? '' ) ); ?>" data-mail-field="verify_heading"></td>
					</tr>
					<tr>
						<th><label for="altijara_emails_verify_intro"><?php esc_html_e( 'Intro', 'altijara' ); ?></label></th>
						<td><textarea id="altijara_emails_verify_intro" class="large-text" rows="4" name="altijara_emails[verify_intro]" data-mail-field="verify_intro"><?php echo esc_textarea( (string) ( $emails['verify_intro'] ?? '' ) ); ?></textarea></td>
					</tr>
					<tr>
						<th><label for="altijara_emails_verify_cta"><?php esc_html_e( 'Button label', 'altijara' ); ?></label></th>
						<td><input id="altijara_emails_verify_cta" class="regular-text" type="text" name="altijara_emails[verify_cta]" value="<?php echo esc_attr( (string) ( $emails['verify_cta'] ?? '' ) ); ?>" data-mail-field="verify_cta"></td>
					</tr>
				</table>
			</div>

			<p class="submit altijara-mail-builder__submit">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Emails', 'altijara' ); ?></button>
				<span class="description"><?php esc_html_e( 'Preview updates as you type. Save to apply on the site.', 'altijara' ); ?></span>
			</p>
		</div>

		<aside class="altijara-mail-builder__preview" aria-label="<?php esc_attr_e( 'Email preview', 'altijara' ); ?>">
			<div class="altijara-mail-preview-card">
				<div class="altijara-mail-preview-toolbar">
					<div class="altijara-mail-preview-toolbar__title">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
						<strong><?php esc_html_e( 'Live preview', 'altijara' ); ?></strong>
					</div>
					<div class="altijara-mail-device" role="group" aria-label="<?php esc_attr_e( 'Preview width', 'altijara' ); ?>">
						<button type="button" class="altijara-mail-device__btn is-active" data-mail-device="desktop" aria-pressed="true"><?php esc_html_e( 'Desktop', 'altijara' ); ?></button>
						<button type="button" class="altijara-mail-device__btn" data-mail-device="mobile" aria-pressed="false"><?php esc_html_e( 'Mobile', 'altijara' ); ?></button>
					</div>
					<span class="altijara-mail-preview-status" data-mail-status data-state="ready"><?php esc_html_e( 'Ready', 'altijara' ); ?></span>
				</div>
				<div class="altijara-mail-preview-subject">
					<span><?php esc_html_e( 'Subject', 'altijara' ); ?></span>
					<strong data-mail-subject><?php echo esc_html( (string) ( $preview['subject'] ?? '' ) ); ?></strong>
				</div>
				<div class="altijara-mail-preview-frame" data-mail-device-frame="desktop">
					<iframe
						id="altijaraMailPreviewFrame"
						class="altijara-mail-preview-iframe"
						title="<?php esc_attr_e( 'Email HTML preview', 'altijara' ); ?>"
						sandbox="allow-same-origin"
					></iframe>
				</div>
				<textarea id="altijaraMailPreviewSeed" class="altijara-mail-preview-seed" hidden><?php echo esc_textarea( (string) ( $preview['html'] ?? '' ) ); ?></textarea>
			</div>
		</aside>
	</div>
	<?php
}
