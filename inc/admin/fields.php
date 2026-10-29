<?php
/**
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Open a settings content block.
 *
 * @param string $title Block title.
 * @param string $hint  Optional short description.
 * @param string $extra_class Optional extra class.
 */
function altijara_admin_block_open( $title, $hint = '', $extra_class = '' ) {
	$class = 'altijara-admin-block';
	if ( $extra_class ) {
		$class .= ' ' . sanitize_html_class( $extra_class );
	}
	echo '<div class="' . esc_attr( $class ) . '">';
	echo '<div class="altijara-admin-block__head">';
	echo '<h2 class="altijara-admin-block__title">' . esc_html( $title ) . '</h2>';
	if ( $hint ) {
		echo '<p class="altijara-admin-block__hint">' . esc_html( $hint ) . '</p>';
	}
	echo '</div>';
	echo '<div class="altijara-admin-block__body">';
}

/**
 * Close a settings content block.
 */
function altijara_admin_block_close() {
	echo '</div></div>';
}

/**
 * Open a field group inside a block or section card.
 *
 * @param string $title    Group title.
 * @param string $hint     Optional hint.
 * @param bool   $full_row Span full width in 2-column section layouts.
 */
function altijara_admin_field_group_open( $title, $hint = '', $full_row = false ) {
	$class = 'altijara-field-group' . ( $full_row ? ' altijara-field-group--full' : '' );
	echo '<div class="' . esc_attr( $class ) . '">';
	echo '<div class="altijara-field-group__head">';
	echo '<h3 class="altijara-field-group__title">' . esc_html( $title ) . '</h3>';
	if ( $hint ) {
		echo '<p class="altijara-field-group__hint">' . esc_html( $hint ) . '</p>';
	}
	echo '</div>';
	echo '<div class="altijara-field-group__body">';
}

/**
 * Close a field group.
 */
function altijara_admin_field_group_close() {
	echo '</div></div>';
}

/**
 * Panel sticky actions bar open.
 */
function altijara_admin_actions_open() {
	echo '<div class="altijara-panel-actions">';
}

/**
 * Panel sticky actions bar close.
 */
function altijara_admin_actions_close() {
	echo '</div>';
}

/**
 * Media field helper with live preview.
 *
 * @param string $name  Input name.
 * @param string $value URL value.
 * @param string $label Label.
 * @param string $hint  Optional description.
 */
function altijara_admin_media_field( $name, $value, $label, $hint = '' ) {
	?>
	<tr>
		<th><label><?php echo esc_html( $label ); ?></label></th>
		<td>
			<div class="altijara-media-field">
				<div class="altijara-media-preview <?php echo $value ? 'has-image' : ''; ?>">
					<?php if ( $value ) : ?>
						<img src="<?php echo esc_url( $value ); ?>" alt="">
					<?php else : ?>
						<span class="altijara-media-preview__empty"><?php esc_html_e( 'No image selected', 'altijara' ); ?></span>
					<?php endif; ?>
				</div>
				<div class="altijara-media-controls">
					<input type="url" class="regular-text altijara-media-url" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="https://…">
					<div class="altijara-media-buttons">
						<button type="button" class="button altijara-media-upload"><?php esc_html_e( 'Choose from library', 'altijara' ); ?></button>
						<button type="button" class="button altijara-media-clear"><?php esc_html_e( 'Clear', 'altijara' ); ?></button>
					</div>
					<?php if ( $hint ) : ?>
						<p class="description"><?php echo esc_html( $hint ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</td>
	</tr>
	<?php
}

/**
 * Color field with palette checker (text + native color input + swatch).
 *
 * @param string $name        Input name.
 * @param string $value       Hex value.
 * @param string $label       Label.
 * @param string $placeholder Placeholder / fallback hex.
 * @param string $input_id    Optional input id.
 */
function altijara_admin_color_field( $name, $value, $label, $placeholder = '#1897B8', $input_id = '' ) {
	$id    = $input_id ? $input_id : 'altijara-color-' . sanitize_key( preg_replace( '/[^a-z0-9]+/i', '-', $name ) );
	$hex   = ( is_string( $value ) && preg_match( '/^#[0-9A-Fa-f]{6}$/', $value ) ) ? $value : '';
	$pick  = $hex ? $hex : ( preg_match( '/^#[0-9A-Fa-f]{6}$/', $placeholder ) ? $placeholder : '#1897B8' );
	?>
	<tr>
		<th><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label></th>
		<td>
			<div class="altijara-color-field">
				<span class="altijara-color-swatch" style="background:<?php echo esc_attr( $hex ? $hex : 'transparent' ); ?>" data-swatch-for="<?php echo esc_attr( $id ); ?>"></span>
				<input type="text" class="altijara-color-text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>">
				<input type="color" class="altijara-color-picker" value="<?php echo esc_attr( $pick ); ?>" data-target="<?php echo esc_attr( $id ); ?>" title="<?php esc_attr_e( 'Open color palette', 'altijara' ); ?>">
			</div>
		</td>
	</tr>
	<?php
}

/**
 * Compact color card for palette grids (no table row).
 *
 * @param string $name        Input name.
 * @param string $value       Hex value.
 * @param string $label       Label.
 * @param string $placeholder Placeholder / fallback hex.
 * @param string $input_id    Optional input id.
 */
function altijara_admin_color_card( $name, $value, $label, $placeholder = '#1897B8', $input_id = '' ) {
	$id    = $input_id ? $input_id : 'altijara-color-' . sanitize_key( preg_replace( '/[^a-z0-9]+/i', '-', $name ) );
	$hex   = ( is_string( $value ) && preg_match( '/^#[0-9A-Fa-f]{6}$/', $value ) ) ? $value : '';
	$pick  = $hex ? $hex : ( preg_match( '/^#[0-9A-Fa-f]{6}$/', $placeholder ) ? $placeholder : '#1897B8' );
	?>
	<label class="altijara-color-card" for="<?php echo esc_attr( $id ); ?>">
		<span class="altijara-color-card__label"><?php echo esc_html( $label ); ?></span>
		<span class="altijara-color-field">
			<span class="altijara-color-swatch" style="background:<?php echo esc_attr( $hex ? $hex : 'transparent' ); ?>" data-swatch-for="<?php echo esc_attr( $id ); ?>"></span>
			<input type="text" class="altijara-color-text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>">
			<input type="color" class="altijara-color-picker" value="<?php echo esc_attr( $pick ); ?>" data-target="<?php echo esc_attr( $id ); ?>" title="<?php esc_attr_e( 'Open color palette', 'altijara' ); ?>">
		</span>
	</label>
	<?php
}

/**
 * Admin brand logo URL (header logo → footer logo → default CDN asset).
 *
 * @return string
 */
function altijara_admin_brand_logo_url() {
	$header = altijara_get_option( 'header', array() );
	if ( ! empty( $header['logo_url'] ) ) {
		return (string) $header['logo_url'];
	}
	$footer = altijara_get_option( 'footer', array() );
	if ( ! empty( $footer['logo_url'] ) ) {
		return (string) $footer['logo_url'];
	}
	$defs = altijara_default_options();
	return ! empty( $defs['header']['logo_url'] ) ? (string) $defs['header']['logo_url'] : '';
}
