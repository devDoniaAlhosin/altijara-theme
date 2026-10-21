<?php
/**
 * Category archive layout builder (term meta).
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ALTIJARA_TERM_LAYOUT_KEY', '_altijara_archive_layout' );

/**
 * Get category layout pack (saved or preset).
 *
 * @param int  $term_id Term ID.
 * @param bool $fallback_preset When empty, return preset.
 * @return array{sections:array,order:string[]}
 */
function altijara_get_category_layout( $term_id, $fallback_preset = true ) {
	$term_id = absint( $term_id );
	$raw     = $term_id ? get_term_meta( $term_id, ALTIJARA_TERM_LAYOUT_KEY, true ) : null;
	if ( is_array( $raw ) && ! empty( $raw['sections'] ) ) {
		return altijara_normalize_layout_pack( $raw );
	}
	if ( $fallback_preset ) {
		return altijara_layout_preset_category( $term_id );
	}
	return array(
		'sections' => array(),
		'order'    => array(),
	);
}

/**
 * Whether category has a custom saved layout.
 *
 * @param int $term_id Term ID.
 */
function altijara_category_has_custom_layout( $term_id ) {
	$raw = get_term_meta( absint( $term_id ), ALTIJARA_TERM_LAYOUT_KEY, true );
	return is_array( $raw ) && ! empty( $raw['sections'] );
}

/**
 * Save category layout.
 *
 * @param int                  $term_id Term ID.
 * @param array<string, mixed> $pack    Layout pack.
 */
function altijara_save_category_layout( $term_id, $pack ) {
	$term_id = absint( $term_id );
	if ( ! $term_id ) {
		return;
	}
	$pack = altijara_normalize_layout_pack( $pack );
	update_term_meta( $term_id, ALTIJARA_TERM_LAYOUT_KEY, $pack );
}

/**
 * Category edit form: layout builder.
 *
 * @param WP_Term $term Term.
 */
function altijara_category_layout_edit_form( $term ) {
	if ( ! ( $term instanceof WP_Term ) || 'category' !== $term->taxonomy ) {
		return;
	}
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	$pack = altijara_get_category_layout( (int) $term->term_id, true );
	wp_nonce_field( 'altijara_save_category_layout', 'altijara_category_layout_nonce' );
	?>
	<tr class="form-field altijara-term-layout-row">
		<th scope="row"><label><?php esc_html_e( 'Archive layout (modules)', 'altijara' ); ?></label></th>
		<td>
			<p class="description" style="margin-top:0;">
				<?php esc_html_e( 'JNews-style modules for this category. Title, filters, and pagination stay fixed. Leave as default or add/reorder widgets. Check “Reset” to restore the theme preset.', 'altijara' ); ?>
			</p>
			<label style="display:inline-block;margin-bottom:12px;">
				<input type="checkbox" name="altijara_category_layout_reset" value="1">
				<?php esc_html_e( 'Reset layout to theme default', 'altijara' ); ?>
			</label>
			<?php
			altijara_render_layout_builder(
				array(
					'builder_id'   => 'cat_layout_' . (int) $term->term_id,
					'field_prefix' => 'altijara_cat_layout_sections',
					'order_field'  => 'altijara_cat_layout_order',
					'sections'     => $pack['sections'],
					'order'        => $pack['order'],
					'surface'      => 'category',
					'lock_hero'    => false,
				)
			);
			?>
		</td>
	</tr>
	<?php
}
add_action( 'category_edit_form_fields', 'altijara_category_layout_edit_form', 20 );

/**
 * Save category layout from term edit.
 *
 * @param int $term_id Term ID.
 */
function altijara_category_layout_save( $term_id ) {
	$term_id = absint( $term_id );
	if ( ! $term_id || ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	if ( empty( $_POST['altijara_category_layout_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['altijara_category_layout_nonce'] ) ), 'altijara_save_category_layout' ) ) {
		return;
	}
	if ( ! empty( $_POST['altijara_category_layout_reset'] ) ) {
		delete_term_meta( $term_id, ALTIJARA_TERM_LAYOUT_KEY );
		return;
	}
	if ( empty( $_POST['altijara_cat_layout_sections'] ) || ! is_array( $_POST['altijara_cat_layout_sections'] ) ) {
		return;
	}
	$sections = wp_unslash( $_POST['altijara_cat_layout_sections'] ); // phpcs:ignore
	$order    = isset( $_POST['altijara_cat_layout_order'] ) && is_array( $_POST['altijara_cat_layout_order'] )
		? wp_unslash( $_POST['altijara_cat_layout_order'] ) // phpcs:ignore
		: array();
	$pack = altijara_sanitize_layout( $sections, $order, array( 'lock_hero' => false ) );
	altijara_save_category_layout( $term_id, $pack );
}
add_action( 'edited_category', 'altijara_category_layout_save', 20 );

/**
 * Enqueue builder assets on category edit screen.
 *
 * @param string $hook Hook.
 */
function altijara_category_layout_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'term.php', 'edit-tags.php' ), true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'category' !== $screen->taxonomy ) {
		return;
	}
	$css = ALTIJARA_THEME_DIR . '/assets/css/admin-sections.css';
	$js  = ALTIJARA_THEME_DIR . '/assets/js/admin/section-manager.js';
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style(
		'altijara-admin-sections',
		altijara_asset( 'assets/css/admin-sections.css' ),
		array( 'dashicons' ),
		file_exists( $css ) ? (string) filemtime( $css ) : ALTIJARA_THEME_VERSION
	);
	wp_enqueue_script(
		'altijara-admin-sections',
		altijara_asset( 'assets/js/admin/section-manager.js' ),
		array(),
		file_exists( $js ) ? (string) filemtime( $js ) : ALTIJARA_THEME_VERSION,
		true
	);
	wp_localize_script(
		'altijara-admin-sections',
		'AltijaraAdmin',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'altijara_admin_search' ),
			'i18n'    => array(
				'noResults'     => __( 'No results', 'altijara' ),
				'removeSection' => __( 'Remove this section?', 'altijara' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'altijara_category_layout_admin_assets', 30 );
