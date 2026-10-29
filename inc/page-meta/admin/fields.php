<?php
/**
 * Admin field UI helpers for page meta.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Open accordion section.
 *
 * @param string $id     Section id.
 * @param string $title  Title.
 * @param bool   $open   Open by default.
 */
function altijara_page_meta_section_open( $id, $title, $open = false ) {
	$cls = $open ? ' altijara-pm-section is-open' : ' altijara-pm-section';
	echo '<div id="altijara-pm-sec-' . esc_attr( $id ) . '" class="' . esc_attr( trim( $cls ) ) . '" data-section="' . esc_attr( $id ) . '">';
	echo '<button type="button" class="altijara-pm-section__toggle" aria-expanded="' . ( $open ? 'true' : 'false' ) . '">';
	echo '<span class="altijara-pm-section__title">' . esc_html( $title ) . '</span>';
	echo '<span class="altijara-pm-section__chevron dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>';
	echo '</button>';
	echo '<div class="altijara-pm-section__body">';
}

/**
 * Close accordion section.
 */
function altijara_page_meta_section_close() {
	echo '</div></div>';
}

/**
 * Open two-column field grid.
 */
function altijara_page_meta_grid_open() {
	echo '<div class="altijara-pm-grid">';
}

/**
 * Close field grid.
 */
function altijara_page_meta_grid_close() {
	echo '</div>';
}

/**
 * Text / textarea field.
 *
 * @param string $key        Key.
 * @param string $label      Label.
 * @param string $value      Value.
 * @param bool   $multiline  Textarea.
 * @param string $type       Input type when not multiline.
 * @param string $hint       Optional description.
 */
function altijara_page_meta_field_text( $key, $label, $value, $multiline = false, $type = 'text', $hint = '' ) {
	$name = 'altijara_page[' . $key . ']';
	$type = in_array( $type, array( 'text', 'email', 'url', 'tel' ), true ) ? $type : 'text';
	echo '<div class="altijara-pm-field">';
	echo '<label><strong>' . esc_html( $label ) . '</strong></label>';
	if ( $multiline ) {
		echo '<textarea class="widefat" rows="3" name="' . esc_attr( $name ) . '">' . esc_textarea( $value ) . '</textarea>';
	} else {
		echo '<input class="widefat" type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
	}
	if ( $hint ) {
		echo '<p class="description">' . esc_html( $hint ) . '</p>';
	}
	echo '</div>';
}

/**
 * Media / file URL field with library picker.
 *
 * @param string $key   Key.
 * @param string $label Label.
 * @param string $value URL.
 * @param string $hint  Hint.
 */
