<?php
/**
 * Settings panel: integrations.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render integrations settings panel.
 *
 * @param array<string, mixed> $ctx Panel context.
 */
function altijara_render_settings_panel_integrations( array $ctx ) {
	$opts    = $ctx['opts'];
	$defs    = $ctx['defs'];
	$header  = $ctx['header'];
	$footer  = $ctx['footer'];
	$article = $ctx['article'];
	$social  = $ctx['social'];
	$map     = $ctx['map'];

				$advanced   = is_array( $opts['advanced'] ?? null ) ? $opts['advanced'] : array();
				$adv_defs   = is_array( $defs['advanced'] ?? null ) ? $defs['advanced'] : array();
				$empty_defs = function_exists( 'altijara_empty_section_message_defaults' )
					? altijara_empty_section_message_defaults()
					: array(
						'message'  => '«{name}» — لا مقالات في هذا التصنيف بعد',
						'fallback' => 'لا توجد مقالات حالياً',
					);
				$empty_msg  = isset( $advanced['empty_section_message'] ) ? (string) $advanced['empty_section_message'] : $empty_defs['message'];
				$empty_fb   = isset( $advanced['empty_section_fallback'] ) ? (string) $advanced['empty_section_fallback'] : $empty_defs['fallback'];
				// Show migrated default in the admin field when still on the old {label} template.
				if ( '«{label}» — لا مقالات في هذا التصنيف بعد' === $empty_msg ) {
					$empty_msg = $empty_defs['message'];
				}

				$login_slug_default = isset( $adv_defs['admin_login_slug'] ) ? (string) $adv_defs['admin_login_slug'] : 'altijaramagazine-admin-login';
				$login_bg_default   = isset( $adv_defs['admin_login_bg'] ) ? (string) $adv_defs['admin_login_bg'] : '#FFF1E5';
				$login_slug         = isset( $advanced['admin_login_slug'] ) && '' !== trim( (string) $advanced['admin_login_slug'] )
					? (string) $advanced['admin_login_slug']
					: $login_slug_default;
				$login_logo         = isset( $advanced['admin_login_logo_url'] ) ? (string) $advanced['admin_login_logo_url'] : '';
				$login_bg           = isset( $advanced['admin_login_bg'] ) && '' !== trim( (string) $advanced['admin_login_bg'] )
					? (string) $advanced['admin_login_bg']
					: $login_bg_default;
				$login_url_preview  = function_exists( 'altijara_admin_login_url' )
					? altijara_admin_login_url()
					: home_url( '/' . $login_slug . '/' );
				?>
				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Admin login portal', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Custom WordPress admin login URL and branding. Member login at /login/ is unchanged.', 'altijara' ); ?></p>
					<table class="form-table">
						<tr>
							<th><label for="admin_login_slug"><?php esc_html_e( 'Login URL slug', 'altijara' ); ?></label></th>
							<td>
								<input type="text" class="regular-text" id="admin_login_slug" name="altijara_advanced[admin_login_slug]" value="<?php echo esc_attr( $login_slug ); ?>" placeholder="<?php echo esc_attr( $login_slug_default ); ?>">
								<p class="description">
									<?php
									printf(
										/* translators: %s: full login URL */
										esc_html__( 'Current portal: %s', 'altijara' ),
										'<code>' . esc_html( $login_url_preview ) . '</code>'
									);
									?>
								</p>
							</td>
						</tr>
						<?php
						if ( function_exists( 'altijara_admin_media_field' ) ) {
							altijara_admin_media_field(
								'altijara_advanced[admin_login_logo_url]',
								$login_logo,
								__( 'Login logo', 'altijara' ),
								__( 'Shown above the login form. Leave empty to use the dark header logo.', 'altijara' )
							);
						}
						if ( function_exists( 'altijara_admin_color_field' ) ) {
							altijara_admin_color_field(
								'altijara_advanced[admin_login_bg]',
								$login_bg,
								__( 'Login background', 'altijara' ),
								$login_bg_default,
								'admin_login_bg'
							);
						}
						?>
					</table>
				</div>
				<div class="altijara-panel-actions" style="margin-bottom:20px;">
					<?php submit_button( __( 'Save admin login', 'altijara' ), 'primary', 'submit', false ); ?>
				</div>
				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Empty Home widgets', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Shown when a visible Home section has no posts. Use {name} for the selected category/tag (e.g. مال وأعمال) and {label} for the section title.', 'altijara' ); ?></p>
					<table class="form-table">
						<tr>
							<th><label for="empty_section_message"><?php esc_html_e( 'Empty section message', 'altijara' ); ?></label></th>
							<td>
								<input type="text" class="large-text" id="empty_section_message" name="altijara_advanced[empty_section_message]" value="<?php echo esc_attr( $empty_msg ); ?>" placeholder="<?php echo esc_attr( $empty_defs['message'] ); ?>">
								<p class="description"><?php esc_html_e( 'Example: «{name}» — لا مقالات في هذا التصنيف بعد', 'altijara' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="empty_section_fallback"><?php esc_html_e( 'Fallback message', 'altijara' ); ?></label></th>
							<td>
								<input type="text" class="large-text" id="empty_section_fallback" name="altijara_advanced[empty_section_fallback]" value="<?php echo esc_attr( $empty_fb ); ?>" placeholder="<?php echo esc_attr( $empty_defs['fallback'] ); ?>">
								<p class="description"><?php esc_html_e( 'Used when the section has no label.', 'altijara' ); ?></p>
							</td>
						</tr>
					</table>
				</div>
				<div class="altijara-panel-actions" style="margin-bottom:20px;">
					<?php submit_button( __( 'Save empty messages', 'altijara' ), 'primary', 'submit', false ); ?>
				</div>
				<div class="altijara-tools-grid">
					<?php
					$utility_nav = function_exists( 'altijara_page_meta_utility_nav_items' )
						? altijara_page_meta_utility_nav_items()
						: array();
					if ( $utility_nav ) :
						?>
						<div class="altijara-tool-card">
							<strong><?php esc_html_e( 'Fixed pages', 'altijara' ); ?></strong>
							<p><?php esc_html_e( 'Edit About, Contact, Advertise, and other template pages. Content lives in Altijara Page Settings on each page.', 'altijara' ); ?></p>
							<p class="altijara-admin-actions" style="display:flex;flex-wrap:wrap;gap:8px;margin:0;">
								<?php foreach ( $utility_nav as $item ) : ?>
									<a class="button" href="<?php echo esc_url( $item['edit'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
								<?php endforeach; ?>
							</p>
						</div>
					<?php endif; ?>
					<div class="altijara-tool-card">
						<strong><?php esc_html_e( 'Purge cache', 'altijara' ); ?></strong>
						<p><?php esc_html_e( 'Clears cached homepage section queries so content changes show immediately.', 'altijara' ); ?></p>
						<button type="submit" name="altijara_purge_cache" class="button button-primary" value="1"><?php esc_html_e( 'Purge all theme cache', 'altijara' ); ?></button>
					</div>
					<div class="altijara-tool-card">
						<strong><?php esc_html_e( 'Re-run setup', 'altijara' ); ?></strong>
						<p><?php esc_html_e( 'Full fresh-start seed: resets Theme Options to defaults, ensures utility pages, rebuilds primary / top bar / footer menus, overwrites category map, reseeds Home 2 layout, and sets Home 2 as the front page. Uploading/replacing the theme ZIP runs this automatically.', 'altijara' ); ?></p>
						<button type="submit" name="altijara_rerun_setup" class="button button-secondary" value="1" onclick="return confirm('Re-run full theme setup? Menus will be rebuilt, category map overwritten, and Home 2 forced as the front page.');">
							<?php esc_html_e( 'Run theme setup now', 'altijara' ); ?>
						</button>
					</div>
					<div class="altijara-tool-card">
						<strong><?php esc_html_e( 'Menus', 'altijara' ); ?></strong>
						<p><?php esc_html_e( 'Add, remove, and reorder items under Appearance → Menus. The hamburger drawer uses Drawer Sections (Top) and Top Bar / Drawer Links (Bottom). Nest items under a parent in the top menu to show an expand arrow in the drawer.', 'altijara' ); ?></p>
						<p><?php esc_html_e( 'Note: Rebuild primary also reassigns the Drawer Sections (Top) location to the primary menu.', 'altijara' ); ?></p>
						<p class="altijara-admin-actions" style="display:flex;flex-wrap:wrap;gap:8px;margin:0 0 10px;">
							<a class="button button-primary" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php esc_html_e( 'Open Menus editor', 'altijara' ); ?></a>
							<a class="button" href="<?php echo esc_url( admin_url( 'nav-menus.php?action=locations' ) ); ?>"><?php esc_html_e( 'Manage locations', 'altijara' ); ?></a>
						</p>
						<p class="altijara-admin-actions" style="display:flex;flex-wrap:wrap;gap:8px;margin:0;">
							<button type="submit" name="altijara_rebuild_primary_nav" class="button" value="1"><?php esc_html_e( 'Rebuild primary', 'altijara' ); ?></button>
							<button type="submit" name="altijara_rebuild_utility_nav" class="button" value="1"><?php esc_html_e( 'Rebuild top bar', 'altijara' ); ?></button>
							<button type="submit" name="altijara_rebuild_footer_nav" class="button" value="1"><?php esc_html_e( 'Rebuild footer', 'altijara' ); ?></button>
							<button type="submit" name="altijara_sync_nav_categories" class="button" value="1"><?php esc_html_e( 'Sync → category URLs', 'altijara' ); ?></button>
						</p>
					</div>
					<div class="altijara-tool-card">
						<strong><?php esc_html_e( 'Sync magazine issues', 'altijara' ); ?></strong>
						<p><?php esc_html_e( 'Creates/updates issue posts from issues_data.json and attaches featured images already in the Media Library.', 'altijara' ); ?></p>
						<button type="submit" name="altijara_ingest_issues" class="button button-secondary" value="1">
							<?php esc_html_e( 'Import / sync issues now', 'altijara' ); ?>
						</button>
					</div>
				</div>
				<?php
				$setup_flag = defined( 'ALTIJARA_SETUP_FLAG' ) ? ALTIJARA_SETUP_FLAG : 'altijara_theme_setup_state';
				$state      = get_option( $setup_flag, array() );
				if ( ! empty( $state['last_report'] ) && is_array( $state['last_report'] ) ) :
					?>
					<h3><?php esc_html_e( 'Last setup report', 'altijara' ); ?></h3>
					<pre class="altijara-setup-report"><?php echo esc_html( wp_json_encode( $state['last_report'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ); ?></pre>
				<?php endif; ?>

<?php
}
