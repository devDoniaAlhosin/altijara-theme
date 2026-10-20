<?php
/**
 * Homepage sections — admin section card
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render one section card in admin (widget-specific fields).
 *
 * @param string               $id      Section ID.
 * @param array<string, mixed> $section Config.
 * @param array<string, mixed> $args    Extra: is_hero, sortable.
 */
function altijara_render_section_admin_card( $id, $section, $args = array() ) {
	if ( ! is_array( $section ) ) {
		$section = array();
	}
	$defaults = altijara_default_sections();
	$section  = array_merge( isset( $defaults[ $id ] ) ? $defaults[ $id ] : array(
		'zone'         => 'body',
		'label'        => $id,
		'visible'      => false,
		'category'     => '',
		'tag'          => 0,
		'manual_posts' => array(),
		'issue_ids'    => array(),
		'count'        => 4,
		'offset'       => 0,
		'orderby'      => 'date',
		'order'        => 'DESC',
		'variant'      => 'default',
		'source'       => 'posts',
		'view_period'  => 'all',
		'widget'       => 'article_grid',
		'layout'       => 'news_grid',
		'pair_with'    => '',
	), $section );

	$is_hero = ! empty( $args['is_hero'] ) || 'hero' === ( $section['zone'] ?? '' ) || in_array( $id, altijara_hero_section_ids(), true );
	$widget  = ! empty( $section['widget'] ) ? $section['widget'] : altijara_layout_to_widget( $section['layout'] ?? '' );
	$wdef    = function_exists( 'altijara_get_widget_definition' ) ? altijara_get_widget_definition( $widget ) : null;
	$choices = altijara_get_category_choices();
	$tags    = altijara_get_tag_choices();
	$manual  = ! empty( $section['manual_posts'] ) ? implode( ',', array_map( 'absint', (array) $section['manual_posts'] ) ) : '';
	$issues  = ! empty( $section['issue_ids'] ) ? implode( ',', array_map( 'absint', (array) $section['issue_ids'] ) ) : '';
	$field   = 'altijara_sections[' . esc_attr( $id ) . ']';
	$titles  = altijara_get_post_titles_map( $section['manual_posts'] ?? array() );
	$issue_t = altijara_get_post_titles_map( $section['issue_ids'] ?? array() );
	$label   = altijara_strip_hero_label_prefix( isset( $section['label'] ) ? (string) $section['label'] : (string) $id );
	$family  = ( 'article_grid' === $widget ) ? 'card' : 'block';
	?>
	<details class="altijara-section-card" data-section-id="<?php echo esc_attr( $id ); ?>" data-widget="<?php echo esc_attr( $widget ); ?>" data-zone="<?php echo esc_attr( $is_hero ? 'hero' : 'body' ); ?>" <?php echo ! empty( $section['visible'] ) ? 'open' : ''; ?>>
		<summary>
			<?php if ( ! $is_hero ) : ?>
				<span class="altijara-section-drag dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'altijara' ); ?>" aria-hidden="true"></span>
			<?php endif; ?>
			<strong class="altijara-section-summary-label"><?php echo esc_html( $label ); ?></strong>
			<span class="altijara-widget-badge altijara-widget-badge--<?php echo esc_attr( $family ); ?>"><?php echo esc_html( ( is_array( $wdef ) && ! empty( $wdef['label'] ) ) ? $wdef['label'] : $widget ); ?></span>
			<span class="altijara-section-status"><?php echo ! empty( $section['visible'] ) ? esc_html__( 'ظاهر', 'altijara' ) : esc_html__( 'مخفي', 'altijara' ); ?></span>
			<?php if ( ! $is_hero ) : ?>
				<button type="button" class="button-link-delete altijara-section-delete" title="<?php esc_attr_e( 'Remove section', 'altijara' ); ?>">&times;</button>
			<?php endif; ?>
		</summary>
		<div class="altijara-section-body">
			<input type="hidden" name="<?php echo esc_attr( $field ); ?>[widget]" value="<?php echo esc_attr( $widget ); ?>">
			<input type="hidden" name="<?php echo esc_attr( $field ); ?>[zone]" value="<?php echo esc_attr( $is_hero ? 'hero' : 'body' ); ?>">
			<input type="hidden" name="<?php echo esc_attr( $field ); ?>[pair_with]" class="altijara-section-pair" value="<?php echo esc_attr( $section['pair_with'] ?? '' ); ?>">
			<?php if ( ! $is_hero ) : ?>
				<input type="hidden" class="altijara-body-order-item" name="altijara_sections_order[]" value="<?php echo esc_attr( $id ); ?>">
			<?php endif; ?>

			<p>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $field ); ?>[visible]" value="1" <?php checked( ! empty( $section['visible'] ) ); ?>>
					<?php esc_html_e( 'إظهار القسم', 'altijara' ); ?>
				</label>
			</p>

			<?php if ( altijara_widget_supports( $widget, 'label' ) ) : ?>
				<p>
					<label><?php esc_html_e( 'العنوان (عربي)', 'altijara' ); ?></label><br>
					<input type="text" class="regular-text" name="<?php echo esc_attr( $field ); ?>[label]" value="<?php echo esc_attr( $label ); ?>">
				</p>
			<?php endif; ?>

			<?php if ( altijara_widget_supports( $widget, 'category' ) ) : ?>
				<?php
				$cat_val = (string) ( $section['category'] ?? '' );
				if ( $cat_val && ! is_numeric( $cat_val ) && function_exists( 'altijara_resolve_category_id' ) ) {
					$resolved = altijara_resolve_category_id( $cat_val );
					if ( $resolved ) {
						$cat_val = (string) $resolved;
					}
				}
				?>
				<p>
					<label><?php esc_html_e( 'Category', 'altijara' ); ?></label><br>
					<select name="<?php echo esc_attr( $field ); ?>[category]" class="regular-text">
						<?php foreach ( $choices as $val => $lab ) : ?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $cat_val, (string) $val ); ?>>
								<?php echo esc_html( $lab ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<span class="description"><?php esc_html_e( 'Live categories from this site.', 'altijara' ); ?></span>
				</p>
			<?php endif; ?>

			<?php if ( altijara_widget_supports( $widget, 'tag' ) ) : ?>
				<p>
					<label><?php esc_html_e( 'وسم', 'altijara' ); ?></label><br>
					<select name="<?php echo esc_attr( $field ); ?>[tag]" class="regular-text">
						<?php foreach ( $tags as $val => $lab ) : ?>
							<option value="<?php echo esc_attr( (string) $val ); ?>" <?php selected( (int) $section['tag'], (int) $val ); ?>>
								<?php echo esc_html( $lab ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>
			<?php endif; ?>

			<?php if ( altijara_widget_supports( $widget, 'source' ) ) : ?>
				<p>
					<label><?php esc_html_e( 'مصدر الشرائح', 'altijara' ); ?></label><br>
					<select name="<?php echo esc_attr( $field ); ?>[source]">
						<option value="issues" <?php selected( $section['source'] ?? '', 'issues' ); ?>><?php esc_html_e( 'أعداد المجلة (issue)', 'altijara' ); ?></option>
						<option value="posts" <?php selected( $section['source'] ?? '', 'posts' ); ?>><?php esc_html_e( 'مقالات', 'altijara' ); ?></option>
					</select>
				</p>
			<?php endif; ?>

			<?php if ( altijara_widget_supports( $widget, 'issue_ids' ) ) : ?>
				<div class="altijara-post-picker" data-post-type="issue" data-field="<?php echo esc_attr( $field ); ?>[issue_ids]">
					<label><?php esc_html_e( 'أعداد محددة (بحث)', 'altijara' ); ?></label>
					<input type="hidden" class="altijara-picker-ids" name="<?php echo esc_attr( $field ); ?>[issue_ids]" value="<?php echo esc_attr( $issues ); ?>">
					<input type="search" class="altijara-picker-search regular-text" placeholder="<?php esc_attr_e( 'Search for an issue…', 'altijara' ); ?>" autocomplete="off">
					<ul class="altijara-picker-results" hidden></ul>
					<ul class="altijara-picker-selected">
						<?php foreach ( $issue_t as $pid => $title ) : ?>
							<li data-id="<?php echo esc_attr( (string) $pid ); ?>">
								<span><?php echo esc_html( $title ); ?></span>
								<button type="button" class="button-link-delete altijara-picker-remove">&times;</button>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( altijara_widget_supports( $widget, 'manual_posts' ) ) : ?>
				<div class="altijara-post-picker" data-post-type="<?php echo 'magazine_slider' === $widget ? 'any' : 'post'; ?>" data-field="<?php echo esc_attr( $field ); ?>[manual_posts]">
					<label><?php esc_html_e( 'مقالات يدوية (بحث AJAX)', 'altijara' ); ?></label>
					<input type="hidden" class="altijara-picker-ids" name="<?php echo esc_attr( $field ); ?>[manual_posts]" value="<?php echo esc_attr( $manual ); ?>">
					<input type="search" class="altijara-picker-search regular-text" placeholder="<?php esc_attr_e( 'ابحث عن مقال بالعنوان…', 'altijara' ); ?>" autocomplete="off">
					<ul class="altijara-picker-results" hidden></ul>
					<ul class="altijara-picker-selected">
						<?php foreach ( $titles as $pid => $title ) : ?>
							<li data-id="<?php echo esc_attr( (string) $pid ); ?>">
								<span><?php echo esc_html( $title ); ?></span>
								<button type="button" class="button-link-delete altijara-picker-remove">&times;</button>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<p style="display:flex;gap:16px;flex-wrap:wrap;">
				<?php if ( altijara_widget_supports( $widget, 'count' ) ) : ?>
					<label><?php esc_html_e( 'العدد', 'altijara' ); ?>
						<input type="number" min="1" max="20" name="<?php echo esc_attr( $field ); ?>[count]" value="<?php echo esc_attr( (string) (int) $section['count'] ); ?>" style="width:70px">
					</label>
				<?php endif; ?>
				<?php if ( altijara_widget_supports( $widget, 'offset' ) ) : ?>
					<label><?php esc_html_e( 'الإزاحة', 'altijara' ); ?>
						<input type="number" min="0" max="100" name="<?php echo esc_attr( $field ); ?>[offset]" value="<?php echo esc_attr( (string) (int) $section['offset'] ); ?>" style="width:70px">
					</label>
				<?php endif; ?>
				<?php if ( altijara_widget_supports( $widget, 'orderby' ) ) : ?>
					<label><?php esc_html_e( 'الترتيب حسب', 'altijara' ); ?>
						<select name="<?php echo esc_attr( $field ); ?>[orderby]">
							<?php
							$order_opts = array(
								'date'          => 'التاريخ',
								'modified'      => 'التعديل',
								'title'         => 'العنوان',
								'rand'          => 'عشوائي',
								'comment_count' => 'التعليقات',
							);
							foreach ( $order_opts as $k => $lab ) :
								?>
								<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $section['orderby'], $k ); ?>><?php echo esc_html( $lab ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label><?php esc_html_e( 'الاتجاه', 'altijara' ); ?>
						<select name="<?php echo esc_attr( $field ); ?>[order]">
							<option value="DESC" <?php selected( $section['order'], 'DESC' ); ?>>DESC</option>
							<option value="ASC" <?php selected( $section['order'], 'ASC' ); ?>>ASC</option>
						</select>
					</label>
				<?php endif; ?>
			</p>

			<?php if ( altijara_widget_supports( $widget, 'view_period' ) ) : ?>
				<p>
					<label><?php esc_html_e( 'فترة الأكثر قراءة', 'altijara' ); ?></label><br>
					<select name="<?php echo esc_attr( $field ); ?>[view_period]">
						<?php
						foreach ( array(
							'day'   => 'آخر 24 ساعة',
							'week'  => 'آخر أسبوع',
							'month' => 'آخر 30 يوم',
							'year'  => 'آخر سنة',
							'all'   => 'كل الأوقات',
						) as $k => $lab ) :
							?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $section['view_period'] ?? 'all', $k ); ?>><?php echo esc_html( $lab ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
			<?php endif; ?>

			<?php if ( altijara_widget_supports( $widget, 'variant' ) && $wdef && ! empty( $wdef['variants'] ) ) : ?>
				<p>
					<label><?php esc_html_e( 'نمط التخطيط', 'altijara' ); ?></label><br>
					<select name="<?php echo esc_attr( $field ); ?>[variant]">
						<?php foreach ( $wdef['variants'] as $vk => $vdef ) : ?>
							<option value="<?php echo esc_attr( $vk ); ?>" <?php selected( $section['variant'] ?? 'default', $vk ); ?>>
								<?php echo esc_html( $vdef['label'] ?? $vk ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>
			<?php endif; ?>

			<?php if ( altijara_widget_supports( $widget, 'cache' ) ) : ?>
				<p class="description"><?php esc_html_e( 'Saving clears section cache. Use Purge below if needed.', 'altijara' ); ?></p>
			<?php endif; ?>
		</div>
	</details>
	<?php
}
