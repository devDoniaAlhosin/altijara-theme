<?php
/**
 * Design tokens — fonts & colors (admin-controllable CSS variables).
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default design tokens matching assets/css/style.css :root.
 *
 * @return array<string, mixed>
 */
function altijara_default_design() {
	return array(
		'font_main'         => 'ibm_plex',
		'font_serif'        => 'playfair',
		'font_main_custom'  => '',
		'font_serif_custom' => '',
		'bg_salmon'         => '#FFF1E5',
		'bg_salmon_light'   => '#F2DFCE',
		'bg_card'           => '#FFF7EF',
		'ink_black'         => '#262A33',
		'ink_secondary'     => '#3b424e',
		'ink_muted'         => '#6e7787',
		'accent_brand'      => '#1897B8',
		'accent_hover'      => '#12748e',
		'accent_amber'      => '#d97706',
		'footer_bg'         => '',
		'footer_text'       => '',
	);
}

/**
 * Preset font stacks (self-hosted where applicable).
 *
 * @return array<string, array{label:string,stack:string}>
 */
function altijara_font_main_presets() {
	return array(
		'ibm_plex' => array(
			'label' => 'IBM Plex Sans Arabic (الافتراضي — محلي)',
			'stack' => '"IBM Plex Sans Arabic", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
		),
		'system'   => array(
			'label' => 'خطوط النظام',
			'stack' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
		),
		'tahoma'   => array(
			'label' => 'Tahoma / Arial',
			'stack' => 'Tahoma, Arial, "Segoe UI", sans-serif',
		),
		'custom'   => array(
			'label' => 'مخصص…',
			'stack' => '',
		),
	);
}

/**
 * @return array<string, array{label:string,stack:string}>
 */
function altijara_font_serif_presets() {
	return array(
		'playfair' => array(
			'label' => 'Playfair Display (الافتراضي — محلي)',
			'stack' => '"Playfair Display", Georgia, serif',
		),
		'georgia'  => array(
			'label' => 'Georgia',
			'stack' => 'Georgia, "Times New Roman", Times, serif',
		),
		'custom'   => array(
			'label' => 'مخصص…',
			'stack' => '',
		),
	);
}

/**
 * Resolve CSS font-family value for a preset key.
 *
 * @param string $preset Preset id.
 * @param string $custom Custom stack when preset is custom.
 * @param string $kind   main|serif.
 * @return string
 */
function altijara_resolve_font_stack( $preset, $custom, $kind = 'main' ) {
	$presets = ( 'serif' === $kind ) ? altijara_font_serif_presets() : altijara_font_main_presets();
	$preset  = sanitize_key( $preset );
	if ( 'custom' === $preset && is_string( $custom ) && '' !== trim( $custom ) ) {
		return altijara_sanitize_font_stack( $custom );
	}
	if ( isset( $presets[ $preset ] ) && '' !== $presets[ $preset ]['stack'] ) {
		return $presets[ $preset ]['stack'];
	}
	$fallback = ( 'serif' === $kind ) ? 'playfair' : 'ibm_plex';
	return $presets[ $fallback ]['stack'];
}

/**
 * Allow safe CSS font-family lists only.
 *
 * @param string $stack Raw.
 * @return string
 */
function altijara_sanitize_font_stack( $stack ) {
	$stack   = wp_strip_all_tags( (string) $stack );
	$cleaned = preg_replace( '/[^a-zA-Z0-9\s\-_,"\']+/', '', $stack );
	return is_string( $cleaned ) ? trim( $cleaned ) : '';
}

/**
 * Sanitize a hex color or return default.
 *
 * @param mixed  $value   Input.
 * @param string $default Default hex.
 * @return string
 */
function altijara_sanitize_hex( $value, $default = '' ) {
	if ( ! is_string( $value ) || '' === trim( $value ) ) {
		return $default;
	}
	$hex = sanitize_hex_color( $value );
	return $hex ? $hex : $default;
}

/**
 * Sanitize design settings from admin.
 *
 * @param array<string, mixed> $raw Raw POST.
 * @return array<string, mixed>
 */
