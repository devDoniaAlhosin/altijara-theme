<?php
/**
 * Settings panel: article.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render article settings panel.
 *
 * @param array<string, mixed> $ctx Panel context.
 */
function altijara_render_settings_panel_article( array $ctx ) {
	$opts    = $ctx['opts'];
	$defs    = $ctx['defs'];
	$header  = $ctx['header'];
	$footer  = $ctx['footer'];
	$article = $ctx['article'];
	$social  = $ctx['social'];
	$map     = $ctx['map'];

				$article_cfg = function_exists( 'altijara_sanitize_article_settings' )
					? altijara_sanitize_article_settings( $article )
					: $article;
				?>
				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Article page', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Controls that apply to every single article. Configure اقرأ ايضاً in Article modules below.', 'altijara' ); ?></p>
					<div class="altijara-field-group">
						<div class="altijara-field-group__body">
							<div class="altijara-field">
								<input type="hidden" name="altijara_article[show_save]" value="0">
								<label>
									<input type="checkbox" name="altijara_article[show_save]" value="1" <?php checked( ! isset( $article_cfg['show_save'] ) || ! empty( $article_cfg['show_save'] ) ); ?>>
									<?php esc_html_e( 'Show the save button', 'altijara' ); ?>
								</label>
								<p class="description" style="margin:4px 0 0;"><?php esc_html_e( 'Bookmark button on single articles and on category, tag, and search lists. The saved-articles page always keeps it so a reader can remove an item.', 'altijara' ); ?></p>
							</div>
							<div class="altijara-field" style="margin-top:10px;">
								<input type="hidden" name="altijara_article[show_author]" value="0">
								<label>
									<input type="checkbox" name="altijara_article[show_author]" value="1" <?php checked( ! isset( $article_cfg['show_author'] ) || ! empty( $article_cfg['show_author'] ) ); ?>>
									<?php esc_html_e( 'Show the author box', 'altijara' ); ?>
								</label>
								<p class="description" style="margin:4px 0 0;"><?php esc_html_e( 'Avatar, name, verified analyst badge, and date on single articles. The name links to the author’s full article archive.', 'altijara' ); ?></p>
							</div>
						</div>
					</div>
				</div>

				<?php
				$article_body = function_exists( 'altijara_get_article_body_layout' )
					? altijara_get_article_body_layout()
					: array(
						'visible'  => true,
						'sections' => array(),
						'order'    => array(),
					);
				?>
				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Article modules', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Blocks under the article (including اقرأ ايضاً). Open each widget to set its title, source, count, and load-more options. Add more from the list, then drag to reorder.', 'altijara' ); ?></p>
					<input type="hidden" name="altijara_article_body_layout_visible" value="0">
					<label style="display:block;margin:8px 16px 14px;">
						<input type="checkbox" name="altijara_article_body_layout_visible" value="1" <?php checked( ! isset( $article_body['visible'] ) || ! empty( $article_body['visible'] ) ); ?>>
						<?php esc_html_e( 'Show the modules under the article', 'altijara' ); ?>
					</label>
					<input type="hidden" name="altijara_article_body_layout_submitted" value="1">
					<?php
					if ( function_exists( 'altijara_render_layout_builder' ) ) {
						altijara_render_layout_builder(
							array(
								'builder_id'   => 'article_body',
								'field_prefix' => 'altijara_article_body_sections',
								'order_field'  => 'altijara_article_body_order',
								'sections'     => isset( $article_body['sections'] ) && is_array( $article_body['sections'] ) ? $article_body['sections'] : array(),
								'order'        => isset( $article_body['order'] ) && is_array( $article_body['order'] ) ? $article_body['order'] : array(),
								'surface'      => 'article_body',
								'lock_hero'    => false,
								'zone_title'   => __( 'Article modules', 'altijara' ),
								'zone_desc'    => __( 'Add, reorder, or remove widgets. Open one to set what it displays.', 'altijara' ),
							)
						);
					}
					?>
				</div>

				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Share platforms', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Buttons on single articles. Instagram and TikTok open the device share sheet. Custom buttons use the URL template below.', 'altijara' ); ?></p>
					<input type="hidden" name="altijara_article[share_platforms_submitted]" value="1">
					<div class="altijara-checkbox-grid">
						<?php
						$share_choices = function_exists( 'altijara_article_share_platform_choices' ) ? altijara_article_share_platform_choices() : array();
						$share_on      = isset( $article_cfg['share_platforms'] ) && is_array( $article_cfg['share_platforms'] )
							? array_map( 'strval', $article_cfg['share_platforms'] )
							: array( 'whatsapp', 'x', 'linkedin', 'facebook', 'telegram', 'copy' );
						foreach ( $share_choices as $pkey => $plabel ) :
							?>
							<label>
								<input type="checkbox" name="altijara_article[share_platforms][]" value="<?php echo esc_attr( $pkey ); ?>" <?php checked( in_array( $pkey, $share_on, true ) ); ?>>
								<span><?php echo esc_html( $plabel ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>

					<div class="altijara-field-group" style="margin:16px;">
						<div class="altijara-field-group__head">
							<h3 class="altijara-field-group__title"><?php esc_html_e( 'Custom share buttons', 'altijara' ); ?></h3>
							<p class="altijara-field-group__hint"><?php esc_html_e( 'Use {url} and {title} in the share URL template.', 'altijara' ); ?></p>
						</div>
						<div class="altijara-field-group__body">
							<?php
							$share_extra = isset( $article_cfg['share_extra'] ) && is_array( $article_cfg['share_extra'] ) ? $article_cfg['share_extra'] : array();
							$share_icons = function_exists( 'altijara_article_share_icon_choices' ) ? altijara_article_share_icon_choices() : array( 'custom' => __( 'Generic / other', 'altijara' ) );
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
						</div>
					</div>
				</div>

				<?php
				$article_side = function_exists( 'altijara_get_article_sidebar_layout' )
					? altijara_get_article_sidebar_layout()
					: array(
						'visible'  => true,
						'sections' => array(),
						'order'    => array(),
					);
				?>
				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Article sidebar', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Column beside the article. Open each widget to set its options. Ads are configured on the Advertisement widget.', 'altijara' ); ?></p>
					<div class="altijara-field" style="margin:8px 16px 14px;">
						<label class="altijara-field__label" for="altijara-article-sidebar-empty"><?php esc_html_e( 'Empty list message', 'altijara' ); ?></label>
						<input type="text" class="regular-text" id="altijara-article-sidebar-empty" name="altijara_article[sidebar_empty_message]" value="<?php echo esc_attr( (string) ( $article_cfg['sidebar_empty_message'] ?? '' ) ); ?>" placeholder="<?php echo esc_attr__( 'لا توجد مقالات في تصنيف هذا المقال حالياً', 'altijara' ); ?>">
						<p class="description" style="margin:4px 0 0;"><?php esc_html_e( 'Shown under أحدث الأخبار / الأكثر قراءة when the article’s category has no other posts to list.', 'altijara' ); ?></p>
					</div>
					<input type="hidden" name="altijara_article_sidebar_layout_visible" value="0">
					<label style="display:block;margin:8px 16px 14px;">
						<input type="checkbox" name="altijara_article_sidebar_layout_visible" value="1" <?php checked( ! isset( $article_side['visible'] ) || ! empty( $article_side['visible'] ) ); ?>>
						<?php esc_html_e( 'Show the article sidebar', 'altijara' ); ?>
					</label>
					<input type="hidden" name="altijara_article_sidebar_layout_submitted" value="1">
					<?php
					if ( function_exists( 'altijara_render_layout_builder' ) ) {
						altijara_render_layout_builder(
							array(
								'builder_id'   => 'article_sidebar',
								'field_prefix' => 'altijara_article_sidebar_sections',
								'order_field'  => 'altijara_article_sidebar_order',
								'sections'     => isset( $article_side['sections'] ) && is_array( $article_side['sections'] ) ? $article_side['sections'] : array(),
								'order'        => isset( $article_side['order'] ) && is_array( $article_side['order'] ) ? $article_side['order'] : array(),
								'surface'      => 'article_sidebar',
								'lock_hero'    => false,
								'zone_title'   => __( 'Sidebar widgets', 'altijara' ),
								'zone_desc'    => __( 'Add, reorder, or remove widgets. Drag the handle to change the order.', 'altijara' ),
							)
						);
					}
					?>
				</div>
				<div class="altijara-panel-actions">
					<?php submit_button( __( 'Save Article', 'altijara' ), 'primary', 'submit', false ); ?>
				</div>


<?php
}
