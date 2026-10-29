<?php
/**
 * Shared archive/admin panel field helpers.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Categories for archive assignment checklists.
 *
 * @return WP_Term[]
 */
function altijara_admin_archive_categories() {
	$terms = get_categories(
		array(
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);
	if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
		return array();
	}
	return $terms;
}

/**
 * Labels for the three lower-column widgets.
 *
 * @return array<string, string>
 */
function altijara_archive_sidebar_labels() {
	return array(
		'ad'        => __( 'Advertisement', 'altijara' ),
		'magazine'  => __( 'Magazine issues slider', 'altijara' ),
		'most_read' => __( 'Most read', 'altijara' ),
	);
}

/**
 * Position numbers for the lower-column selects.
 *
 * @param array<string, mixed> $sidebar Sanitized column.
 * @return array<string, int>
 */
function altijara_archive_sidebar_position_map( $sidebar ) {
	$labels    = altijara_archive_sidebar_labels();
	$positions = array();
	$slot      = 1;
	$order     = isset( $sidebar['order'] ) && is_array( $sidebar['order'] ) ? $sidebar['order'] : array();
	foreach ( $order as $key ) {
		$key = sanitize_key( (string) $key );
		if ( isset( $labels[ $key ] ) && ! isset( $positions[ $key ] ) ) {
			$positions[ $key ] = $slot;
			$slot++;
		}
	}
	foreach ( array_keys( $labels ) as $key ) {
		if ( ! isset( $positions[ $key ] ) ) {
			$positions[ $key ] = $slot;
			$slot++;
		}
	}
	return $positions;
}

/**
 * Visibility and order inputs for the lower column.
 *
 * @param array<string, mixed> $sidebar   Sanitized column.
 * @param string               $name      Field name prefix.
 * @param string               $id_prefix Element id prefix.
 */
function altijara_render_archive_sidebar_inputs( $sidebar, $name, $id_prefix ) {
	$name      = preg_replace( '/[^a-z0-9_]/', '', (string) $name );
	$id_prefix = preg_replace( '/[^a-z0-9_\-]/', '', (string) $id_prefix );
	if ( '' === $name ) {
		$name = 'altijara_archive_sidebar';
	}
	if ( '' === $id_prefix ) {
		$id_prefix = $name;
	}
	$labels     = altijara_archive_sidebar_labels();
	$positions  = altijara_archive_sidebar_position_map( is_array( $sidebar ) ? $sidebar : array() );
	$visible_id = $id_prefix . '_visible';
	?>
	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Column', 'altijara' ); ?></th>
			<td>
				<label for="<?php echo esc_attr( $visible_id ); ?>">
					<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[visible]" id="<?php echo esc_attr( $visible_id ); ?>" value="1" <?php checked( ! empty( $sidebar['visible'] ) ); ?>>
					<?php esc_html_e( 'Show the lower column', 'altijara' ); ?>
				</label>
			</td>
		</tr>
		<?php foreach ( $labels as $key => $label ) : ?>
			<tr>
				<th scope="row">
					<label for="<?php echo esc_attr( $id_prefix . '_pos_' . $key ); ?>"><?php echo esc_html( $label ); ?></label>
				</th>
				<td>
					<select name="<?php echo esc_attr( $name ); ?>[positions][<?php echo esc_attr( $key ); ?>]" id="<?php echo esc_attr( $id_prefix . '_pos_' . $key ); ?>">
						<?php for ( $n = 1; $n <= 3; $n++ ) : ?>
							<option value="<?php echo esc_attr( (string) $n ); ?>" <?php selected( (int) $positions[ $key ], $n ); ?>><?php echo esc_html( (string) $n ); ?></option>
						<?php endfor; ?>
					</select>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<p class="description"><?php esc_html_e( '1 is the top of the column. If two widgets share a number, the earlier default (advertisement, magazine slider, most read) stays ahead.', 'altijara' ); ?></p>
	<?php
}

/**
 * Lower column on the category archive template. Posts its own save flag.
 */
