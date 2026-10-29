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
 * Default design tokens matching assets/css/base.css :root.
 *
 * @return array<string, mixed>
 */
function altijara_default_design() {
	return array(
		'font_main'         => 'ibm_plex',
		'font_serif'        => 'playfair',
		'font_main_custom'  => '',
		'font_serif_custom' => '',
		'custom_fonts'      => array(),
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
	$presets = array(
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
	);
	foreach ( altijara_uploaded_font_presets( 'sans-serif' ) as $key => $preset ) {
		$presets[ $key ] = $preset;
	}
	$presets['custom'] = array(
		'label' => 'مخصص (CSS)…',
		'stack' => '',
	);
	return $presets;
}

/**
 * @return array<string, array{label:string,stack:string}>
 */
function altijara_font_serif_presets() {
	$presets = array(
		'playfair' => array(
			'label' => 'Playfair Display (الافتراضي — محلي)',
			'stack' => '"Playfair Display", Georgia, serif',
		),
		'georgia'  => array(
			'label' => 'Georgia',
			'stack' => 'Georgia, "Times New Roman", Times, serif',
		),
	);
	foreach ( altijara_uploaded_font_presets( 'serif' ) as $key => $preset ) {
		$presets[ $key ] = $preset;
	}
	$presets['custom'] = array(
		'label' => 'مخصص (CSS)…',
		'stack' => '',
	);
	return $presets;
}

/**
 * Stable slug for an uploaded font family name.
 *
 * @param string $family Family name.
 * @return string
 */
function altijara_custom_font_slug( $family ) {
	$family = trim( (string) $family );
	$slug   = sanitize_title( $family );
	if ( '' === $slug ) {
		$slug = 'font-' . substr( md5( $family ), 0, 10 );
	}
	return $slug;
}

/**
 * Sanitize a display font-family name for @font-face.
 *
 * @param string $name Raw name.
 * @return string
 */
function altijara_sanitize_font_family_name( $name ) {
	$name = wp_strip_all_tags( (string) $name );
	$name = preg_replace( '/\s+/u', ' ', $name );
	$name = is_string( $name ) ? trim( $name ) : '';
	if ( function_exists( 'mb_substr' ) ) {
		$name = mb_substr( $name, 0, 80 );
	} else {
		$name = substr( $name, 0, 80 );
	}
	// Letters (any script), numbers, spaces, hyphen, underscore.
	$clean = preg_replace( '/[^\p{L}\p{N}\s\-_]/u', '', $name );
	return is_string( $clean ) ? trim( $clean ) : '';
}

/**
 * Whether a URL looks like an allowed font file.
 *
 * @param string $url File URL.
 * @return bool
 */
function altijara_is_font_file_url( $url ) {
	$path = wp_parse_url( (string) $url, PHP_URL_PATH );
	if ( ! is_string( $path ) || '' === $path ) {
		return false;
	}
	return (bool) preg_match( '/\.(woff2?|ttf|otf)$/i', $path );
}

/**
 * CSS format() hint from font URL.
 *
 * @param string $url Font URL.
 * @return string
 */
function altijara_font_format_from_url( $url ) {
	$path = strtolower( (string) wp_parse_url( (string) $url, PHP_URL_PATH ) );
	if ( substr( $path, -5 ) === '.woff2' ) {
		return 'woff2';
	}
	if ( substr( $path, -4 ) === '.woff' ) {
		return 'woff';
	}
	if ( substr( $path, -4 ) === '.ttf' ) {
		return 'truetype';
	}
	if ( substr( $path, -4 ) === '.otf' ) {
		return 'opentype';
	}
	return 'woff2';
}

/**
 * Sanitize uploaded custom font face rows from admin.
 *
 * @param mixed $raw Raw rows.
 * @return array<int, array{family:string,slug:string,weight:string,style:string,url:string}>
 */
function altijara_sanitize_custom_fonts( $raw ) {
	if ( ! is_array( $raw ) ) {
		return array();
	}
	$allowed_weights = array( '100', '200', '300', '400', '500', '600', '700', '800', '900' );
	$out             = array();
	$used_slugs      = array();
	foreach ( $raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$family = altijara_sanitize_font_family_name( $row['family'] ?? '' );
		$url    = isset( $row['url'] ) ? esc_url_raw( (string) $row['url'] ) : '';
		if ( '' === $family || '' === $url || ! altijara_is_font_file_url( $url ) ) {
			continue;
		}
		$slug = isset( $row['slug'] ) ? sanitize_key( (string) $row['slug'] ) : '';
		if ( '' === $slug ) {
			$slug = altijara_custom_font_slug( $family );
		}
		if ( isset( $used_slugs[ $slug ] ) && $used_slugs[ $slug ] !== $family ) {
			$slug = altijara_custom_font_slug( $family . '-' . substr( md5( $url ), 0, 6 ) );
		}
		$used_slugs[ $slug ] = $family;

		$weight = isset( $row['weight'] ) ? preg_replace( '/\D+/', '', (string) $row['weight'] ) : '400';
		$weight = is_string( $weight ) ? $weight : '400';
		if ( ! in_array( $weight, $allowed_weights, true ) ) {
			$weight = '400';
		}
		$style = isset( $row['style'] ) ? sanitize_key( (string) $row['style'] ) : 'normal';
		if ( ! in_array( $style, array( 'normal', 'italic' ), true ) ) {
			$style = 'normal';
		}
		$out[] = array(
			'family' => $family,
			'slug'   => $slug,
			'weight' => $weight,
			'style'  => $style,
			'url'    => $url,
		);
		if ( count( $out ) >= 24 ) {
			break;
		}
	}
	return $out;
}

/**
 * Saved custom font faces.
 *
 * @return array<int, array{family:string,slug:string,weight:string,style:string,url:string}>
 */
function altijara_get_custom_fonts() {
	$design = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'design', array() ) : array();
	if ( ! is_array( $design ) || empty( $design['custom_fonts'] ) || ! is_array( $design['custom_fonts'] ) ) {
		return array();
	}
	return altijara_sanitize_custom_fonts( $design['custom_fonts'] );
}

