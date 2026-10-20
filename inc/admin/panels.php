<?php
/**
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render one settings tab panel body (inside form).
 *
 * @param string               $id  Tab id.
 * @param array<string, mixed> $ctx Panel context.
 */
function altijara_render_settings_panel( $id, array $ctx ) {
	$opts        = $ctx['opts'];
	$defs        = $ctx['defs'];
	$header      = $ctx['header'];
	$footer      = $ctx['footer'];
	$general     = $ctx['general'];
	$article     = $ctx['article'];
	$magazine    = $ctx['magazine'];
	$advertising = $ctx['advertising'];
	$social      = $ctx['social'];
	$map         = $ctx['map'];

	?>
		<?php if ( 'general' === $id ) : ?>
				<table class="form-table">
					<tr>
						<th><label for="site_tagline_ar"><?php esc_html_e( 'Site tagline', 'altijara' ); ?></label></th>
						<td>
							<input class="regular-text" type="text" id="site_tagline_ar" name="altijara_general[site_tagline_ar]" value="<?php echo esc_attr( $general['site_tagline_ar'] ?? '' ); ?>">
							<p class="description"><?php esc_html_e( 'Short line next to the magazine name if needed.', 'altijara' ); ?></p>
						</td>
					</tr>
				</table>
				<h2><?php esc_html_e( 'Category map', 'altijara' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Maps stable theme keys (menus, defaults) to live WordPress categories. Homepage section cards pick categories directly; this map is the fallback for keys like business → your live slug/ID.', 'altijara' ); ?></p>
				<?php
				$cat_choices = function_exists( 'altijara_get_category_choices' ) ? altijara_get_category_choices() : array( '' => '—' );
				?>
				<table class="form-table">
					<?php foreach ( $map as $key => $value ) : ?>
						<?php
						$current = is_scalar( $value ) ? (string) $value : '';
						$selected = $current;
						if ( $current && ! is_numeric( $current ) && function_exists( 'altijara_resolve_category_id' ) ) {
							$rid = altijara_resolve_category_id( is_numeric( $current ) ? $current : (string) $key );
							// Prefer resolving via map value as slug.
							$term = get_term_by( 'slug', sanitize_title( $current ), 'category' );
							if ( $term && ! is_wp_error( $term ) ) {
								$selected = (string) (int) $term->term_id;
							} elseif ( $rid ) {
								$selected = (string) $rid;
							}
						}
						?>
						<tr>
							<th><label><?php echo esc_html( (string) $key ); ?></label></th>
							<td>
								<select name="altijara_category_map[<?php echo esc_attr( (string) $key ); ?>]" class="regular-text">
									<?php foreach ( $cat_choices as $val => $lab ) : ?>
										<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $selected, (string) $val ); ?>><?php echo esc_html( $lab ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<p class="altijara-admin-actions">
					<?php submit_button( __( 'Save General', 'altijara' ), 'primary', 'submit', false ); ?>
					<button type="submit" name="altijara_detect_category_map" class="button button-secondary" value="1"><?php esc_html_e( 'Detect from site', 'altijara' ); ?></button>
				</p>

			<?php elseif ( 'design' === $id ) : ?>
				<?php
				if ( ! function_exists( 'altijara_default_design' ) || ! function_exists( 'altijara_font_main_presets' ) ) {
					echo '<div class="notice notice-error"><p>' . esc_html__( 'Design module missing. Re-upload the full theme folder.', 'altijara' ) . '</p></div>';
				} else {
				$design = is_array( $opts['design'] ?? null ) ? array_merge( altijara_default_design(), $opts['design'] ) : altijara_default_design();
				$main_presets  = altijara_font_main_presets();
				$serif_presets = altijara_font_serif_presets();
				?>
				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Fonts', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Self-hosted defaults: IBM Plex Sans Arabic + Playfair Display (no Google Fonts).', 'altijara' ); ?></p>
					<table class="form-table">
						<tr>
							<th><label for="font_main"><?php esc_html_e( 'Body font', 'altijara' ); ?></label></th>
							<td>
								<select name="altijara_design[font_main]" id="font_main">
									<?php foreach ( $main_presets as $key => $preset ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $design['font_main'], $key ); ?>><?php echo esc_html( $preset['label'] ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="font_main_custom"><?php esc_html_e( 'Custom body font (CSS)', 'altijara' ); ?></label></th>
							<td>
								<input class="large-text" type="text" id="font_main_custom" name="altijara_design[font_main_custom]" value="<?php echo esc_attr( $design['font_main_custom'] ); ?>" placeholder="'IBM Plex Sans Arabic', Tahoma, sans-serif">
								<p class="description"><?php esc_html_e( 'Used only when “Custom” is selected.', 'altijara' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="font_serif"><?php esc_html_e( 'Display / title font', 'altijara' ); ?></label></th>
							<td>
								<select name="altijara_design[font_serif]" id="font_serif">
									<?php foreach ( $serif_presets as $key => $preset ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $design['font_serif'], $key ); ?>><?php echo esc_html( $preset['label'] ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="font_serif_custom"><?php esc_html_e( 'Custom display font (CSS)', 'altijara' ); ?></label></th>
							<td>
								<input class="large-text" type="text" id="font_serif_custom" name="altijara_design[font_serif_custom]" value="<?php echo esc_attr( $design['font_serif_custom'] ); ?>" placeholder="'Playfair Display', Georgia, serif">
							</td>
						</tr>
					</table>
				</div>

				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Color palette', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Use the color checker next to each field to pick a hex value.', 'altijara' ); ?></p>
					<table class="form-table">
						<?php
						$color_fields = array(
							'bg_salmon'       => array( __( 'Page background', 'altijara' ), '#FFF1E5' ),
							'bg_salmon_light' => array( __( 'Secondary light background', 'altijara' ), '#F2DFCE' ),
							'bg_card'         => array( __( 'Card background', 'altijara' ), '#FFF7EF' ),
							'ink_black'       => array( __( 'Primary text', 'altijara' ), '#262A33' ),
							'ink_secondary'   => array( __( 'Secondary text', 'altijara' ), '#3b424e' ),
							'ink_muted'       => array( __( 'Muted text', 'altijara' ), '#6e7787' ),
							'accent_brand'    => array( __( 'Brand accent', 'altijara' ), '#1897B8' ),
							'accent_hover'    => array( __( 'Accent hover', 'altijara' ), '#12748e' ),
							'accent_amber'    => array( __( 'Alert / amber', 'altijara' ), '#d97706' ),
							'footer_bg'       => array( __( 'Footer background (optional)', 'altijara' ), '#1a1a1a' ),
							'footer_text'     => array( __( 'Footer text (optional)', 'altijara' ), '#ffffff' ),
						);
						foreach ( $color_fields as $ckey => $meta ) :
							altijara_admin_color_field(
								'altijara_design[' . $ckey . ']',
								$design[ $ckey ] ?? '',
								$meta[0],
								$meta[1],
								'design-' . $ckey
							);
						endforeach;
						?>
					</table>
				</div>
				<?php submit_button( __( 'Save Design', 'altijara' ) ); ?>
				<button type="submit" name="altijara_reset_group" value="design" class="button" onclick="return confirm('Reset fonts and colors to defaults?');"><?php esc_html_e( 'Reset to defaults', 'altijara' ); ?></button>
				<?php } ?>

			<?php elseif ( 'header' === $id ) : ?>
				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Logos', 'altijara' ); ?></h2>
					<table class="form-table">
						<?php
						altijara_admin_media_field( 'altijara_header[logo_url]', $header['logo_url'] ?? '', __( 'Primary logo', 'altijara' ), __( 'Shown in the site header.', 'altijara' ) );
						altijara_admin_media_field( 'altijara_header[logo_hover_url]', $header['logo_hover_url'] ?? '', __( 'Hover logo (optional)', 'altijara' ), __( 'Replaces the primary logo on mouse hover.', 'altijara' ) );
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
								<input class="large-text code" type="text" id="altijara_header_ad_shortcode" name="altijara_header[ad_shortcode]" value="<?php echo esc_attr( $header['ad_shortcode'] ?? '' ); ?>" placeholder='[the_ad id="123"]'>
								<p class="description"><?php esc_html_e( 'Paste your ad plugin shortcode. Leave empty to show the ad-area placeholder.', 'altijara' ); ?></p>
							</td>
						</tr>
						<?php altijara_admin_media_field( 'altijara_header[ad_image]', $header['ad_image'] ?? '', __( 'Fallback ad image (optional)', 'altijara' ), __( 'Used only when no shortcode is set.', 'altijara' ) ); ?>
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

			<?php elseif ( 'navigation' === $id ) : ?>
				<p class="description"><?php esc_html_e( 'Primary section links should open category archives (/category/{slug}/), not Pages. Use Sync below after updating the category map.', 'altijara' ); ?></p>
				<?php
				$locs     = get_registered_nav_menus();
				$assigned = get_nav_menu_locations();
				?>
				<table class="widefat striped altijara-admin-table">
					<thead><tr><th><?php esc_html_e( 'Location', 'altijara' ); ?></th><th><?php esc_html_e( 'Assigned menu', 'altijara' ); ?></th></tr></thead>
					<tbody>
					<?php foreach ( $locs as $loc => $loc_label ) : ?>
						<tr>
							<td><?php echo esc_html( $loc_label ); ?> <code><?php echo esc_html( $loc ); ?></code></td>
							<td>
								<?php
								if ( ! empty( $assigned[ $loc ] ) ) {
									$m = wp_get_nav_menu_object( $assigned[ $loc ] );
									echo $m ? esc_html( $m->name ) : '—';
								} else {
									echo '—';
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<p class="altijara-admin-actions">
					<button type="submit" name="altijara_rebuild_primary_nav" class="button button-primary" value="1"><?php esc_html_e( 'Rebuild primary navbar (all categories)', 'altijara' ); ?></button>
					<button type="submit" name="altijara_rebuild_utility_nav" class="button button-primary" value="1"><?php esc_html_e( 'Rebuild top bar (About / Contact / Advertise)', 'altijara' ); ?></button>
					<button type="submit" name="altijara_sync_nav_categories" class="button" value="1"><?php esc_html_e( 'Sync menu → category URLs', 'altijara' ); ?></button>
					<a class="button" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php esc_html_e( 'Open Menus editor', 'altijara' ); ?></a>
					<a class="button" href="<?php echo esc_url( admin_url( 'nav-menus.php?action=locations' ) ); ?>"><?php esc_html_e( 'Manage locations', 'altijara' ); ?></a>
				</p>
				<p class="description"><?php esc_html_e( 'Primary: category archives. Top bar: من نحن → /about/ · اتصل بنا → /contact/ · أعلن معنا → /advertise/ (creates pages if missing).', 'altijara' ); ?></p>

			<?php elseif ( 'homepage' === $id ) : ?>
				<p class="description"><?php esc_html_e( 'Build the homepage like WPBakery: edit the fixed Hero grid, then add Cards or Blocks below and drag to reorder.', 'altijara' ); ?></p>
				<?php
				if ( function_exists( 'altijara_render_homepage_builder' ) ) {
					altijara_render_homepage_builder();
				}
				?>
				<div class="altijara-section-actions">
					<?php submit_button( __( 'Save Homepage Sections', 'altijara' ), 'primary', 'submit', false ); ?>
					<button type="submit" name="altijara_purge_cache" class="button button-secondary" value="1"><?php esc_html_e( 'Purge section cache', 'altijara' ); ?></button>
					<button type="submit" name="altijara_reset_sections" class="button" value="1" onclick="return confirm('Reset all homepage sections to defaults?');"><?php esc_html_e( 'Reset to defaults', 'altijara' ); ?></button>
				</div>

			<?php elseif ( 'archives' === $id ) : ?>
				<?php
				$archive_map     = is_array( $opts['archive_layouts'] ?? null ) ? $opts['archive_layouts'] : array();
				$layout_choices  = function_exists( 'altijara_archive_layout_choices' ) ? altijara_archive_layout_choices() : array( 'posts' => 'Posts', 'issues' => 'Issues' );
				$full_hero_ids   = function_exists( 'altijara_get_full_hero_category_ids' ) ? altijara_get_full_hero_category_ids() : array();
				$archive_cats    = get_categories(
					array(
						'hide_empty' => false,
						'orderby'    => 'name',
						'order'      => 'ASC',
					)
				);
				?>
				<div class="altijara-callout">
					<strong><?php esc_html_e( 'Category archives', 'altijara' ); ?></strong>
					<p><?php esc_html_e( 'Choose Posts archive or Magazine issues layout for each category. The CPT archive /issues/ always uses the issues layout.', 'altijara' ); ?></p>
				</div>
				<table class="widefat striped altijara-admin-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Category', 'altijara' ); ?></th>
							<th><?php esc_html_e( 'Slug', 'altijara' ); ?></th>
							<th><?php esc_html_e( 'Layout', 'altijara' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php if ( empty( $archive_cats ) || is_wp_error( $archive_cats ) ) : ?>
						<tr><td colspan="3"><?php esc_html_e( 'No categories found.', 'altijara' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $archive_cats as $cat ) : ?>
							<?php
							if ( ! $cat instanceof WP_Term ) {
								continue;
							}
							$tid      = (int) $cat->term_id;
							$selected = isset( $archive_map[ (string) $tid ] ) ? (string) $archive_map[ (string) $tid ] : 'posts';
							?>
							<tr>
								<td>
									<strong><?php echo esc_html( $cat->name ); ?></strong>
									<div class="description"><a href="<?php echo esc_url( get_category_link( $tid ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View archive', 'altijara' ); ?></a></div>
								</td>
								<td><code><?php echo esc_html( $cat->slug ); ?></code></td>
								<td>
									<select name="altijara_archive_layouts[<?php echo esc_attr( (string) $tid ); ?>]">
										<?php foreach ( $layout_choices as $lval => $llab ) : ?>
											<option value="<?php echo esc_attr( $lval ); ?>" <?php selected( $selected, $lval ); ?>><?php echo esc_html( $llab ); ?></option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
					</tbody>
				</table>

				<div class="altijara-admin-block" style="margin-top:28px;">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Posts hero layout', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Select categories that show a single full-width hero (no recommended side column). Unselected categories keep hero + 3 side posts. Defaults match the old prototype: سيارات، لقاءات، سياحة، المعارض.', 'altijara' ); ?></p>
					<?php if ( empty( $archive_cats ) || is_wp_error( $archive_cats ) ) : ?>
						<p><?php esc_html_e( 'No categories found.', 'altijara' ); ?></p>
					<?php else : ?>
						<div class="altijara-checkbox-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:8px 16px;margin:12px 0 8px;">
							<?php foreach ( $archive_cats as $cat ) : ?>
								<?php
								if ( ! $cat instanceof WP_Term ) {
									continue;
								}
								$tid = (int) $cat->term_id;
								?>
								<label style="display:flex;align-items:flex-start;gap:8px;">
									<input type="checkbox" name="altijara_archive_full_hero[]" value="<?php echo esc_attr( (string) $tid ); ?>" <?php checked( in_array( $tid, $full_hero_ids, true ) ); ?>>
									<span>
										<strong><?php echo esc_html( $cat->name ); ?></strong>
										<code style="display:block;font-size:11px;opacity:.75;"><?php echo esc_html( $cat->slug ); ?></code>
									</span>
								</label>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
				<?php submit_button( __( 'Save Archive Layouts', 'altijara' ) ); ?>

			<?php elseif ( 'footer' === $id ) : ?>
				<div class="altijara-callout">
					<strong><?php esc_html_e( 'Footer', 'altijara' ); ?></strong>
					<p><?php esc_html_e( 'The bottom bar on every page — logo, columns, social icons, and copyright.', 'altijara' ); ?></p>
				</div>
				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Brand', 'altijara' ); ?></h2>
					<table class="form-table">
						<?php altijara_admin_media_field( 'altijara_footer[logo_url]', $footer['logo_url'] ?? '', __( 'Footer logo', 'altijara' ), __( 'Usually the light/white logo for dark backgrounds.', 'altijara' ) ); ?>
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

			<?php elseif ( 'social' === $id ) : ?>
				<p class="description"><?php esc_html_e( 'Shared social links for header and footer. Disable a platform to hide it.', 'altijara' ); ?></p>
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
				<?php submit_button( __( 'Save Social Links', 'altijara' ) ); ?>
				<button type="submit" name="altijara_reset_group" value="social" class="button" onclick="return confirm('Reset social links to defaults?');"><?php esc_html_e( 'Reset to defaults', 'altijara' ); ?></button>

			<?php elseif ( 'article' === $id ) : ?>
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Related articles', 'altijara' ); ?></th>
						<td>
							<label><input type="checkbox" name="altijara_article[show_related]" value="1" <?php checked( ! empty( $article['show_related'] ) ); ?>> <?php esc_html_e( 'Show related posts', 'altijara' ); ?></label>
							<label style="margin-inline-start:12px;"><?php esc_html_e( 'Count', 'altijara' ); ?>
								<input type="number" min="1" max="12" name="altijara_article[related_count]" value="<?php echo esc_attr( (string) (int) ( $article['related_count'] ?? 2 ) ); ?>" style="width:70px">
							</label>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Save Article', 'altijara' ) ); ?>

			<?php elseif ( 'magazine' === $id ) : ?>
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'FlipHTML5 base URL', 'altijara' ); ?></th>
						<td><input class="regular-text" type="url" name="altijara_magazine[flip_base]" value="<?php echo esc_attr( $magazine['flip_base'] ?? '' ); ?>"></td>
					</tr>
				</table>
				<?php submit_button( __( 'Save Magazine', 'altijara' ) ); ?>

			<?php elseif ( 'advertising' === $id ) : ?>
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Sidebar ad', 'altijara' ); ?></th>
						<td><label><input type="checkbox" name="altijara_advertising[sidebar_enabled]" value="1" <?php checked( ! empty( $advertising['sidebar_enabled'] ) ); ?>> <?php esc_html_e( 'Enabled', 'altijara' ); ?></label></td>
					</tr>
					<tr>
						<th><label for="altijara_sidebar_ad_shortcode"><?php esc_html_e( 'Sidebar ad shortcode', 'altijara' ); ?></label></th>
						<td>
							<input class="large-text code" type="text" id="altijara_sidebar_ad_shortcode" name="altijara_advertising[sidebar_shortcode]" value="<?php echo esc_attr( $advertising['sidebar_shortcode'] ?? '' ); ?>" placeholder='[the_ad id="123"]'>
							<p class="description"><?php esc_html_e( 'Paste your ad plugin shortcode (category / article sidebar). Leave empty to use the fallback image or placeholder.', 'altijara' ); ?></p>
						</td>
					</tr>
					<?php altijara_admin_media_field( 'altijara_advertising[sidebar_image]', $advertising['sidebar_image'] ?? '', __( 'Fallback sidebar image', 'altijara' ), __( 'Used when no shortcode is set. Falls back to Header billboard image if empty.', 'altijara' ) ); ?>
					<tr>
						<th><?php esc_html_e( 'Fallback sidebar link', 'altijara' ); ?></th>
						<td><input class="regular-text" type="url" name="altijara_advertising[sidebar_url]" value="<?php echo esc_attr( $advertising['sidebar_url'] ?? '' ); ?>"></td>
					</tr>
				</table>
				<p class="description"><?php esc_html_e( 'Header billboard ad is configured under the Header tab.', 'altijara' ); ?></p>
				<?php submit_button( __( 'Save Advertising', 'altijara' ) ); ?>

			<?php else : ?>
				<div class="altijara-tools-grid">
					<div class="altijara-tool-card">
						<strong><?php esc_html_e( 'Purge cache', 'altijara' ); ?></strong>
						<p><?php esc_html_e( 'Clears cached homepage section queries so content changes show immediately.', 'altijara' ); ?></p>
						<button type="submit" name="altijara_purge_cache" class="button button-primary" value="1"><?php esc_html_e( 'Purge all theme cache', 'altijara' ); ?></button>
					</div>
					<div class="altijara-tool-card">
						<strong><?php esc_html_e( 'Re-run setup', 'altijara' ); ?></strong>
						<p><?php esc_html_e( 'Creates any missing default menus without duplicating existing ones.', 'altijara' ); ?></p>
						<button type="submit" name="altijara_rerun_setup" class="button button-secondary" value="1" onclick="return confirm('Re-run theme setup? Existing menus will not be duplicated.');">
							<?php esc_html_e( 'Re-run setup (safe)', 'altijara' ); ?>
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
			<?php endif; ?>
	<?php
}
