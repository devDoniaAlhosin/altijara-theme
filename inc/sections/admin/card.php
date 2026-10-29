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
 * Include-subcategories checkbox for category-scoped modules.
 *
 * @param string               $field   Input name prefix (e.g. altijara_sections[id]).
 * @param array<string, mixed> $section Section config.
 * @param string               $widget  Widget type.
 * @param bool                 $panel   Wrap as posts source panel when true.
 */
function altijara_admin_render_include_children_field( $field, $section, $widget = '', $panel = false ) {
	$checked = function_exists( 'altijara_section_include_children' )
		? altijara_section_include_children( is_array( $section ) ? $section : array() )
		: true;
	$class   = 'altijara-field';
	$attrs   = '';
	if ( $panel ) {
		$class .= ' altijara-source-panel';
		$attrs  = ' data-source-panel="posts"';
		$source = is_array( $section ) ? (string) ( $section['source'] ?? 'posts' ) : 'posts';
		if ( $widget && function_exists( 'altijara_widget_supports' ) && altijara_widget_supports( $widget, 'source' ) && 'issues' === $source ) {
			$attrs .= ' hidden';
		}
	}
	echo '<div class="' . esc_attr( $class ) . '"' . $attrs . '>';
	echo '<input type="hidden" name="' . esc_attr( $field ) . '[include_children]" value="0">';
	echo '<label><input type="checkbox" name="' . esc_attr( $field ) . '[include_children]" value="1" ' . checked( $checked, true, false ) . '> ';
	echo esc_html__( 'Include subcategories', 'altijara' );
	echo '</label>';
	echo '<span class="altijara-field__hint">' . esc_html__( 'On: also apply the category filter to child categories. Off: only the selected categories themselves.', 'altijara' ) . '</span>';
	echo '</div>';
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

	$is_hero = ! empty( $args['is_hero'] ) || 'hero' === ( $section['zone'] ?? '' ) || ( function_exists( 'altijara_hero_section_ids' ) && in_array( $id, altijara_hero_section_ids(), true ) );
	$widget  = ! empty( $section['widget'] ) ? $section['widget'] : altijara_layout_to_widget( $section['layout'] ?? '' );
	$wdef    = function_exists( 'altijara_get_widget_definition' ) ? altijara_get_widget_definition( $widget ) : null;
	$choices = altijara_get_category_choices();
	$tags    = altijara_get_tag_choices();
	$manual  = ! empty( $section['manual_posts'] ) ? implode( ',', array_map( 'absint', (array) $section['manual_posts'] ) ) : '';
	$issues  = ! empty( $section['issue_ids'] ) ? implode( ',', array_map( 'absint', (array) $section['issue_ids'] ) ) : '';
	$prefix  = ! empty( $args['field_prefix'] ) ? (string) $args['field_prefix'] : 'altijara_sections';
	$order_f = ! empty( $args['order_field'] ) ? (string) $args['order_field'] : 'altijara_sections_order';
	$field   = $prefix . '[' . esc_attr( $id ) . ']';
	$titles  = altijara_get_post_titles_map( $section['manual_posts'] ?? array() );
	$issue_t = altijara_get_post_titles_map( $section['issue_ids'] ?? array() );
	$label   = altijara_strip_hero_label_prefix( isset( $section['label'] ) ? (string) $section['label'] : (string) $id );
	$family  = ( 'article_grid' === $widget ) ? 'card' : 'block';
	$variant = isset( $section['variant'] ) ? (string) $section['variant'] : 'default';
	$pair_with = isset( $section['pair_with'] ) ? sanitize_key( (string) $section['pair_with'] ) : '';
	// Paired tech + most-read always uses the cars_row silhouette / template.
	if ( 'article_grid' === $widget && $pair_with ) {
		$variant = 'cars_row';
	}
	$paired    = $pair_with ? '1' : '';
	$shape     = function_exists( 'altijara_builder_shape_key' )
		? altijara_builder_shape_key( $widget, $variant, $paired )
		: 'block';
	$visible = ! empty( $section['visible'] );

	$has_display = altijara_widget_supports( $widget, 'label' )
		|| altijara_widget_supports( $widget, 'variant' )
		|| altijara_widget_supports( $widget, 'sidebar_count' )
		|| altijara_widget_supports( $widget, 'featured_rows' )
		|| altijara_widget_supports( $widget, 'news_size' );

	$has_source = altijara_widget_supports( $widget, 'category' )
		|| altijara_widget_supports( $widget, 'tag' )
		|| altijara_widget_supports( $widget, 'source' )
		|| altijara_widget_supports( $widget, 'issue_ids' )
		|| altijara_widget_supports( $widget, 'manual_posts' );

	$has_query = altijara_widget_supports( $widget, 'count' )
		|| altijara_widget_supports( $widget, 'per_load' )
		|| altijara_widget_supports( $widget, 'button_label' )
		|| altijara_widget_supports( $widget, 'offset' )
		|| altijara_widget_supports( $widget, 'orderby' )
		|| altijara_widget_supports( $widget, 'view_period' )
		|| altijara_widget_supports( $widget, 'band' )
		|| altijara_widget_supports( $widget, 'lower_order' );
	?>
	<details class="altijara-section-card" data-section-id="<?php echo esc_attr( $id ); ?>" data-widget="<?php echo esc_attr( $widget ); ?>" data-zone="<?php echo esc_attr( $is_hero ? 'hero' : 'body' ); ?>" data-shape="<?php echo esc_attr( $shape ); ?>" data-pair-with="<?php echo esc_attr( $pair_with ); ?>">
		<summary>
			<?php if ( ! $is_hero ) : ?>
				<span class="altijara-section-drag dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'altijara' ); ?>" aria-hidden="true"></span>
			<?php endif; ?>
			<?php
			if ( function_exists( 'altijara_render_builder_shape' ) ) {
				altijara_render_builder_shape( $shape, 'sm' );
			}
			?>
			<strong class="altijara-section-summary-label"><?php echo esc_html( $label ); ?></strong>
			<span class="altijara-widget-badge altijara-widget-badge--<?php echo esc_attr( $family ); ?>"><?php echo esc_html( ( is_array( $wdef ) && ! empty( $wdef['label'] ) ) ? $wdef['label'] : $widget ); ?></span>
			<span class="altijara-section-summary-meta">
				<span
					class="altijara-section-status <?php echo $visible ? 'is-on' : 'is-off'; ?>"
					data-label-on="<?php echo esc_attr__( 'Visible', 'altijara' ); ?>"
					data-label-off="<?php echo esc_attr__( 'Hidden', 'altijara' ); ?>"
				>
					<span class="altijara-section-status__dot" aria-hidden="true"></span>
					<?php echo $visible ? esc_html__( 'Visible', 'altijara' ) : esc_html__( 'Hidden', 'altijara' ); ?>
				</span>
				<?php if ( ! $is_hero ) : ?>
					<button type="button" class="altijara-section-delete" title="<?php esc_attr_e( 'Remove section', 'altijara' ); ?>" aria-label="<?php esc_attr_e( 'Remove section', 'altijara' ); ?>">
						<span class="dashicons dashicons-trash" aria-hidden="true"></span>
					</button>
				<?php endif; ?>
			</span>
		</summary>
		<div class="altijara-section-body">
			<input type="hidden" name="<?php echo esc_attr( $field ); ?>[widget]" value="<?php echo esc_attr( $widget ); ?>">
			<input type="hidden" name="<?php echo esc_attr( $field ); ?>[zone]" value="<?php echo esc_attr( $is_hero ? 'hero' : 'body' ); ?>">
			<input type="hidden" name="<?php echo esc_attr( $field ); ?>[pair_with]" class="altijara-section-pair" value="<?php echo esc_attr( $pair_with ); ?>">
			<?php
			// article_grid has no visible variant select — still POST the layout so cars_row survives save.
			if ( 'article_grid' === $widget ) :
				?>
				<input type="hidden" name="<?php echo esc_attr( $field ); ?>[variant]" value="<?php echo esc_attr( $variant ); ?>">
			<?php endif; ?>
			<?php if ( ! $is_hero ) : ?>
				<input type="hidden" class="altijara-body-order-item" name="<?php echo esc_attr( $order_f ); ?>[]" value="<?php echo esc_attr( $id ); ?>">
			<?php endif; ?>

			<?php altijara_admin_field_group_open( __( 'Visibility', 'altijara' ) ); ?>
				<div class="altijara-field">
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $field ); ?>[visible]" value="1" <?php checked( $visible ); ?>>
						<?php esc_html_e( 'Show this module on the front end', 'altijara' ); ?>
					</label>
				</div>
			<?php altijara_admin_field_group_close(); ?>

			<?php if ( altijara_widget_supports( $widget, 'ad_creative' ) ) : ?>
				<?php
				$ad_shortcode = isset( $section['ad_shortcode'] ) ? (string) $section['ad_shortcode'] : '';
				$ad_image     = isset( $section['ad_image'] ) ? (string) $section['ad_image'] : '';
				$ad_url       = isset( $section['ad_url'] ) ? (string) $section['ad_url'] : '';
				altijara_admin_field_group_open(
					__( 'Advertisement', 'altijara' ),
					__( 'Shortcode wins when set. Otherwise the image and link below are shown. Leave image empty to use the theme default creative.', 'altijara' )
				);
				?>
				<div class="altijara-field">
					<label class="altijara-field__label" for="<?php echo esc_attr( $id ); ?>-ad-shortcode"><?php esc_html_e( 'Ad shortcode', 'altijara' ); ?></label>
					<input type="text" class="large-text code" id="<?php echo esc_attr( $id ); ?>-ad-shortcode" name="<?php echo esc_attr( $field ); ?>[ad_shortcode]" value="<?php echo esc_attr( $ad_shortcode ); ?>" placeholder='[the_ad id="123"]'>
				</div>
				<div class="altijara-field">
					<span class="altijara-field__label"><?php esc_html_e( 'Fallback image', 'altijara' ); ?></span>
					<div class="altijara-media-field">
						<div class="altijara-media-preview <?php echo $ad_image ? 'has-image' : ''; ?>">
							<?php if ( $ad_image ) : ?>
								<img src="<?php echo esc_url( $ad_image ); ?>" alt="">
							<?php else : ?>
								<span class="altijara-media-preview__empty"><?php esc_html_e( 'No image selected', 'altijara' ); ?></span>
							<?php endif; ?>
						</div>
						<div class="altijara-media-controls">
							<input type="url" class="regular-text altijara-media-url" name="<?php echo esc_attr( $field ); ?>[ad_image]" value="<?php echo esc_attr( $ad_image ); ?>" placeholder="https://…">
							<div class="altijara-media-buttons">
								<button type="button" class="button altijara-media-upload"><?php esc_html_e( 'Choose from library', 'altijara' ); ?></button>
								<button type="button" class="button altijara-media-clear"><?php esc_html_e( 'Clear', 'altijara' ); ?></button>
							</div>
						</div>
					</div>
				</div>
				<div class="altijara-field">
					<label class="altijara-field__label" for="<?php echo esc_attr( $id ); ?>-ad-url"><?php esc_html_e( 'Fallback link', 'altijara' ); ?></label>
					<input type="url" class="regular-text" id="<?php echo esc_attr( $id ); ?>-ad-url" name="<?php echo esc_attr( $field ); ?>[ad_url]" value="<?php echo esc_attr( $ad_url ); ?>" placeholder="https://…">
				</div>
				<?php altijara_admin_field_group_close(); ?>
			<?php endif; ?>

			<?php if ( $has_display ) : ?>
				<?php altijara_admin_field_group_open( __( 'Display', 'altijara' ) ); ?>
					<?php if ( altijara_widget_supports( $widget, 'label' ) ) : ?>
						<div class="altijara-field">
							<label class="altijara-field__label"><?php esc_html_e( 'Title', 'altijara' ); ?></label>
							<input type="text" class="regular-text" name="<?php echo esc_attr( $field ); ?>[label]" value="<?php echo esc_attr( $label ); ?>">
							<?php if ( altijara_widget_supports( $widget, 'label_admin_only' ) ) : ?>
								<span class="altijara-field__hint"><?php esc_html_e( 'Name in this editor only — this block has no front-end heading.', 'altijara' ); ?></span>
							<?php elseif ( 'issues_carousel' === $widget ) : ?>
								<span class="altijara-field__hint"><?php esc_html_e( 'Leave empty to auto-fill from the issue numbers shown (e.g. إصدارات مجلة التجارة (الأعداد 715 – 704)). Type a title to override.', 'altijara' ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( altijara_widget_supports( $widget, 'variant' ) && 'article_grid' !== $widget && $wdef && ! empty( $wdef['variants'] ) ) : ?>
						<div class="altijara-field">
							<label class="altijara-field__label"><?php esc_html_e( 'Layout style', 'altijara' ); ?></label>
							<select name="<?php echo esc_attr( $field ); ?>[variant]">
								<?php foreach ( $wdef['variants'] as $vk => $vdef ) : ?>
									<option value="<?php echo esc_attr( $vk ); ?>" <?php selected( $section['variant'] ?? 'default', $vk ); ?>>
										<?php echo esc_html( $vdef['label'] ?? $vk ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endif; ?>

					<?php if ( altijara_widget_supports( $widget, 'sidebar_count' ) ) : ?>
						<div class="altijara-field">
							<label class="altijara-field__label"><?php esc_html_e( 'Recommended posts', 'altijara' ); ?></label>
							<input type="number" min="0" max="30" step="1" class="small-text" name="<?php echo esc_attr( $field ); ?>[count]" value="<?php echo esc_attr( (string) max( 0, min( 30, (int) ( $section['count'] ?? 3 ) ) ) ); ?>">
							<span class="altijara-field__hint"><?php esc_html_e( '0–30 beside the hero (0 hides the list). Full-width hero also hides it.', 'altijara' ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( altijara_widget_supports( $widget, 'featured_rows' ) ) : ?>
						<div class="altijara-field">
							<label class="altijara-field__label"><?php esc_html_e( 'Featured card rows', 'altijara' ); ?></label>
							<select name="<?php echo esc_attr( $field ); ?>[count]">
								<option value="4" <?php selected( (int) ( $section['count'] ?? 8 ), 4 ); ?>><?php esc_html_e( '1 row (4 cards)', 'altijara' ); ?></option>
								<option value="8" <?php selected( (int) ( $section['count'] ?? 8 ), 8 ); ?>><?php esc_html_e( '2 rows (8 cards)', 'altijara' ); ?></option>
								<option value="12" <?php selected( (int) ( $section['count'] ?? 8 ), 12 ); ?>><?php esc_html_e( '3 rows (12 cards)', 'altijara' ); ?></option>
							</select>
						</div>
					<?php endif; ?>

					<?php if ( altijara_widget_supports( $widget, 'news_size' ) ) : ?>
						<div class="altijara-field">
							<label class="altijara-field__label"><?php esc_html_e( 'Latest news per page', 'altijara' ); ?></label>
							<input type="number" min="1" max="50" step="1" class="small-text" name="<?php echo esc_attr( $field ); ?>[count]" value="<?php echo esc_attr( (string) max( 1, min( 50, (int) ( $section['count'] ?? 10 ) ) ) ); ?>">
							<span class="altijara-field__hint"><?php esc_html_e( '1–50 posts per page in this list.', 'altijara' ); ?></span>
						</div>
					<?php endif; ?>

					<?php
					// Category-archive modules have no category picker — scope still applies to the viewed category.
					if (
						function_exists( 'altijara_widget_uses_category_scope' )
						&& altijara_widget_uses_category_scope( $widget )
						&& ! altijara_widget_supports( $widget, 'category' )
					) {
						echo '<p class="altijara-field__hint" style="margin:0 0 10px;">' . esc_html__( 'Uses the current archive category.', 'altijara' ) . '</p>';
						altijara_admin_render_include_children_field( $field, $section, $widget, false );
					}
					?>
				<?php altijara_admin_field_group_close(); ?>
			<?php endif; ?>

			<?php if ( $has_source ) : ?>
				<?php
				$has_slide_source = altijara_widget_supports( $widget, 'source' );
				$slide_source    = $has_slide_source ? ( ( $section['source'] ?? '' ) === 'issues' ? 'issues' : 'posts' ) : '';
				$source_hint     = $has_slide_source
					? __( 'Pick Posts or Magazine issues — only the matching fields are shown.', 'altijara' )
					: __( 'Category / search picks override the automatic feed when set.', 'altijara' );
				$source_label    = in_array( $widget, array( 'magazine_slider', 'issues_lead', 'issues_carousel' ), true )
					? __( 'Slide source', 'altijara' )
					: __( 'Content type', 'altijara' );
				?>
				<?php altijara_admin_field_group_open( __( 'Content source', 'altijara' ), $source_hint, true ); ?>
					<?php if ( $has_slide_source ) : ?>
						<div class="altijara-field">
							<label class="altijara-field__label"><?php echo esc_html( $source_label ); ?></label>
							<select name="<?php echo esc_attr( $field ); ?>[source]" class="altijara-slide-source regular-text">
								<option value="posts" <?php selected( $slide_source, 'posts' ); ?>><?php esc_html_e( 'Posts', 'altijara' ); ?></option>
								<option value="issues" <?php selected( $slide_source, 'issues' ); ?>><?php esc_html_e( 'Magazine issues', 'altijara' ); ?></option>
							</select>
						</div>
					<?php endif; ?>

					<?php if ( altijara_widget_supports( $widget, 'category' ) ) : ?>
						<?php
						$surface         = isset( $args['surface'] ) ? sanitize_key( (string) $args['surface'] ) : '';
						$on_article      = in_array( $surface, array( 'article_body', 'article_sidebar' ), true );
						$on_cat_side     = in_array( $surface, array( 'category_sidebar', 'category_archive' ), true );
						$on_search_like  = in_array( $surface, array( 'search_sidebar', 'tag_sidebar' ), true );
						$has_article_src = altijara_widget_supports( $widget, 'article_source' ) || $on_article;
						$has_archive_src = altijara_widget_supports( $widget, 'archive_source' ) || $on_cat_side;
						$cat_hidden      = $has_slide_source && 'issues' === $slide_source;
						$cat_filter      = function_exists( 'altijara_section_category_filter' )
							? altijara_section_category_filter( $section )
							: ( ( $section['category_filter'] ?? '' ) === 'exclude' ? 'exclude' : 'include' );
						$cat_selected    = function_exists( 'altijara_section_category_ids' )
							? altijara_section_category_ids( $section )
							: array();
						if ( ! $cat_selected && ! empty( $section['category'] ) ) {
							$legacy = (string) $section['category'];
							if ( is_numeric( $legacy ) ) {
								$cat_selected = array( absint( $legacy ) );
							} elseif ( function_exists( 'altijara_resolve_category_id' ) ) {
								$rid = absint( altijara_resolve_category_id( $legacy ) );
								if ( $rid ) {
									$cat_selected = array( $rid );
								}
							}
						}
						$cat_source = function_exists( 'altijara_section_category_source' )
							? altijara_section_category_source( $section )
							: sanitize_key( (string) ( $section['category_source'] ?? '' ) );
						if ( $has_article_src && ! in_array( $cat_source, array( 'same', 'selected' ), true ) ) {
							$cat_source = $cat_selected ? 'selected' : 'same';
						}
						$cat_counts      = function_exists( 'altijara_get_category_choice_counts' ) ? altijara_get_category_choice_counts() : array();
						$cat_rows        = function_exists( 'altijara_get_category_choice_rows' ) ? altijara_get_category_choice_rows() : array();
						$cat_choices     = array();
						$cat_name_by_id  = array();
						$cat_has_posts   = false;
						$cat_any_empty   = false;
						foreach ( $cat_rows as $crow ) {
							if ( ! is_array( $crow ) ) {
								continue;
							}
							$cid = absint( $crow['id'] ?? 0 );
							if ( ! $cid ) {
								continue;
							}
							$cname = (string) ( $crow['name'] ?? $crow['label'] ?? ( '#' . $cid ) );
							$ccnt  = (int) ( $cat_counts[ $cid ] ?? (int) ( $crow['count'] ?? -1 ) );
							$cat_choices[] = array(
								'id'    => $cid,
								'name'  => $cname,
								'label' => (string) ( $crow['label'] ?? $cname ),
								'slug'  => (string) ( $crow['slug'] ?? '' ),
								'count' => $ccnt,
							);
							$cat_name_by_id[ $cid ] = $cname;
						}
						if ( ! $cat_choices ) {
							foreach ( $choices as $val => $lab ) {
								$cid = absint( $val );
								if ( ! $cid ) {
									continue;
								}
								$cname = preg_replace( '/\s*\(ID:\s*\d+\)\s*$/', '', (string) $lab );
								$ccnt  = (int) ( $cat_counts[ $cid ] ?? -1 );
								$cat_choices[] = array(
									'id'    => $cid,
									'name'  => $cname,
									'label' => (string) $lab,
									'slug'  => '',
									'count' => $ccnt,
								);
								$cat_name_by_id[ $cid ] = $cname;
							}
						}
						foreach ( $cat_selected as $cid ) {
							$ccnt = (int) ( $cat_counts[ $cid ] ?? -1 );
							if ( $ccnt > 0 ) {
								$cat_has_posts = true;
							} elseif ( 0 === $ccnt ) {
								$cat_any_empty = true;
							}
							if ( ! isset( $cat_name_by_id[ $cid ] ) ) {
								$term = get_term( $cid, 'category' );
								$cat_name_by_id[ $cid ] = ( $term && ! is_wp_error( $term ) ) ? (string) $term->name : ( '#' . $cid );
							}
						}
						$cat_empty = ! empty( $cat_selected ) && $cat_any_empty && ! $cat_has_posts && 'include' === $cat_filter;
						$cat_choices_json = wp_json_encode(
							$cat_choices,
							JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
						);
						if ( ! is_string( $cat_choices_json ) ) {
							$cat_choices_json = '[]';
						}
						?>
						<?php if ( $has_article_src ) : ?>
							<div class="altijara-field altijara-source-panel" data-source-panel="posts"<?php echo $cat_hidden ? ' hidden' : ''; ?>>
								<label class="altijara-field__label"><?php esc_html_e( 'Category source', 'altijara' ); ?></label>
								<select name="<?php echo esc_attr( $field ); ?>[category_source]" class="altijara-category-source regular-text">
									<option value="same" <?php selected( $cat_source, 'same' ); ?>><?php esc_html_e( 'Same as this article’s category', 'altijara' ); ?></option>
									<option value="selected" <?php selected( $cat_source, 'selected' ); ?>><?php esc_html_e( 'Selected categories', 'altijara' ); ?></option>
								</select>
								<span class="altijara-field__hint"><?php esc_html_e( 'Same = posts from this article’s category. Selected = Include or Exclude the categories you pick below. Chosen posts below replace the filter.', 'altijara' ); ?></span>
							</div>
						<?php endif; ?>
						<div class="altijara-category-source-selected"<?php echo ( $has_article_src && 'same' === $cat_source ) ? ' hidden' : ''; ?>>
							<div class="altijara-field altijara-source-panel" data-source-panel="posts"<?php echo $cat_hidden ? ' hidden' : ''; ?>>
								<label class="altijara-field__label"><?php esc_html_e( 'Category filter', 'altijara' ); ?></label>
								<select name="<?php echo esc_attr( $field ); ?>[category_filter]" class="regular-text">
									<option value="include" <?php selected( $cat_filter, 'include' ); ?>><?php esc_html_e( 'Include', 'altijara' ); ?></option>
									<option value="exclude" <?php selected( $cat_filter, 'exclude' ); ?>><?php esc_html_e( 'Exclude', 'altijara' ); ?></option>
								</select>
								<span class="altijara-field__hint"><?php esc_html_e( 'Include = only these categories. Exclude = all posts except these categories.', 'altijara' ); ?></span>
							</div>
							<div class="altijara-field altijara-source-panel" data-source-panel="posts"<?php echo $cat_hidden ? ' hidden' : ''; ?>>
								<label class="altijara-field__label"><?php esc_html_e( 'Categories', 'altijara' ); ?></label>
								<input type="hidden" name="<?php echo esc_attr( $field ); ?>[categories_present]" value="1">
								<div
									class="altijara-term-picker altijara-term-picker--categories altijara-cat-select"
									data-field-name="<?php echo esc_attr( $field ); ?>[categories][]"
								>
									<script type="application/json" class="altijara-term-picker__data"><?php echo $cat_choices_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON_HEX_* encoded. ?></script>
									<div class="altijara-term-picker__selected">
										<?php foreach ( $cat_selected as $cid ) : ?>
											<?php
											$cname = isset( $cat_name_by_id[ $cid ] ) ? $cat_name_by_id[ $cid ] : ( '#' . $cid );
											$ccnt  = (int) ( $cat_counts[ $cid ] ?? -1 );
											?>
											<span class="altijara-term-picker__chip" data-id="<?php echo esc_attr( (string) $cid ); ?>" data-count="<?php echo esc_attr( (string) $ccnt ); ?>">
												<span class="altijara-term-picker__chip-label"><?php echo esc_html( $cname ); ?></span>
												<button type="button" class="altijara-term-picker__remove" aria-label="<?php esc_attr_e( 'Remove', 'altijara' ); ?>">&times;</button>
												<input type="hidden" name="<?php echo esc_attr( $field ); ?>[categories][]" value="<?php echo esc_attr( (string) $cid ); ?>">
											</span>
										<?php endforeach; ?>
									</div>
									<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>-cat-search"><?php esc_html_e( 'Search categories', 'altijara' ); ?></label>
									<input
										type="search"
										id="<?php echo esc_attr( $id ); ?>-cat-search"
										class="altijara-term-picker__search"
										placeholder="<?php esc_attr_e( 'Search categories to add…', 'altijara' ); ?>"
										autocomplete="off"
										spellcheck="false"
									>
									<ul class="altijara-term-picker__results" hidden></ul>
								</div>
								<p class="altijara-field__hint altijara-empty-tag-warning"<?php echo $cat_empty ? '' : ' hidden'; ?>>
									<?php esc_html_e( 'None of the selected categories have posts. The column will stay empty until at least one has articles.', 'altijara' ); ?>
								</p>
								<span class="altijara-field__hint"><?php
								if ( $has_article_src ) {
									esc_html_e( 'Pick categories to include or exclude. Chosen posts below replace the filter.', 'altijara' );
								} elseif ( $has_archive_src ) {
									esc_html_e( 'Leave empty to use the category page being viewed (Include). Chosen posts below replace this filter.', 'altijara' );
								} elseif ( $on_search_like ) {
									esc_html_e( 'Leave empty for sitewide. Add categories to include or exclude from the list.', 'altijara' );
								} else {
									esc_html_e( 'Leave empty for all categories. Offset skips posts within this same filter.', 'altijara' );
								}
								?></span>
							</div>
							<?php altijara_admin_render_include_children_field( $field, $section, $widget, $has_slide_source ); ?>
						</div>
					<?php endif; ?>

					<?php if ( altijara_widget_supports( $widget, 'tag' ) ) : ?>
						<?php
						$tag_counts   = function_exists( 'altijara_get_tag_choice_counts' ) ? altijara_get_tag_choice_counts() : array();
						$tag_selected = function_exists( 'altijara_section_tag_ids' ) ? altijara_section_tag_ids( $section ) : array_filter( array( absint( $section['tag'] ?? 0 ) ) );
						$tag_choices  = array();
						$tag_has_posts = false;
						$tag_any_empty = false;
						foreach ( $tags as $val => $lab ) {
							if ( '' === (string) $val || 0 === (int) $val ) {
								continue;
							}
							$tid = (int) $val;
							$cnt = (int) ( $tag_counts[ $tid ] ?? -1 );
							$tag_choices[] = array(
								'id'    => $tid,
								'name'  => preg_replace( '/\s*\(ID:\s*\d+\)\s*$/', '', (string) $lab ),
								'label' => (string) $lab,
								'count' => $cnt,
							);
							if ( ! in_array( $tid, $tag_selected, true ) ) {
								continue;
							}
							if ( $cnt > 0 ) {
								$tag_has_posts = true;
							} elseif ( 0 === $cnt ) {
								$tag_any_empty = true;
							}
						}
						// Column empties only when every selected tag has zero posts (tag__in is OR).
						$tag_empty = ! empty( $tag_selected ) && $tag_any_empty && ! $tag_has_posts;
						$tag_choices_json = wp_json_encode(
							$tag_choices,
							JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
						);
						if ( ! is_string( $tag_choices_json ) ) {
							$tag_choices_json = '[]';
						}
						$tag_name_by_id = array();
						foreach ( $tag_choices as $trow ) {
							$tag_name_by_id[ (int) $trow['id'] ] = (string) $trow['name'];
						}
						?>
						<div class="altijara-field altijara-source-panel" data-source-panel="posts"<?php echo ( $has_slide_source && 'issues' === $slide_source ) ? ' hidden' : ''; ?>>
							<label class="altijara-field__label"><?php esc_html_e( 'Tags', 'altijara' ); ?></label>
							<input type="hidden" name="<?php echo esc_attr( $field ); ?>[tag_present]" value="1">
							<div
								class="altijara-term-picker altijara-term-picker--tags altijara-tag-select"
								data-field-name="<?php echo esc_attr( $field ); ?>[tag][]"
							>
								<script type="application/json" class="altijara-term-picker__data"><?php echo $tag_choices_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON_HEX_* encoded. ?></script>
								<div class="altijara-term-picker__selected">
									<?php foreach ( $tag_selected as $tid ) : ?>
										<?php
										$tname = isset( $tag_name_by_id[ $tid ] ) ? $tag_name_by_id[ $tid ] : ( '#' . $tid );
										$tcnt  = (int) ( $tag_counts[ $tid ] ?? -1 );
										?>
										<span class="altijara-term-picker__chip" data-id="<?php echo esc_attr( (string) $tid ); ?>" data-count="<?php echo esc_attr( (string) $tcnt ); ?>">
											<span class="altijara-term-picker__chip-label"><?php echo esc_html( $tname ); ?></span>
											<button type="button" class="altijara-term-picker__remove" aria-label="<?php esc_attr_e( 'Remove', 'altijara' ); ?>">&times;</button>
											<input type="hidden" name="<?php echo esc_attr( $field ); ?>[tag][]" value="<?php echo esc_attr( (string) $tid ); ?>">
										</span>
									<?php endforeach; ?>
								</div>
								<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>-tag-search"><?php esc_html_e( 'Search tags', 'altijara' ); ?></label>
								<input
									type="search"
									id="<?php echo esc_attr( $id ); ?>-tag-search"
									class="altijara-term-picker__search"
									placeholder="<?php esc_attr_e( 'Search tags to add…', 'altijara' ); ?>"
									autocomplete="off"
									spellcheck="false"
								>
								<ul class="altijara-term-picker__results" hidden></ul>
							</div>
							<span class="altijara-field__hint"><?php esc_html_e( 'Click the field to search and add tags. Leave empty to ignore tags.', 'altijara' ); ?></span>
							<p class="altijara-field__hint altijara-empty-tag-warning"<?php echo $tag_empty ? '' : ' hidden'; ?>>
								<?php esc_html_e( 'None of the selected tags have posts. The column will stay empty until at least one tag has articles.', 'altijara' ); ?>
							</p>
						</div>
					<?php endif; ?>

					<?php if ( altijara_widget_supports( $widget, 'band' ) && $has_slide_source ) : ?>
						<div class="altijara-field altijara-source-panel" data-source-panel="issues"<?php echo ( 'posts' === $slide_source ) ? ' hidden' : ''; ?>>
							<span class="altijara-field__label"><?php esc_html_e( 'Issue number range', 'altijara' ); ?></span>
							<div class="altijara-field-row">
								<label><?php esc_html_e( 'From', 'altijara' ); ?>
									<input type="number" min="0" max="9999" step="1" name="<?php echo esc_attr( $field ); ?>[band_min]" value="<?php echo esc_attr( (string) (int) ( $section['band_min'] ?? 0 ) ); ?>" style="width:90px" inputmode="numeric">
								</label>
								<label><?php esc_html_e( 'To', 'altijara' ); ?>
									<input type="number" min="0" max="9999" step="1" name="<?php echo esc_attr( $field ); ?>[band_max]" value="<?php echo esc_attr( (string) (int) ( $section['band_max'] ?? 0 ) ); ?>" style="width:90px" inputmode="numeric">
								</label>
							</div>
							<span class="altijara-field__hint"><?php esc_html_e( 'Inclusive issue numbers (e.g. 704–717). 0 = no bound. Count caps how many cards are shown. If From is higher than To, they are swapped on save.', 'altijara' ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( altijara_widget_supports( $widget, 'issue_ids' ) ) : ?>
						<div class="altijara-post-picker altijara-source-panel" data-source-panel="issues" data-post-type="issue" data-field="<?php echo esc_attr( $field ); ?>[issue_ids]"<?php echo ( $has_slide_source && 'posts' === $slide_source ) ? ' hidden' : ''; ?>>
							<label class="altijara-field__label"><?php esc_html_e( 'Specific issues', 'altijara' ); ?></label>
							<span class="altijara-field__hint"><?php
							if ( 'issues_lead' === $widget ) {
								esc_html_e( 'Leave empty for auto-latest (highest issue number). Picking an issue freezes the hero until cleared.', 'altijara' );
							} else {
								esc_html_e( 'Optional. Leave empty to pull magazine issues by issue number (highest first).', 'altijara' );
							}
							?></span>
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
						<div class="altijara-post-picker<?php echo $has_slide_source ? ' altijara-source-panel' : ''; ?>"<?php echo $has_slide_source ? ' data-source-panel="posts"' : ''; ?> data-post-type="<?php echo 'magazine_slider' === $widget ? 'any' : 'post'; ?>" data-field="<?php echo esc_attr( $field ); ?>[manual_posts]"<?php echo ( $has_slide_source && 'issues' === $slide_source ) ? ' hidden' : ''; ?>>
							<label class="altijara-field__label"><?php esc_html_e( 'Specific posts', 'altijara' ); ?></label>
							<span class="altijara-field__hint"><?php esc_html_e( 'Optional. Leave empty to pull from the category above.', 'altijara' ); ?></span>
							<input type="hidden" class="altijara-picker-ids" name="<?php echo esc_attr( $field ); ?>[manual_posts]" value="<?php echo esc_attr( $manual ); ?>">
							<input type="search" class="altijara-picker-search regular-text" placeholder="<?php esc_attr_e( 'Search posts by title…', 'altijara' ); ?>" autocomplete="off">
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
				<?php altijara_admin_field_group_close(); ?>
			<?php endif; ?>

			<?php if ( $has_query ) : ?>
				<?php altijara_admin_field_group_open( __( 'Query & order', 'altijara' ), '', true ); ?>
					<?php if ( altijara_widget_supports( $widget, 'lower_order' ) ) : ?>
						<?php
						$lower_order = array();
						$raw_order   = isset( $section['sidebar_order'] ) ? (string) $section['sidebar_order'] : '';
						if ( '' !== $raw_order ) {
							$lower_order = array_values( array_filter( array_map( 'sanitize_key', explode( ',', $raw_order ) ) ) );
						}
						if ( ! $lower_order && function_exists( 'altijara_archive_sidebar_widgets' ) ) {
							$lower_order = altijara_archive_sidebar_widgets();
						}
						$lower_positions = function_exists( 'altijara_archive_sidebar_position_map' )
							? altijara_archive_sidebar_position_map( array( 'order' => $lower_order ) )
							: array();
						$lower_labels = function_exists( 'altijara_archive_sidebar_labels' ) ? altijara_archive_sidebar_labels() : array();
						?>
						<div class="altijara-field">
							<span class="altijara-field__label"><?php esc_html_e( 'Lower column order', 'altijara' ); ?></span>
							<span class="altijara-field__hint"><?php esc_html_e( '1 is the top. Configure advertisement creative on the Advertisement widget.', 'altijara' ); ?></span>
							<div class="altijara-field-row" style="margin-top:8px;">
								<?php foreach ( $lower_labels as $lkey => $llabel ) : ?>
									<label><?php echo esc_html( $llabel ); ?>
										<select name="<?php echo esc_attr( $field ); ?>[sidebar_positions][<?php echo esc_attr( $lkey ); ?>]">
											<?php for ( $n = 1; $n <= 3; $n++ ) : ?>
												<option value="<?php echo esc_attr( (string) $n ); ?>" <?php selected( (int) ( $lower_positions[ $lkey ] ?? $n ), $n ); ?>><?php echo esc_html( (string) $n ); ?></option>
											<?php endfor; ?>
										</select>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( altijara_widget_supports( $widget, 'count' ) || altijara_widget_supports( $widget, 'offset' ) || altijara_widget_supports( $widget, 'orderby' ) || altijara_widget_supports( $widget, 'order' ) ) : ?>
						<div class="altijara-field-row altijara-field-row--query">
							<?php if ( altijara_widget_supports( $widget, 'count' ) ) : ?>
								<?php
								$count_max   = ( 'article_related' === $widget ) ? 12 : 20;
								$no_count_max = ( 'article_list' === $widget );
								$count_val   = (int) $section['count'];
								if ( $count_val < 1 ) {
									$count_val = 1;
								}
								if ( ! $no_count_max && $count_val > $count_max ) {
									$count_val = $count_max;
								}
								?>
								<label><?php esc_html_e( 'Count', 'altijara' ); ?>
									<input type="number" min="1"<?php echo $no_count_max ? '' : ' max="' . esc_attr( (string) $count_max ) . '"'; ?> name="<?php echo esc_attr( $field ); ?>[count]" value="<?php echo esc_attr( (string) $count_val ); ?>">
								</label>
							<?php endif; ?>
							<?php if ( altijara_widget_supports( $widget, 'per_load' ) ) : ?>
								<label><?php esc_html_e( 'Per “load more” click', 'altijara' ); ?>
									<input type="number" min="0" max="12" name="<?php echo esc_attr( $field ); ?>[per_load]" value="<?php echo esc_attr( (string) (int) ( $section['per_load'] ?? 0 ) ); ?>">
								</label>
							<?php endif; ?>
							<?php if ( altijara_widget_supports( $widget, 'button_label' ) ) : ?>
								<label><?php esc_html_e( 'Load more button text', 'altijara' ); ?>
									<input type="text" class="regular-text" name="<?php echo esc_attr( $field ); ?>[button_label]" value="<?php echo esc_attr( (string) ( $section['button_label'] ?? '' ) ); ?>" placeholder="<?php echo esc_attr__( 'تحميل المزيد', 'altijara' ); ?>">
								</label>
							<?php endif; ?>
							<?php if ( altijara_widget_supports( $widget, 'offset' ) ) : ?>
								<label><?php esc_html_e( 'Offset', 'altijara' ); ?>
									<input type="number" min="0" max="100" name="<?php echo esc_attr( $field ); ?>[offset]" value="<?php echo esc_attr( (string) (int) $section['offset'] ); ?>">
								</label>
							<?php endif; ?>
							<?php if ( altijara_widget_supports( $widget, 'orderby' ) ) : ?>
								<label><?php esc_html_e( 'Order by', 'altijara' ); ?>
									<select name="<?php echo esc_attr( $field ); ?>[orderby]">
										<?php
										$order_opts = array(
											'date'          => __( 'Date', 'altijara' ),
											'modified'      => __( 'Modified', 'altijara' ),
											'title'         => __( 'Title', 'altijara' ),
											'rand'          => __( 'Random', 'altijara' ),
											'comment_count' => __( 'Comments', 'altijara' ),
											'issue_number'  => __( 'Issue number', 'altijara' ),
										);
										// Issue-number sort only makes sense for issue feeds.
										if ( ! in_array( $widget, array( 'issues_lead', 'issues_carousel', 'magazine_slider' ), true )
											&& ! ( altijara_widget_supports( $widget, 'source' ) && 'issues' === ( $section['source'] ?? '' ) ) ) {
											unset( $order_opts['issue_number'] );
										}
										foreach ( $order_opts as $k => $lab ) :
											?>
											<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $section['orderby'], $k ); ?>><?php echo esc_html( $lab ); ?></option>
										<?php endforeach; ?>
									</select>
								</label>
							<?php endif; ?>
							<?php if ( altijara_widget_supports( $widget, 'orderby' ) || altijara_widget_supports( $widget, 'order' ) ) : ?>
								<label><?php echo 'cat_sidebar_magazine' === $widget ? esc_html__( 'Issue order', 'altijara' ) : esc_html__( 'Direction', 'altijara' ); ?>
									<select name="<?php echo esc_attr( $field ); ?>[order]">
										<?php if ( 'cat_sidebar_magazine' === $widget ) : ?>
											<option value="DESC" <?php selected( $section['order'], 'DESC' ); ?>><?php esc_html_e( 'To → From (highest # first)', 'altijara' ); ?></option>
											<option value="ASC" <?php selected( $section['order'], 'ASC' ); ?>><?php esc_html_e( 'From → To (lowest # first)', 'altijara' ); ?></option>
										<?php else : ?>
											<option value="DESC" <?php selected( $section['order'], 'DESC' ); ?>>DESC</option>
											<option value="ASC" <?php selected( $section['order'], 'ASC' ); ?>>ASC</option>
										<?php endif; ?>
									</select>
								</label>
							<?php endif; ?>
						</div>
						<?php if ( altijara_widget_supports( $widget, 'per_load' ) ) : ?>
							<p class="description" style="margin:8px 0 0;"><?php esc_html_e( 'Count = cards shown first. Per click: 0 means 2 more each time. Button text: leave empty for “تحميل المزيد”.', 'altijara' ); ?></p>
						<?php endif; ?>
						<?php if ( altijara_widget_supports( $widget, 'offset' ) ) : ?>
							<p class="description" style="margin:8px 0 0;"><?php
							if ( altijara_widget_supports( $widget, 'archive_source' ) ) {
								esc_html_e( 'Skip this many posts from the same Content source filter so this block starts after the ones above it.', 'altijara' );
							} else {
								esc_html_e( 'Skip this many posts from the same Content source filter (same categories / order).', 'altijara' );
							}
							?></p>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ( altijara_widget_supports( $widget, 'view_period' ) ) : ?>
						<div class="altijara-field" style="margin-top:12px;">
							<label class="altijara-field__label"><?php esc_html_e( 'Most-read period', 'altijara' ); ?></label>
							<select name="<?php echo esc_attr( $field ); ?>[view_period]">
								<?php
								foreach ( array(
									'day'   => __( 'Last 24 hours', 'altijara' ),
									'week'  => __( 'Last week', 'altijara' ),
									'month' => __( 'Last 30 days', 'altijara' ),
									'year'  => __( 'Last year', 'altijara' ),
									'all'   => __( 'All time', 'altijara' ),
								) as $k => $lab ) :
									?>
									<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $section['view_period'] ?? 'all', $k ); ?>><?php echo esc_html( $lab ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endif; ?>

					<?php if ( altijara_widget_supports( $widget, 'band' ) && ! altijara_widget_supports( $widget, 'source' ) ) : ?>
						<div class="altijara-field" style="margin-top:12px;">
							<span class="altijara-field__label"><?php esc_html_e( 'Issue number range', 'altijara' ); ?></span>
							<div class="altijara-field-row">
								<label><?php esc_html_e( 'From issue #', 'altijara' ); ?>
									<input type="number" min="0" max="9999" step="1" name="<?php echo esc_attr( $field ); ?>[band_min]" value="<?php echo esc_attr( (string) (int) ( $section['band_min'] ?? 0 ) ); ?>" style="width:90px" inputmode="numeric">
								</label>
								<label><?php esc_html_e( 'To issue #', 'altijara' ); ?>
									<input type="number" min="0" max="9999" step="1" name="<?php echo esc_attr( $field ); ?>[band_max]" value="<?php echo esc_attr( (string) (int) ( $section['band_max'] ?? 0 ) ); ?>" style="width:90px" inputmode="numeric">
								</label>
							</div>
							<span class="altijara-field__hint"><?php
							if ( 'cat_sidebar_magazine' === $widget ) {
								esc_html_e( 'Magazine issue numbers to include (e.g. 704–717). 0 = no bound. Count caps how many covers are shown. Use Issue order above for From→To or To→From. If From is higher than To, they are swapped on save.', 'altijara' );
							} else {
								esc_html_e( '0 = no bound. Lead uses offset/count; carousel filters by number range. If From is higher than To, they are swapped on save.', 'altijara' );
							}
							?></span>
						</div>
					<?php endif; ?>
				<?php altijara_admin_field_group_close(); ?>
			<?php endif; ?>

			<?php if ( altijara_widget_supports( $widget, 'cache' ) ) : ?>
				<p class="description"><?php esc_html_e( 'Saving clears section cache. Use Tools → Purge if needed.', 'altijara' ); ?></p>
			<?php endif; ?>
		</div>
	</details>
	<?php
}