/**
 * Uploaded families as selectable font presets.
 *
 * @param string $fallback_generic sans-serif|serif.
 * @return array<string, array{label:string,stack:string}>
 */
function altijara_uploaded_font_presets( $fallback_generic = 'sans-serif' ) {
	$fallback_generic = ( 'serif' === $fallback_generic ) ? 'serif' : 'sans-serif';
	$presets          = array();
	foreach ( altijara_get_custom_fonts() as $face ) {
		$slug = ! empty( $face['slug'] ) ? sanitize_key( $face['slug'] ) : altijara_custom_font_slug( $face['family'] );
		$key  = 'upload_' . $slug;
		if ( isset( $presets[ $key ] ) ) {
			continue;
		}
		$presets[ $key ] = array(
			/* translators: %s: font family name */
			'label' => sprintf( __( '%s (uploaded)', 'altijara' ), $face['family'] ),
			'stack' => '"' . $face['family'] . '", ' . $fallback_generic,
		);
	}
	return $presets;
}

/**
 * Build @font-face rules for uploaded fonts.
 *
 * @return string
 */
function altijara_get_custom_fonts_face_css() {
	$faces = altijara_get_custom_fonts();
	if ( empty( $faces ) ) {
		return '';
	}
	$css = '';
	foreach ( $faces as $face ) {
		$family = str_replace( array( '"', "'", '<', '>', '{', '}' ), '', $face['family'] );
		$url    = esc_url( $face['url'] );
		if ( '' === $family || '' === $url ) {
			continue;
		}
		$format = altijara_font_format_from_url( $face['url'] );
		$css   .= '@font-face{font-family:"' . $family . '";font-style:' . $face['style'] . ';font-weight:' . $face['weight'] . ';font-display:swap;src:url("' . $url . '") format("' . $format . '");}';
	}
	return $css;
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
	return $presets[ $fallback ]['stack'] ?? altijara_font_main_presets()['ibm_plex']['stack'];
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
	$d            = altijara_default_design();
	$custom_fonts = altijara_sanitize_custom_fonts( $raw['custom_fonts'] ?? array() );

	$main_keys  = array( 'ibm_plex', 'system', 'tahoma', 'custom' );
	$serif_keys = array( 'playfair', 'georgia', 'custom' );
	foreach ( $custom_fonts as $face ) {
		$slug = ! empty( $face['slug'] ) ? sanitize_key( $face['slug'] ) : altijara_custom_font_slug( $face['family'] );
		$key  = 'upload_' . $slug;
		$main_keys[]  = $key;
		$serif_keys[] = $key;
	}
	$main_keys  = array_values( array_unique( $main_keys ) );
	$serif_keys = array_values( array_unique( $serif_keys ) );

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
		'custom_fonts'      => $custom_fonts,
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
	$design = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'design', array() ) : array();
	if ( ! is_array( $design ) ) {
		$design = array();
	}
	$design = array_merge( altijara_default_design(), $design );

	$main  = altijara_resolve_font_stack( $design['font_main'], $design['font_main_custom'] ?? '', 'main' );
	$serif = altijara_resolve_font_stack( $design['font_serif'], $design['font_serif_custom'] ?? '', 'serif' );

	$brand = ! empty( $design['accent_brand'] ) ? $design['accent_brand'] : '#1897B8';
	$hover = ! empty( $design['accent_hover'] ) ? $design['accent_hover'] : '#12748e';

	// Quote-safe: stacks already include family quotes from presets.
	$vars = array(
		'--font-main'       => $main,
		'--font-serif'      => $serif,
		'--font-arabic'     => $main, // article-page.css historically used this name.
		'--bg-salmon'       => $design['bg_salmon'],
		'--bg-salmon-light' => $design['bg_salmon_light'],
		'--bg-card'         => $design['bg_card'],
		'--ink-black'       => $design['ink_black'],
		'--ink-secondary'   => $design['ink_secondary'],
		'--ink-muted'       => $design['ink_muted'],
		'--accent-brand'    => $brand,
		'--accent-red'      => $brand,
		'--accent-blue'     => $brand,
		'--color-brand'     => $brand,
		'--accent-red-hover'=> $hover,
		'--accent-black'    => $design['ink_black'],
		'--accent-amber'    => $design['accent_amber'],
		'--article-teal'    => $brand,
		'--article-text'    => $design['ink_black'],
		'--article-bg'      => $design['bg_salmon'],
		'--article-card-bg' => $design['bg_card'],
	);

	$parts = array();
	foreach ( $vars as $name => $value ) {
		$value = trim( (string) $value );
		if ( '' === $value ) {
			continue;
		}
		// Strip characters that could break out of a CSS declaration.
		$value   = str_replace( array( '<', '>', '{', '}' ), '', $value );
		$parts[] = $name . ':' . $value;
	}

	$faces = altijara_get_custom_fonts_face_css();
	$css   = $faces;
	$css  .= ':root{' . implode( ';', $parts ) . '}';
	// Force body/html so later stylesheets (e.g. article-page.css) cannot ignore admin fonts.
	$css .= 'html,body{font-family:var(--font-main) !important;background-color:var(--bg-salmon);color:var(--ink-black)}';

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
 * Allow uploading web font files through the media library.
 *
 * @param array<string, string> $mimes Mime map.
 * @return array<string, string>
 */