function altijara_sanitize_design_input( $raw ) {
	$d          = altijara_default_design();
	$main_keys  = array_keys( altijara_font_main_presets() );
	$serif_keys = array_keys( altijara_font_serif_presets() );

	$font_main = isset( $raw['font_main'] ) ? sanitize_key( $raw['font_main'] ) : $d['font_main'];
	if ( ! in_array( $font_main, $main_keys, true ) ) {
		$font_main = $d['font_main'];
	}
	$font_serif = isset( $raw['font_serif'] ) ? sanitize_key( $raw['font_serif'] ) : $d['font_serif'];
	if ( ! in_array( $font_serif, $serif_keys, true ) ) {
		$font_serif = $d['font_serif'];
	}

	return array(
		'font_main'         => $font_main,
		'font_serif'        => $font_serif,
		'font_main_custom'  => isset( $raw['font_main_custom'] ) ? altijara_sanitize_font_stack( $raw['font_main_custom'] ) : '',
		'font_serif_custom' => isset( $raw['font_serif_custom'] ) ? altijara_sanitize_font_stack( $raw['font_serif_custom'] ) : '',
		'bg_salmon'         => altijara_sanitize_hex( $raw['bg_salmon'] ?? '', $d['bg_salmon'] ),
		'bg_salmon_light'   => altijara_sanitize_hex( $raw['bg_salmon_light'] ?? '', $d['bg_salmon_light'] ),
		'bg_card'           => altijara_sanitize_hex( $raw['bg_card'] ?? '', $d['bg_card'] ),
		'ink_black'         => altijara_sanitize_hex( $raw['ink_black'] ?? '', $d['ink_black'] ),
		'ink_secondary'     => altijara_sanitize_hex( $raw['ink_secondary'] ?? '', $d['ink_secondary'] ),
		'ink_muted'         => altijara_sanitize_hex( $raw['ink_muted'] ?? '', $d['ink_muted'] ),
		'accent_brand'      => altijara_sanitize_hex( $raw['accent_brand'] ?? '', $d['accent_brand'] ),
		'accent_hover'      => altijara_sanitize_hex( $raw['accent_hover'] ?? '', $d['accent_hover'] ),
		'accent_amber'      => altijara_sanitize_hex( $raw['accent_amber'] ?? '', $d['accent_amber'] ),
		'footer_bg'         => altijara_sanitize_hex( $raw['footer_bg'] ?? '', '' ),
		'footer_text'       => altijara_sanitize_hex( $raw['footer_text'] ?? '', '' ),
	);
}

/**
 * Build :root CSS overrides from design settings.
 *
 * @return string
 */
function altijara_get_design_css() {
	$design = altijara_get_option( 'design', array() );
	if ( ! is_array( $design ) ) {
		$design = array();
	}
	$design = array_merge( altijara_default_design(), $design );

	$main  = altijara_resolve_font_stack( $design['font_main'], $design['font_main_custom'] ?? '', 'main' );
	$serif = altijara_resolve_font_stack( $design['font_serif'], $design['font_serif_custom'] ?? '', 'serif' );

	$brand = $design['accent_brand'];
	$hover = $design['accent_hover'];

	$lines = array(
		'--font-main: ' . $main,
		'--font-serif: ' . $serif,
		'--bg-salmon: ' . $design['bg_salmon'],
		'--bg-salmon-light: ' . $design['bg_salmon_light'],
		'--bg-card: ' . $design['bg_card'],
		'--ink-black: ' . $design['ink_black'],
		'--ink-secondary: ' . $design['ink_secondary'],
		'--ink-muted: ' . $design['ink_muted'],
		'--accent-brand: ' . $brand,
		'--accent-red: ' . $brand,
		'--accent-blue: ' . $brand,
		'--color-brand: ' . $brand,
		'--accent-red-hover: ' . $hover,
		'--accent-black: ' . $design['ink_black'],
		'--accent-amber: ' . $design['accent_amber'],
	);

	$css = ':root{' . implode( ';', $lines ) . ';}';

	if ( ! empty( $design['footer_bg'] ) || ! empty( $design['footer_text'] ) ) {
		$css .= '.editorial-dark-footer{';
		if ( ! empty( $design['footer_bg'] ) ) {
			$css .= 'background:' . $design['footer_bg'] . ' !important;';
		}
		if ( ! empty( $design['footer_text'] ) ) {
			$css .= 'color:' . $design['footer_text'] . ' !important;';
		}
		$css .= '}';
	}

	return $css;
}

/**
 * Print design CSS after main stylesheet.
 */
function altijara_enqueue_design_css() {
	if ( ! wp_style_is( 'altijara-main', 'enqueued' ) ) {
		return;
	}
	wp_add_inline_style( 'altijara-main', altijara_get_design_css() );
}
add_action( 'wp_enqueue_scripts', 'altijara_enqueue_design_css', 20 );