function altijara_render_archive_sidebar_fields() {
	$sidebar = function_exists( 'altijara_get_archive_sidebar' ) ? altijara_get_archive_sidebar() : array(
		'visible' => true,
		'order'   => array( 'ad', 'magazine', 'most_read' ),
	);
	?>
	<div class="altijara-admin-block" style="margin-top:28px;">
		<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Lower column', 'altijara' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Show or hide the column beside the news list, and set the order of its three widgets. Prefer the Lower sidebar builder above to configure the Advertisement widget (shortcode / image / link). A category can override this on its edit screen. This column is not added to /issues/.', 'altijara' ); ?></p>
		<?php altijara_render_archive_sidebar_inputs( $sidebar, 'altijara_archive_sidebar', 'altijara_archive_sidebar' ); ?>
		<input type="hidden" name="altijara_archive_sidebar_submitted" value="1">
	</div>
	<?php
}

/**
 * Full-hero category tag picker (search + chips). Posts its own save flag.
 */
function altijara_render_archive_full_hero_fields() {
	$full_hero_ids = function_exists( 'altijara_get_full_hero_category_ids' ) ? altijara_get_full_hero_category_ids() : array();
	$archive_cats  = altijara_admin_archive_categories();
	$choices       = array();
	$selected      = array();
	foreach ( $archive_cats as $cat ) {
		if ( ! $cat instanceof WP_Term ) {
			continue;
		}
		$tid  = (int) $cat->term_id;
		$row  = array(
			'id'   => $tid,
			'name' => (string) $cat->name,
			'slug' => (string) $cat->slug,
		);
		$choices[] = $row;
		if ( in_array( $tid, $full_hero_ids, true ) ) {
			$selected[] = $row;
		}
	}
	$choices_json = wp_json_encode(
		$choices,
		JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
	);
	if ( ! is_string( $choices_json ) ) {
		$choices_json = '[]';
	}
	?>
	<div class="altijara-admin-block" style="margin-top:28px;">
		<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Posts hero layout', 'altijara' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Search and add categories that show a single full-width hero (no recommended side column).', 'altijara' ); ?></p>
		<?php if ( empty( $choices ) ) : ?>
			<p><?php esc_html_e( 'No categories found.', 'altijara' ); ?></p>
		<?php else : ?>
			<div
				class="altijara-tag-picker"
				data-field-name="altijara_archive_full_hero[]"
				data-choices="<?php echo esc_attr( $choices_json ); ?>"
			>
				<script type="application/json" class="altijara-tag-picker__data"><?php echo $choices_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON_HEX_* encoded. ?></script>
				<div class="altijara-tag-picker__selected">
					<?php foreach ( $selected as $row ) : ?>
						<span class="altijara-tag-picker__chip" data-id="<?php echo esc_attr( (string) $row['id'] ); ?>">
							<span class="altijara-tag-picker__chip-label"><?php echo esc_html( $row['name'] ); ?></span>
							<button type="button" class="altijara-tag-picker__remove" aria-label="<?php esc_attr_e( 'Remove', 'altijara' ); ?>">&times;</button>
							<input type="hidden" name="altijara_archive_full_hero[]" value="<?php echo esc_attr( (string) $row['id'] ); ?>">
						</span>
					<?php endforeach; ?>
				</div>
				<label class="screen-reader-text" for="altijara-archive-full-hero-search"><?php esc_html_e( 'Search categories', 'altijara' ); ?></label>
				<input
					type="text"
					id="altijara-archive-full-hero-search"
					class="altijara-tag-picker__search regular-text"
					placeholder="<?php esc_attr_e( 'Search categories to add…', 'altijara' ); ?>"
					autocomplete="off"
					spellcheck="false"
				>
				<ul class="altijara-tag-picker__results" hidden></ul>
			</div>
		<?php endif; ?>
		<input type="hidden" name="altijara_archive_full_hero_submitted" value="1">
	</div>
	<?php
}

/**
 * Category archive module builder. Stored for the editor. The public category template does not read it.
 */
