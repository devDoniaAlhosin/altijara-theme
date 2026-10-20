<?php
/**
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