function altijara_allow_font_upload_mimes( $mimes ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return $mimes;
	}
	$mimes['woff']  = 'font/woff';
	$mimes['woff2'] = 'font/woff2';
	$mimes['ttf']   = 'font/ttf';
	$mimes['otf']   = 'font/otf';
	return $mimes;
}
add_filter( 'upload_mimes', 'altijara_allow_font_upload_mimes' );

/**
 * Keep WordPress from rejecting font files during filetype checks.
 *
 * @param array<string, mixed> $data     File data.
 * @param string               $file     Full path.
 * @param string               $filename Filename.
 * @param array<string, string>|null $mimes Mimes.
 * @return array<string, mixed>
 */
function altijara_fix_font_filetype_check( $data, $file, $filename, $mimes ) {
	unset( $file, $mimes );
	if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
		return $data;
	}
	$ext = strtolower( pathinfo( (string) $filename, PATHINFO_EXTENSION ) );
	$map = array(
		'woff'  => 'font/woff',
		'woff2' => 'font/woff2',
		'ttf'   => 'font/ttf',
		'otf'   => 'font/otf',
	);
	if ( isset( $map[ $ext ] ) ) {
		$data['ext']  = $ext;
		$data['type'] = $map[ $ext ];
	}
	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'altijara_fix_font_filetype_check', 10, 4 );

/**
 * Print design CSS once at end of <head> so it wins over article-page.css and plugin CSS.
 * (Previously also inlined on altijara-main — that duplicated the payload.)
 */
function altijara_print_design_css_head() {
	if ( is_admin() ) {
		return;
	}
	$css = altijara_get_design_css();
	if ( ! $css ) {
		return;
	}
	echo '<style id="altijara-design-tokens">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized in altijara_get_design_css().
}
add_action( 'wp_head', 'altijara_print_design_css_head', 100 );
