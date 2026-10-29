<?php
/**
 * WordPress login + global admin chrome branding.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login page logo (Theme Options → Tools, else dark header logo).
 *
 * @return string
 */
function altijara_admin_login_logo_url() {
	$adv = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'advanced', array() ) : array();
	if ( is_array( $adv ) && ! empty( $adv['admin_login_logo_url'] ) ) {
		return (string) $adv['admin_login_logo_url'];
	}

	if ( function_exists( 'altijara_admin_brand_logo_url' ) ) {
		$logo = (string) altijara_admin_brand_logo_url();
		if ( '' !== $logo ) {
			return $logo;
		}
	}

	$header = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'header', array() ) : array();
	if ( ! empty( $header['logo_url'] ) ) {
		return (string) $header['logo_url'];
	}

	if ( function_exists( 'altijara_default_options' ) ) {
		$defs = altijara_default_options();
		if ( ! empty( $defs['header']['logo_url'] ) ) {
			return (string) $defs['header']['logo_url'];
		}
	}

	return '';
}

/**
 * Login page background color (Theme Options → Tools).
 *
 * @return string Hex color.
 */
function altijara_admin_login_bg_color() {
	$fallback = '#FFF1E5';
	$adv      = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'advanced', array() ) : array();
	$bg       = ( is_array( $adv ) && ! empty( $adv['admin_login_bg'] ) ) ? trim( (string) $adv['admin_login_bg'] ) : '';
	if ( preg_match( '/^#[0-9A-Fa-f]{6}$/', $bg ) ) {
		return $bg;
	}
	if ( function_exists( 'altijara_default_options' ) ) {
		$defs = altijara_default_options();
		if ( ! empty( $defs['advanced']['admin_login_bg'] ) && preg_match( '/^#[0-9A-Fa-f]{6}$/', (string) $defs['advanced']['admin_login_bg'] ) ) {
			return (string) $defs['advanced']['admin_login_bg'];
		}
	}
	return $fallback;
}

/**
 * Inline CSS custom property for a logo URL.
 *
 * @param string $logo Logo URL.
 * @param string $var  CSS custom property name.
 * @return string
 */
function altijara_admin_logo_inline_css( $logo, $var = '--altijara-admin-logo' ) {
	$logo = (string) $logo;
	if ( '' === $logo ) {
		return '';
	}
	return sprintf(
		':root{%s:url(%s);}',
		$var,
		wp_json_encode( esc_url_raw( $logo ) )
	);
}

/**
 * Enqueue login screen branding.
 */
function altijara_login_enqueue_branding() {
	$css_path = ALTIJARA_THEME_DIR . '/assets/css/admin-login.css';
	wp_enqueue_style(
		'altijara-admin-login',
		altijara_asset( 'assets/css/admin-login.css' ),
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : ALTIJARA_THEME_VERSION
	);

	$inline = sprintf(
		':root{--altijara-login-bg:%s;}',
		altijara_admin_login_bg_color()
	);
	$logo_css = altijara_admin_logo_inline_css( altijara_admin_login_logo_url(), '--altijara-login-logo' );
	if ( $logo_css ) {
		$inline .= $logo_css . '.login h1 a{background-image:var(--altijara-login-logo)!important;}';
	}
	wp_add_inline_style( 'altijara-admin-login', $inline );
}
add_action( 'login_enqueue_scripts', 'altijara_login_enqueue_branding' );

/**
 * Login logo links to the site front page.
 *
 * @return string
 */
function altijara_login_header_url() {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'altijara_login_header_url' );

/**
 * Login logo title / accessible name.
 *
 * @return string
 */
function altijara_login_header_text() {
	return get_bloginfo( 'name', 'display' );
}
add_filter( 'login_headertext', 'altijara_login_header_text' );

/**
 * Enqueue dark admin chrome on every admin screen.
 *
 * @param string $hook Current admin page hook (unused).
 */
function altijara_admin_enqueue_chrome( $hook ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	$css_path = ALTIJARA_THEME_DIR . '/assets/css/admin-chrome.css';
	wp_enqueue_style(
		'altijara-admin-chrome',
		altijara_asset( 'assets/css/admin-chrome.css' ),
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : ALTIJARA_THEME_VERSION
	);

}
add_action( 'admin_enqueue_scripts', 'altijara_admin_enqueue_chrome', 5 );

/**
 * Point admin-bar / admin logout links at the custom admin login portal.
 *
 * @param string $logout_url Logout URL.
 * @param string $redirect   Redirect after logout.
 * @return string
 */
function altijara_admin_chrome_logout_url( $logout_url, $redirect = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( ! is_admin() || ! function_exists( 'altijara_admin_login_url' ) ) {
		return $logout_url;
	}
	$portal = altijara_admin_login_url();
	$logout_url = remove_query_arg( 'redirect_to', $logout_url );
	return add_query_arg( 'redirect_to', rawurlencode( $portal ), $logout_url );
}
add_filter( 'logout_url', 'altijara_admin_chrome_logout_url', 20, 2 );

/**
 * After logout from wp-admin, land on the custom admin login portal.
 *
 * Runs after the theme front-login logout redirect.
 *
 * @param string       $redirect_to           Default redirect.
 * @param string       $requested_redirect_to Requested redirect.
 * @param WP_User|null $user                  User who logged out.
 * @return string
 */
function altijara_admin_chrome_logout_redirect( $redirect_to, $requested_redirect_to = '', $user = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( ! function_exists( 'altijara_admin_login_url' ) ) {
		return $redirect_to;
	}

	$portal = altijara_admin_login_url();
	$slug   = function_exists( 'altijara_admin_login_slug' ) ? altijara_admin_login_slug() : 'altijaramagazine-admin-login';

	if ( is_string( $requested_redirect_to ) && false !== strpos( $requested_redirect_to, $slug ) ) {
		return $portal;
	}

	$referer = wp_get_referer();
	if ( is_string( $referer ) && false !== strpos( $referer, '/wp-admin' ) ) {
		return $portal;
	}

	return $redirect_to;
}
add_filter( 'logout_redirect', 'altijara_admin_chrome_logout_redirect', 100001, 3 );