function altijara_page_meta_media_field( $key, $label, $value, $hint = '' ) {
	$name = 'altijara_page[' . $key . ']';
	?>
	<div class="altijara-pm-field altijara-pm-media-field">
		<label><strong><?php echo esc_html( $label ); ?></strong></label>
		<div class="altijara-pm-media">
			<input type="url" class="widefat altijara-pm-media-url" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="https://…">
			<div class="altijara-pm-media-buttons">
				<button type="button" class="button altijara-pm-media-upload"><?php esc_html_e( 'Choose from library', 'altijara' ); ?></button>
				<button type="button" class="button altijara-pm-media-clear"><?php esc_html_e( 'Clear', 'altijara' ); ?></button>
			</div>
		</div>
		<?php if ( $hint ) : ?>
			<p class="description"><?php echo esc_html( $hint ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render one repeater column control.
 *
 * @param string               $name          Input name.
 * @param string               $col           Column key.
 * @param string               $col_label     Label.
 * @param string               $val           Value.
 * @param array<int, string>   $textarea_cols Textarea keys.
 * @param array<string, array> $select_cols   Select options map.
 */
function altijara_page_meta_repeater_control( $name, $col, $col_label, $val, $textarea_cols, $select_cols ) {
	echo '<div class="altijara-pm-field">';
	echo '<label>' . esc_html( $col_label ) . '</label>';
	if ( isset( $select_cols[ $col ] ) && is_array( $select_cols[ $col ] ) ) {
		echo '<select class="widefat" name="' . esc_attr( $name ) . '">';
		foreach ( $select_cols[ $col ] as $opt_val => $opt_label ) {
			echo '<option value="' . esc_attr( $opt_val ) . '"' . selected( $val, (string) $opt_val, false ) . '>' . esc_html( $opt_label ) . '</option>';
		}
		echo '</select>';
	} elseif ( in_array( $col, $textarea_cols, true ) ) {
		echo '<textarea class="widefat" rows="3" name="' . esc_attr( $name ) . '">' . esc_textarea( $val ) . '</textarea>';
	} else {
		echo '<input class="widefat" type="text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '">';
	}
	echo '</div>';
}

/**
 * Repeater field UI.
 *
 * @param string                             $key           Meta key.
 * @param string                             $label         Section label.
 * @param array<int, mixed>                  $rows          Rows.
 * @param array<string, string>              $columns       Column key => label.
 * @param array<int, string>                 $textarea_cols Keys that use textarea.
 * @param array<string, array<string, string>> $select_cols Column key => options.
 */
function altijara_page_meta_repeater( $key, $label, $rows, $columns, $textarea_cols = array(), $select_cols = array() ) {
	if ( ! is_array( $rows ) ) {
		$rows = array();
	}
	$rows = array_values( $rows );
	if ( empty( $rows ) ) {
		$empty = array();
		foreach ( array_keys( $columns ) as $col ) {
			$empty[ $col ] = '';
		}
		$rows = array( $empty );
	}
	?>
	<div class="altijara-pm-repeater" data-repeater="<?php echo esc_attr( $key ); ?>">
		<div class="altijara-pm-repeater__label"><?php echo esc_html( $label ); ?></div>
		<div class="altijara-pm-repeater-rows">
			<?php foreach ( $rows as $i => $row ) : ?>
				<div class="altijara-pm-row" data-index="<?php echo (int) $i; ?>">
					<div class="altijara-pm-row-head">
						<strong class="altijara-pm-row-title"><?php echo esc_html( sprintf( /* translators: %d row number */ __( 'Item %d', 'altijara' ), (int) $i + 1 ) ); ?></strong>
						<div class="altijara-pm-row-actions">
							<button type="button" class="button-link altijara-pm-move-up" title="<?php esc_attr_e( 'Move up', 'altijara' ); ?>">↑</button>
							<button type="button" class="button-link altijara-pm-move-down" title="<?php esc_attr_e( 'Move down', 'altijara' ); ?>">↓</button>
							<button type="button" class="button-link-delete altijara-pm-remove"><?php esc_html_e( 'Remove', 'altijara' ); ?></button>
						</div>
					</div>
					<div class="altijara-pm-row-fields">
						<?php
						foreach ( $columns as $col => $col_label ) :
							$val  = isset( $row[ $col ] ) ? (string) $row[ $col ] : '';
							$name = 'altijara_page[' . $key . '][' . (int) $i . '][' . $col . ']';
							altijara_page_meta_repeater_control( $name, $col, $col_label, $val, $textarea_cols, $select_cols );
						endforeach;
						?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button button-primary altijara-pm-add"><?php esc_html_e( 'Add item', 'altijara' ); ?></button>
		<script type="text/template" class="altijara-pm-row-tpl">
			<div class="altijara-pm-row" data-index="__i__">
				<div class="altijara-pm-row-head">
					<strong class="altijara-pm-row-title"><?php echo esc_html__( 'Item', 'altijara' ); ?> __n__</strong>
					<div class="altijara-pm-row-actions">
						<button type="button" class="button-link altijara-pm-move-up" title="<?php esc_attr_e( 'Move up', 'altijara' ); ?>">↑</button>
						<button type="button" class="button-link altijara-pm-move-down" title="<?php esc_attr_e( 'Move down', 'altijara' ); ?>">↓</button>
						<button type="button" class="button-link-delete altijara-pm-remove"><?php esc_html_e( 'Remove', 'altijara' ); ?></button>
					</div>
				</div>
				<div class="altijara-pm-row-fields">
					<?php foreach ( $columns as $col => $col_label ) : ?>
						<?php
						$name = 'altijara_page[' . $key . '][__i__][' . $col . ']';
						altijara_page_meta_repeater_control( $name, $col, $col_label, '', $textarea_cols, $select_cols );
						?>
					<?php endforeach; ?>
				</div>
			</div>
		</script>
	</div>
	<?php
}