function altijara_render_category_archives_builder() {
	$pack = function_exists( 'altijara_get_category_archives_layout' )
		? altijara_get_category_archives_layout( true )
		: array(
			'sections' => array(),
			'order'    => array(),
		);
	if ( ! empty( $pack['sections'] ) && is_array( $pack['sections'] ) ) {
		foreach ( $pack['sections'] as $sid => $row ) {
			if ( is_array( $row ) && isset( $row['widget'] ) && 'cat_archive_lower' === $row['widget'] ) {
				unset( $pack['sections'][ $sid ] );
			}
		}
		$pack['order'] = array_values(
			array_filter(
				isset( $pack['order'] ) && is_array( $pack['order'] ) ? $pack['order'] : array(),
				static function ( $sid ) use ( $pack ) {
					return isset( $pack['sections'][ $sid ] );
				}
			)
		);
	}
	if ( function_exists( 'altijara_apply_archive_counts_to_layout_pack' ) ) {
		$pack = altijara_apply_archive_counts_to_layout_pack( $pack );
	}
	?>
	<div class="altijara-admin-block" style="margin-top:28px;">
		<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Category archive modules', 'altijara' ); ?></h2>
		<p class="description"><?php esc_html_e( 'These blocks are the category page body. Set recommended posts, featured rows, and news per page inside each module. The column beside the news list is the Lower sidebar section below.', 'altijara' ); ?></p>
		<label style="display:inline-block;margin:8px 0 14px;">
			<input type="checkbox" name="altijara_category_archive_layout_reset" value="1">
			<?php esc_html_e( 'Reset category modules to the editor preset', 'altijara' ); ?>
		</label>
		<input type="hidden" name="altijara_category_archive_layout_submitted" value="1">
		<?php
		if ( function_exists( 'altijara_render_layout_builder' ) ) {
			altijara_render_layout_builder(
				array(
					'builder_id'   => 'category_archives',
					'field_prefix' => 'altijara_category_archive_sections',
					'order_field'  => 'altijara_category_archive_order',
					'sections'     => isset( $pack['sections'] ) && is_array( $pack['sections'] ) ? $pack['sections'] : array(),
					'order'        => isset( $pack['order'] ) && is_array( $pack['order'] ) ? $pack['order'] : array(),
					'surface'      => 'category_archive',
					'lock_hero'    => false,
				)
			);
		}
		?>
	</div>
	<?php
}

/**
 * Lower sidebar builder. Sits under the category modules.
 */
function altijara_render_category_sidebar_builder() {
	$pack = function_exists( 'altijara_get_category_sidebar_layout' )
		? altijara_get_category_sidebar_layout( true )
		: array(
			'visible'  => true,
			'sections' => array(),
			'order'    => array(),
		);
	?>
	<div class="altijara-admin-block" style="margin-top:28px;">
		<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Lower sidebar', 'altijara' ); ?></h2>
		<p class="description"><?php esc_html_e( 'The column beside the latest news list. Show or hide the whole column, add widgets, and open each widget to set its options. Advertisement shortcode, image, and link are set on the Advertisement widget.', 'altijara' ); ?></p>
		<input type="hidden" name="altijara_archive_sidebar_layout_visible" value="0">
		<label style="display:block;margin:8px 0 14px;">
			<input type="checkbox" name="altijara_archive_sidebar_layout_visible" value="1" <?php checked( ! isset( $pack['visible'] ) || ! empty( $pack['visible'] ) ); ?>>
			<?php esc_html_e( 'Show the lower sidebar', 'altijara' ); ?>
		</label>
		<input type="hidden" name="altijara_archive_sidebar_layout_submitted" value="1">
		<?php
		if ( function_exists( 'altijara_render_layout_builder' ) ) {
			altijara_render_layout_builder(
				array(
					'builder_id'   => 'category_sidebar',
					'field_prefix' => 'altijara_archive_sidebar_sections',
					'order_field'  => 'altijara_archive_sidebar_order',
					'sections'     => isset( $pack['sections'] ) && is_array( $pack['sections'] ) ? $pack['sections'] : array(),
					'order'        => isset( $pack['order'] ) && is_array( $pack['order'] ) ? $pack['order'] : array(),
					'surface'      => 'category_sidebar',
					'lock_hero'    => false,
					'zone_title'   => __( 'Sidebar widgets', 'altijara' ),
					'zone_desc'    => __( 'Add, reorder, or remove widgets. Open one to show it and choose what it displays.', 'altijara' ),
				)
			);
		}
		?>
	</div>
	<?php
}

/**
 * Search results sidebar builder.
 */
