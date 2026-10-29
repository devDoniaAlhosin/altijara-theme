<?php
/**
 * Settings panel: design.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render design settings panel.
 *
 * @param array<string, mixed> $ctx Panel context.
 */
function altijara_render_settings_panel_design( array $ctx ) {
	$opts    = $ctx['opts'];
	$defs    = $ctx['defs'];
	$header  = $ctx['header'];
	$footer  = $ctx['footer'];
	$article = $ctx['article'];
	$social  = $ctx['social'];
	$map     = $ctx['map'];

				if ( ! function_exists( 'altijara_default_design' ) || ! function_exists( 'altijara_font_main_presets' ) ) {
					echo '<div class="notice notice-error"><p>' . esc_html__( 'Design module missing. Re-upload the full theme folder.', 'altijara' ) . '</p></div>';
				} else {
				$design = is_array( $opts['design'] ?? null ) ? array_merge( altijara_default_design(), $opts['design'] ) : altijara_default_design();
				$main_presets  = altijara_font_main_presets();
				$serif_presets = altijara_font_serif_presets();
				?>
				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Fonts', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Self-hosted defaults: IBM Plex Sans Arabic + Playfair Display. Upload .woff2 / .woff / .ttf / .otf files below, then choose them as body or display fonts.', 'altijara' ); ?></p>
					<table class="form-table">
						<tr>
							<th><label for="font_main"><?php esc_html_e( 'Body font', 'altijara' ); ?></label></th>
							<td>
								<select name="altijara_design[font_main]" id="font_main" class="altijara-font-role-select" data-font-role="main">
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
								<p class="description"><?php esc_html_e( 'Used only when “Custom (CSS)…” is selected.', 'altijara' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="font_serif"><?php esc_html_e( 'Display / title font', 'altijara' ); ?></label></th>
							<td>
								<select name="altijara_design[font_serif]" id="font_serif" class="altijara-font-role-select" data-font-role="serif">
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

					<?php
					$custom_fonts = isset( $design['custom_fonts'] ) && is_array( $design['custom_fonts'] ) ? $design['custom_fonts'] : array();
					if ( empty( $custom_fonts ) ) {
						$custom_fonts = array(
							array(
								'family' => '',
								'weight' => '400',
								'style'  => 'normal',
								'url'    => '',
							),
						);
					}
					$weight_opts = array( '100', '200', '300', '400', '500', '600', '700', '800', '900' );
					?>
					<div class="altijara-field-group" style="margin:16px;">
						<div class="altijara-field-group__head">
							<h3 class="altijara-field-group__title"><?php esc_html_e( 'Upload custom fonts', 'altijara' ); ?></h3>
							<p class="altijara-field-group__hint"><?php esc_html_e( 'Add one row per file (same family name for multiple weights). After saving, pick the family in Body or Display above.', 'altijara' ); ?></p>
						</div>
						<div class="altijara-field-group__body">
							<table class="widefat striped altijara-admin-table" id="altijara-custom-fonts-table">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Family name', 'altijara' ); ?></th>
										<th><?php esc_html_e( 'Weight', 'altijara' ); ?></th>
										<th><?php esc_html_e( 'Style', 'altijara' ); ?></th>
										<th><?php esc_html_e( 'Font file', 'altijara' ); ?></th>
										<th></th>
									</tr>
								</thead>
								<tbody class="altijara-custom-fonts-rows">
									<?php foreach ( $custom_fonts as $fi => $frow ) : ?>
										<?php
										$row_slug = ! empty( $frow['slug'] )
											? sanitize_key( (string) $frow['slug'] )
											: ( function_exists( 'altijara_custom_font_slug' ) ? altijara_custom_font_slug( (string) ( $frow['family'] ?? '' ) ) : '' );
										?>
										<tr class="altijara-custom-font-row">
											<td>
												<input type="hidden" class="altijara-custom-font-slug" name="altijara_design[custom_fonts][<?php echo (int) $fi; ?>][slug]" value="<?php echo esc_attr( $row_slug ); ?>">
												<input type="text" class="regular-text altijara-custom-font-family" name="altijara_design[custom_fonts][<?php echo (int) $fi; ?>][family]" value="<?php echo esc_attr( $frow['family'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'e.g. Cairo', 'altijara' ); ?>">
											</td>
											<td>
												<select name="altijara_design[custom_fonts][<?php echo (int) $fi; ?>][weight]">
													<?php foreach ( $weight_opts as $w ) : ?>
														<option value="<?php echo esc_attr( $w ); ?>" <?php selected( (string) ( $frow['weight'] ?? '400' ), $w ); ?>><?php echo esc_html( $w ); ?></option>
													<?php endforeach; ?>
												</select>
											</td>
											<td>
												<select name="altijara_design[custom_fonts][<?php echo (int) $fi; ?>][style]">
													<option value="normal" <?php selected( ( $frow['style'] ?? 'normal' ), 'normal' ); ?>><?php esc_html_e( 'Normal', 'altijara' ); ?></option>
													<option value="italic" <?php selected( ( $frow['style'] ?? '' ), 'italic' ); ?>><?php esc_html_e( 'Italic', 'altijara' ); ?></option>
												</select>
											</td>
											<td>
												<div class="altijara-font-file-field">
													<input type="url" class="regular-text altijara-font-file-url" name="altijara_design[custom_fonts][<?php echo (int) $fi; ?>][url]" value="<?php echo esc_attr( $frow['url'] ?? '' ); ?>" placeholder="https://…/font.woff2">
													<button type="button" class="button altijara-font-upload"><?php esc_html_e( 'Upload / library', 'altijara' ); ?></button>
												</div>
											</td>
											<td><button type="button" class="button-link-delete altijara-custom-font-remove"><?php esc_html_e( 'Remove', 'altijara' ); ?></button></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
							<p style="margin:10px 0 0;">
								<button type="button" class="button altijara-custom-font-add"><?php esc_html_e( 'Add font file', 'altijara' ); ?></button>
							</p>
							<template id="altijara-custom-font-tpl">
								<tr class="altijara-custom-font-row">
									<td>
										<input type="hidden" class="altijara-custom-font-slug" name="altijara_design[custom_fonts][__i__][slug]" value="">
										<input type="text" class="regular-text altijara-custom-font-family" name="altijara_design[custom_fonts][__i__][family]" value="" placeholder="<?php esc_attr_e( 'e.g. Cairo', 'altijara' ); ?>">
									</td>
									<td>
										<select name="altijara_design[custom_fonts][__i__][weight]">
											<?php foreach ( $weight_opts as $w ) : ?>
												<option value="<?php echo esc_attr( $w ); ?>" <?php selected( $w, '400' ); ?>><?php echo esc_html( $w ); ?></option>
											<?php endforeach; ?>
										</select>
									</td>
									<td>
										<select name="altijara_design[custom_fonts][__i__][style]">
											<option value="normal" selected><?php esc_html_e( 'Normal', 'altijara' ); ?></option>
											<option value="italic"><?php esc_html_e( 'Italic', 'altijara' ); ?></option>
										</select>
									</td>
									<td>
										<div class="altijara-font-file-field">
											<input type="url" class="regular-text altijara-font-file-url" name="altijara_design[custom_fonts][__i__][url]" value="" placeholder="https://…/font.woff2">
											<button type="button" class="button altijara-font-upload"><?php esc_html_e( 'Upload / library', 'altijara' ); ?></button>
										</div>
									</td>
									<td><button type="button" class="button-link-delete altijara-custom-font-remove"><?php esc_html_e( 'Remove', 'altijara' ); ?></button></td>
								</tr>
							</template>
						</div>
					</div>
				</div>

				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Color palette', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Use the swatch or picker on each card to set a hex value.', 'altijara' ); ?></p>
					<div class="altijara-color-grid">
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
							altijara_admin_color_card(
								'altijara_design[' . $ckey . ']',
								$design[ $ckey ] ?? '',
								$meta[0],
								$meta[1],
								'design-' . $ckey
							);
						endforeach;
						?>
					</div>
				</div>
				<div class="altijara-panel-actions">
					<?php submit_button( __( 'Save Design', 'altijara' ), 'primary', 'submit', false ); ?>
					<button type="submit" name="altijara_reset_group" value="design" class="button" onclick="return confirm('Reset fonts and colors to defaults?');"><?php esc_html_e( 'Reset to defaults', 'altijara' ); ?></button>
				</div>
				<?php } ?>

<?php
}
