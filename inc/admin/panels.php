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
				<?php
				$home2_edit = 0;
				if ( function_exists( 'altijara_get_static_front_page_id' ) ) {
					$home2_edit = altijara_get_static_front_page_id();
					if ( $home2_edit && 'home-2.php' !== (string) get_page_template_slug( $home2_edit ) ) {
						$home2_edit = 0;
					}
				}
				?>
				<p class="description">
					<?php esc_html_e( 'Homepage content (labels, counts, categories, specific posts) is edited on the Front Page using the “Home 2” template → Altijara Page Settings metabox.', 'altijara' ); ?>
					<?php if ( $home2_edit ) : ?>
						<a href="<?php echo esc_url( get_edit_post_link( $home2_edit ) ); ?>"><?php esc_html_e( 'Edit Home 2 page', 'altijara' ); ?></a>
					<?php else : ?>
						<?php esc_html_e( 'Run theme setup (Tools) or set Settings → Reading → Front page to a page with template Home 2.', 'altijara' ); ?>
					<?php endif; ?>
				</p>
				<p class="altijara-admin-actions">
					<?php submit_button( __( 'Save General', 'altijara' ), 'primary', 'submit', false ); ?>
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
				<p class="description"><?php esc_html_e( 'Primary section links should open category archives (/category/{slug}/), not Pages. Use Sync below if menu items point at Pages instead of categories.', 'altijara' ); ?></p>
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
					<button type="submit" name="altijara_rebuild_footer_nav" class="button button-primary" value="1"><?php esc_html_e( 'Rebuild footer menus', 'altijara' ); ?></button>
					<button type="submit" name="altijara_rerun_setup" class="button" value="1"><?php esc_html_e( 'Run theme setup now', 'altijara' ); ?></button>
					<button type="submit" name="altijara_sync_nav_categories" class="button" value="1"><?php esc_html_e( 'Sync menu → category URLs', 'altijara' ); ?></button>
					<a class="button" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php esc_html_e( 'Open Menus editor', 'altijara' ); ?></a>
					<a class="button" href="<?php echo esc_url( admin_url( 'nav-menus.php?action=locations' ) ); ?>"><?php esc_html_e( 'Manage locations', 'altijara' ); ?></a>
				</p>
				<p class="description"><?php esc_html_e( 'Primary: category archives. Top bar / footer: theme utility pages (about-2 if about already exists). Upload while active runs setup on version bump.', 'altijara' ); ?></p>

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
				$magazine_ids  = function_exists( 'altijara_get_archive_magazine_ids' ) ? altijara_get_archive_magazine_ids() : array();
				$full_hero_ids = function_exists( 'altijara_get_full_hero_category_ids' ) ? altijara_get_full_hero_category_ids() : array();
				$counts        = function_exists( 'altijara_get_archive_counts' ) ? altijara_get_archive_counts() : array(
					'sidebar_count' => 3,
					'featured_rows' => 2,
					'news_size'     => 10,
				);
				$archive_cats  = get_categories(
					array(
						'hide_empty' => false,
						'orderby'    => 'name',
						'order'      => 'ASC',
					)
				);
				?>
				<div class="altijara-callout">
					<strong><?php esc_html_e( 'Category archives', 'altijara' ); ?></strong>
					<p><?php esc_html_e( 'All categories use the Posts archive by default. Select only the categories that should use the Magazine issues layout. The CPT archive /issues/ always uses the issues layout.', 'altijara' ); ?></p>
				</div>

				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Magazine issues layout', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Multi-select categories that open the magazine (issues) archive UI.', 'altijara' ); ?></p>
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
									<input type="checkbox" name="altijara_archive_magazine_ids[]" value="<?php echo esc_attr( (string) $tid ); ?>" <?php checked( in_array( $tid, $magazine_ids, true ) ); ?>>
									<span>
										<strong><?php echo esc_html( $cat->name ); ?></strong>
										<code style="display:block;font-size:11px;opacity:.75;"><?php echo esc_html( $cat->slug ); ?></code>
									</span>
								</label>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<input type="hidden" name="altijara_archive_magazine_ids_submitted" value="1">
				</div>

				<div class="altijara-admin-block" style="margin-top:28px;">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Posts archive counts', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Controls the recommended sidebar, featured card rows (4 cards per row), and “أحدث الأخبار” page size before pagination.', 'altijara' ); ?></p>
					<table class="form-table">
						<tr>
							<th><label for="altijara_sidebar_count"><?php esc_html_e( 'Sidebar posts', 'altijara' ); ?></label></th>
							<td>
								<select name="altijara_archive_counts[sidebar_count]" id="altijara_sidebar_count">
									<option value="3" <?php selected( (int) $counts['sidebar_count'], 3 ); ?>>3</option>
									<option value="4" <?php selected( (int) $counts['sidebar_count'], 4 ); ?>>4</option>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="altijara_featured_rows"><?php esc_html_e( 'Featured card rows', 'altijara' ); ?></label></th>
							<td>
								<select name="altijara_archive_counts[featured_rows]" id="altijara_featured_rows">
									<option value="1" <?php selected( (int) $counts['featured_rows'], 1 ); ?>><?php esc_html_e( '1 row (4 cards)', 'altijara' ); ?></option>
									<option value="2" <?php selected( (int) $counts['featured_rows'], 2 ); ?>><?php esc_html_e( '2 rows (8 cards)', 'altijara' ); ?></option>
									<option value="3" <?php selected( (int) $counts['featured_rows'], 3 ); ?>><?php esc_html_e( '3 rows (12 cards)', 'altijara' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="altijara_news_size"><?php esc_html_e( 'Latest news per page', 'altijara' ); ?></label></th>
							<td>
								<select name="altijara_archive_counts[news_size]" id="altijara_news_size">
									<option value="10" <?php selected( (int) $counts['news_size'], 10 ); ?>>10</option>
									<option value="15" <?php selected( (int) $counts['news_size'], 15 ); ?>>15</option>
								</select>
							</td>
						</tr>
					</table>
					<input type="hidden" name="altijara_archive_counts_submitted" value="1">
				</div>

				<div class="altijara-admin-block" style="margin-top:28px;">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Posts hero layout', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Select categories that show a single full-width hero (no recommended side column).', 'altijara' ); ?></p>
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
					<input type="hidden" name="altijara_archive_full_hero_submitted" value="1">
				</div>

				<div class="altijara-admin-block" style="margin-top:28px;">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Issues archive modules', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'JNews-style builder for /issues/ (and magazine category archives). Add lead + carousel bands or other widgets. Reset restores the theme preset.', 'altijara' ); ?></p>
					<label style="display:inline-block;margin:8px 0 14px;">
						<input type="checkbox" name="altijara_issues_layout_reset" value="1">
						<?php esc_html_e( 'Reset issues layout to theme default', 'altijara' ); ?>
					</label>
					<input type="hidden" name="altijara_issues_layout_submitted" value="1">
					<?php
					$issues_pack = function_exists( 'altijara_get_issues_layout' )
						? altijara_get_issues_layout( true )
						: array( 'sections' => array(), 'order' => array() );
					if ( function_exists( 'altijara_render_layout_builder' ) ) {
						altijara_render_layout_builder(
							array(
								'builder_id'   => 'issues_archive',
								'field_prefix' => 'altijara_issues_layout_sections',
								'order_field'  => 'altijara_issues_layout_order',
								'sections'     => $issues_pack['sections'],
								'order'        => $issues_pack['order'],
								'surface'      => 'issues',
								'lock_hero'    => false,
							)
						);
					}
					?>
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

				<?php
				$auth = function_exists( 'altijara_get_auth_settings' ) ? altijara_get_auth_settings() : array();
				$cb   = function_exists( 'altijara_oauth_callback_url' ) ? altijara_oauth_callback_url() : home_url( '/?altijara_oauth_callback=1' );
				?>
				<div class="altijara-admin-block" style="margin-top:32px;">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Social login (Google & Facebook)', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Enable buttons on the login and subscribe pages. Create OAuth apps in Google Cloud Console and Meta for Developers, then paste credentials below.', 'altijara' ); ?></p>
					<p class="description"><strong><?php esc_html_e( 'Authorized redirect URI (use in both consoles):', 'altijara' ); ?></strong><br><code><?php echo esc_html( $cb ); ?></code></p>
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
					</table>
					<?php submit_button( __( 'Save Social Login', 'altijara' ) ); ?>
				</div>

			<?php elseif ( 'article' === $id ) : ?>
				<?php
				$article_cfg = function_exists( 'altijara_sanitize_article_settings' )
					? altijara_sanitize_article_settings( $article )
					: $article;
				$latest_cfg  = is_array( $article_cfg['latest'] ?? null ) ? $article_cfg['latest'] : array( 'count' => 5, 'source' => 'same', 'category_ids' => array() );
				$mr_cfg      = is_array( $article_cfg['most_read'] ?? null ) ? $article_cfg['most_read'] : array( 'count' => 7, 'source' => 'same', 'category_ids' => array() );
				$article_cats = get_categories(
					array(
						'hide_empty' => false,
						'orderby'    => 'name',
						'order'      => 'ASC',
					)
				);
				?>
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Related articles (bottom)', 'altijara' ); ?></th>
						<td>
							<label style="display:block;margin-bottom:10px;">
								<input type="checkbox" name="altijara_article[show_related]" value="1" <?php checked( ! empty( $article_cfg['show_related'] ) ); ?>>
								<?php esc_html_e( 'Show “اقرأ ايضاً”', 'altijara' ); ?>
							</label>
							<label style="display:inline-block;margin-inline-end:16px;">
								<?php esc_html_e( 'Articles before “تحميل المزيد”', 'altijara' ); ?>
								<input type="number" min="1" max="12" name="altijara_article[related_count]" value="<?php echo esc_attr( (string) (int) ( $article_cfg['related_count'] ?? 2 ) ); ?>" style="width:70px;margin-inline-start:6px;">
							</label>
							<label style="display:inline-block;">
								<?php esc_html_e( 'Articles per load more click', 'altijara' ); ?>
								<input type="number" min="1" max="12" name="altijara_article[related_per_load]" value="<?php echo esc_attr( (string) (int) ( $article_cfg['related_per_load'] ?? 2 ) ); ?>" style="width:70px;margin-inline-start:6px;">
							</label>
							<p class="description"><?php esc_html_e( 'First number = how many cards show initially. Second = how many more appear each time the button is clicked.', 'altijara' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Share platforms', 'altijara' ); ?></th>
						<td>
							<p class="description" style="margin-top:0;"><?php esc_html_e( 'Choose which share buttons appear on single articles. Instagram and TikTok copy the link (no web share URL).', 'altijara' ); ?></p>
							<input type="hidden" name="altijara_article[share_platforms_submitted]" value="1">
							<div class="altijara-checkbox-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px 16px;margin-top:8px;">
								<?php
								$share_choices = function_exists( 'altijara_article_share_platform_choices' ) ? altijara_article_share_platform_choices() : array();
								$share_on      = isset( $article_cfg['share_platforms'] ) && is_array( $article_cfg['share_platforms'] )
									? array_map( 'strval', $article_cfg['share_platforms'] )
									: array( 'whatsapp', 'x', 'linkedin', 'facebook', 'telegram', 'copy' );
								foreach ( $share_choices as $pkey => $plabel ) :
									?>
									<label style="display:flex;align-items:center;gap:8px;">
										<input type="checkbox" name="altijara_article[share_platforms][]" value="<?php echo esc_attr( $pkey ); ?>" <?php checked( in_array( $pkey, $share_on, true ) ); ?>>
										<span><?php echo esc_html( $plabel ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>

							<p class="description" style="margin:18px 0 8px;"><strong><?php esc_html_e( 'Custom share buttons', 'altijara' ); ?></strong> — <?php esc_html_e( 'Add any platform. Use {url} and {title} in the share URL template.', 'altijara' ); ?></p>
							<?php
							$share_extra  = isset( $article_cfg['share_extra'] ) && is_array( $article_cfg['share_extra'] ) ? $article_cfg['share_extra'] : array();
							$share_icons  = function_exists( 'altijara_article_share_icon_choices' ) ? altijara_article_share_icon_choices() : array( 'custom' => __( 'Generic / other', 'altijara' ) );
							if ( empty( $share_extra ) ) {
								$share_extra = array(
									array(
										'enabled'      => false,
										'label'        => '',
										'url_template' => '',
										'icon'         => 'custom',
									),
								);
							}
							?>
							<table class="widefat striped altijara-admin-table" id="altijara-share-extra-table">
								<thead>
									<tr>
										<th><?php esc_html_e( 'On', 'altijara' ); ?></th>
										<th><?php esc_html_e( 'Label', 'altijara' ); ?></th>
										<th><?php esc_html_e( 'Share URL template', 'altijara' ); ?></th>
										<th><?php esc_html_e( 'Icon', 'altijara' ); ?></th>
										<th></th>
									</tr>
								</thead>
								<tbody class="altijara-share-extra-rows">
									<?php foreach ( $share_extra as $si => $srow ) : ?>
										<tr class="altijara-share-extra-row">
											<td><input type="checkbox" name="altijara_article[share_extra][<?php echo (int) $si; ?>][enabled]" value="1" <?php checked( ! empty( $srow['enabled'] ) ); ?>></td>
											<td><input type="text" name="altijara_article[share_extra][<?php echo (int) $si; ?>][label]" value="<?php echo esc_attr( $srow['label'] ?? '' ); ?>" class="regular-text" placeholder="e.g. Threads"></td>
											<td><input type="text" name="altijara_article[share_extra][<?php echo (int) $si; ?>][url_template]" value="<?php echo esc_attr( $srow['url_template'] ?? '' ); ?>" class="large-text" placeholder="https://…?url={url}&amp;text={title}"></td>
											<td>
												<select name="altijara_article[share_extra][<?php echo (int) $si; ?>][icon]">
													<?php foreach ( $share_icons as $ik => $il ) : ?>
														<option value="<?php echo esc_attr( $ik ); ?>" <?php selected( $srow['icon'] ?? 'custom', $ik ); ?>><?php echo esc_html( $il ); ?></option>
													<?php endforeach; ?>
												</select>
											</td>
											<td><button type="button" class="button-link-delete altijara-share-extra-remove"><?php esc_html_e( 'Remove', 'altijara' ); ?></button></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
							<p style="margin-top:8px;">
								<button type="button" class="button altijara-share-extra-add"><?php esc_html_e( 'Add share platform', 'altijara' ); ?></button>
							</p>
							<template id="altijara-share-extra-tpl">
								<tr class="altijara-share-extra-row">
									<td><input type="checkbox" name="altijara_article[share_extra][__i__][enabled]" value="1" checked></td>
									<td><input type="text" name="altijara_article[share_extra][__i__][label]" value="" class="regular-text" placeholder="e.g. Threads"></td>
									<td><input type="text" name="altijara_article[share_extra][__i__][url_template]" value="" class="large-text" placeholder="https://…?url={url}&amp;text={title}"></td>
									<td>
										<select name="altijara_article[share_extra][__i__][icon]">
											<?php foreach ( $share_icons as $ik => $il ) : ?>
												<option value="<?php echo esc_attr( $ik ); ?>" <?php selected( $ik, 'custom' ); ?>><?php echo esc_html( $il ); ?></option>
											<?php endforeach; ?>
										</select>
									</td>
									<td><button type="button" class="button-link-delete altijara-share-extra-remove"><?php esc_html_e( 'Remove', 'altijara' ); ?></button></td>
								</tr>
							</template>
						</td>
					</tr>
				</table>

				<div class="altijara-admin-block" style="margin-top:24px;">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Sidebar — أحدث الأخبار', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Latest posts in the article sidebar. Use the article’s own category, or pick specific categories.', 'altijara' ); ?></p>
					<table class="form-table">
						<tr>
							<th><label for="altijara_latest_count"><?php esc_html_e( 'Number of posts', 'altijara' ); ?></label></th>
							<td>
								<input type="number" id="altijara_latest_count" min="1" max="12" name="altijara_article[latest][count]" value="<?php echo esc_attr( (string) (int) ( $latest_cfg['count'] ?? 5 ) ); ?>" style="width:70px">
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Category source', 'altijara' ); ?></th>
							<td>
								<label style="display:block;margin-bottom:6px;">
									<input type="radio" name="altijara_article[latest][source]" value="same" class="altijara-sidebar-source" data-target="altijara-latest-cats" <?php checked( ( $latest_cfg['source'] ?? 'same' ), 'same' ); ?>>
									<?php esc_html_e( 'Same category as the article', 'altijara' ); ?>
								</label>
								<label style="display:block;margin-bottom:10px;">
									<input type="radio" name="altijara_article[latest][source]" value="selected" class="altijara-sidebar-source" data-target="altijara-latest-cats" <?php checked( ( $latest_cfg['source'] ?? '' ), 'selected' ); ?>>
									<?php esc_html_e( 'Selected categories', 'altijara' ); ?>
								</label>
								<div id="altijara-latest-cats" class="altijara-checkbox-grid" style="display:<?php echo ( 'selected' === ( $latest_cfg['source'] ?? '' ) ) ? 'grid' : 'none'; ?>;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px 16px;">
									<?php
									$latest_ids = isset( $latest_cfg['category_ids'] ) && is_array( $latest_cfg['category_ids'] ) ? array_map( 'absint', $latest_cfg['category_ids'] ) : array();
									if ( ! empty( $article_cats ) && ! is_wp_error( $article_cats ) ) :
										foreach ( $article_cats as $cat ) :
											if ( ! $cat instanceof WP_Term ) {
												continue;
											}
											?>
											<label style="display:flex;align-items:flex-start;gap:8px;">
												<input type="checkbox" name="altijara_article[latest][category_ids][]" value="<?php echo esc_attr( (string) (int) $cat->term_id ); ?>" <?php checked( in_array( (int) $cat->term_id, $latest_ids, true ) ); ?>>
												<span><strong><?php echo esc_html( $cat->name ); ?></strong></span>
											</label>
											<?php
										endforeach;
									endif;
									?>
								</div>
							</td>
						</tr>
					</table>
				</div>

				<div class="altijara-admin-block" style="margin-top:28px;">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Sidebar — الأكثر قراءة', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Most-read list in the article sidebar. Same category as the article, or pick specific categories.', 'altijara' ); ?></p>
					<table class="form-table">
						<tr>
							<th><label for="altijara_mr_count"><?php esc_html_e( 'Number of posts', 'altijara' ); ?></label></th>
							<td>
								<input type="number" id="altijara_mr_count" min="1" max="15" name="altijara_article[most_read][count]" value="<?php echo esc_attr( (string) (int) ( $mr_cfg['count'] ?? 7 ) ); ?>" style="width:70px">
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Category source', 'altijara' ); ?></th>
							<td>
								<label style="display:block;margin-bottom:6px;">
									<input type="radio" name="altijara_article[most_read][source]" value="same" class="altijara-sidebar-source" data-target="altijara-mr-cats" <?php checked( ( $mr_cfg['source'] ?? 'same' ), 'same' ); ?>>
									<?php esc_html_e( 'Same category as the article', 'altijara' ); ?>
								</label>
								<label style="display:block;margin-bottom:10px;">
									<input type="radio" name="altijara_article[most_read][source]" value="selected" class="altijara-sidebar-source" data-target="altijara-mr-cats" <?php checked( ( $mr_cfg['source'] ?? '' ), 'selected' ); ?>>
									<?php esc_html_e( 'Selected categories', 'altijara' ); ?>
								</label>
								<div id="altijara-mr-cats" class="altijara-checkbox-grid" style="display:<?php echo ( 'selected' === ( $mr_cfg['source'] ?? '' ) ) ? 'grid' : 'none'; ?>;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px 16px;">
									<?php
									$mr_ids = isset( $mr_cfg['category_ids'] ) && is_array( $mr_cfg['category_ids'] ) ? array_map( 'absint', $mr_cfg['category_ids'] ) : array();
									if ( ! empty( $article_cats ) && ! is_wp_error( $article_cats ) ) :
										foreach ( $article_cats as $cat ) :
											if ( ! $cat instanceof WP_Term ) {
												continue;
											}
											?>
											<label style="display:flex;align-items:flex-start;gap:8px;">
												<input type="checkbox" name="altijara_article[most_read][category_ids][]" value="<?php echo esc_attr( (string) (int) $cat->term_id ); ?>" <?php checked( in_array( (int) $cat->term_id, $mr_ids, true ) ); ?>>
												<span><strong><?php echo esc_html( $cat->name ); ?></strong></span>
											</label>
											<?php
										endforeach;
									endif;
									?>
								</div>
							</td>
						</tr>
					</table>
				</div>
				<script>
				(function(){
					document.querySelectorAll('.altijara-sidebar-source').forEach(function(radio){
						radio.addEventListener('change', function(){
							var id = radio.getAttribute('data-target');
							var box = id ? document.getElementById(id) : null;
							if (!box) return;
							var selected = document.querySelector('input[name="'+radio.name+'"][value="selected"]');
							box.style.display = (selected && selected.checked) ? 'grid' : 'none';
						});
					});
				})();
				</script>
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
						<p><?php esc_html_e( 'Ensures utility pages (slug-2 on conflict), seeds page meta, and force-rebuilds primary, top bar, and footer menus.', 'altijara' ); ?></p>
						<button type="submit" name="altijara_rerun_setup" class="button button-secondary" value="1" onclick="return confirm('Re-run theme setup? Menus will be rebuilt from theme blueprints.');">
							<?php esc_html_e( 'Run theme setup now', 'altijara' ); ?>
						</button>
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
			<?php endif; ?>
	<?php
}