function altijara_render_search_sidebar_builder() {
	$pack = function_exists( 'altijara_get_search_sidebar_layout' )
		? altijara_get_search_sidebar_layout()
		: array(
			'visible'  => true,
			'sections' => array(),
			'order'    => array(),
		);
	?>
	<div class="altijara-admin-block" style="margin-top:28px;">
		<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Search sidebar', 'altijara' ); ?></h2>
		<p class="description"><?php esc_html_e( 'The column beside search results. Show or hide the whole column, add widgets, and open each widget to set its options. Advertisement shortcode, image, and link are set on the Advertisement widget.', 'altijara' ); ?></p>
		<input type="hidden" name="altijara_search_sidebar_layout_visible" value="0">
		<label style="display:block;margin:8px 0 14px;">
			<input type="checkbox" name="altijara_search_sidebar_layout_visible" value="1" <?php checked( ! isset( $pack['visible'] ) || ! empty( $pack['visible'] ) ); ?>>
			<?php esc_html_e( 'Show the search sidebar', 'altijara' ); ?>
		</label>
		<input type="hidden" name="altijara_search_sidebar_layout_submitted" value="1">
		<?php
		if ( function_exists( 'altijara_render_layout_builder' ) ) {
			altijara_render_layout_builder(
				array(
					'builder_id'   => 'search_sidebar',
					'field_prefix' => 'altijara_search_sidebar_sections',
					'order_field'  => 'altijara_search_sidebar_order',
					'sections'     => isset( $pack['sections'] ) && is_array( $pack['sections'] ) ? $pack['sections'] : array(),
					'order'        => isset( $pack['order'] ) && is_array( $pack['order'] ) ? $pack['order'] : array(),
					'surface'      => 'search_sidebar',
					'lock_hero'    => false,
					'zone_title'   => __( 'Sidebar widgets', 'altijara' ),
					'zone_desc'    => __( 'Add, reorder, or remove widgets. Open one to show it and choose what it displays.', 'altijara' ),
				)
			);
		}
		?>
	</div>
	<?php
}

/**
 * Tag archive sidebar builder.
 */
function altijara_render_tag_sidebar_builder() {
	$pack = function_exists( 'altijara_get_tag_sidebar_layout' )
		? altijara_get_tag_sidebar_layout()
		: array(
			'visible'  => true,
			'sections' => array(),
			'order'    => array(),
		);
	?>
	<div class="altijara-admin-block" style="margin-top:28px;">
		<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Tag sidebar', 'altijara' ); ?></h2>
		<p class="description"><?php esc_html_e( 'The column beside the posts that use this tag. Show or hide the whole column, add widgets, and open each widget to set its options.', 'altijara' ); ?></p>
		<input type="hidden" name="altijara_tag_sidebar_layout_visible" value="0">
		<label style="display:block;margin:8px 0 14px;">
			<input type="checkbox" name="altijara_tag_sidebar_layout_visible" value="1" <?php checked( ! isset( $pack['visible'] ) || ! empty( $pack['visible'] ) ); ?>>
			<?php esc_html_e( 'Show the tag sidebar', 'altijara' ); ?>
		</label>
		<input type="hidden" name="altijara_tag_sidebar_layout_submitted" value="1">
		<?php
		if ( function_exists( 'altijara_render_layout_builder' ) ) {
			altijara_render_layout_builder(
				array(
					'builder_id'   => 'tag_sidebar',
					'field_prefix' => 'altijara_tag_sidebar_sections',
					'order_field'  => 'altijara_tag_sidebar_order',
					'sections'     => isset( $pack['sections'] ) && is_array( $pack['sections'] ) ? $pack['sections'] : array(),
					'order'        => isset( $pack['order'] ) && is_array( $pack['order'] ) ? $pack['order'] : array(),
					'surface'      => 'tag_sidebar',
					'lock_hero'    => false,
					'zone_title'   => __( 'Sidebar widgets', 'altijara' ),
					'zone_desc'    => __( 'Add, reorder, or remove widgets. Open one to show it and choose what it displays.', 'altijara' ),
				)
			);
		}
		?>
	</div>
	<?php
}

/**
 * Issues module builder. Each screen gets its own builder id; field names stay shared.
 *
 * @param string $builder_id DOM id prefix.
 */
function altijara_render_issues_archive_builder( $builder_id ) {
	$builder_id = sanitize_key( $builder_id );
	if ( ! $builder_id ) {
		$builder_id = 'issues_archive';
	}
	?>
	<div class="altijara-admin-block" style="margin-top:28px;">
		<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Issues archive modules', 'altijara' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Layout builder for /issues/ (and magazine category archives). Add lead + carousel bands or other widgets. Reset restores the theme preset.', 'altijara' ); ?></p>
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
					'builder_id'   => $builder_id,
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
	<?php
}
