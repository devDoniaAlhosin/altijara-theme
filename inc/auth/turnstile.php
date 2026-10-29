<?php
/**
 * Cloudflare Turnstile for auth forms.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether Turnstile is enabled and configured.
 *
 * @return bool
 */
function altijara_turnstile_required() {
	if ( ! function_exists( 'altijara_get_auth_settings' ) ) {
		return false;
	}
	$auth = altijara_get_auth_settings();
	return ! empty( $auth['turnstile_enabled'] )
		&& '' !== (string) ( $auth['turnstile_site_key'] ?? '' )
		&& '' !== (string) ( $auth['turnstile_secret_key'] ?? '' );
}

/**
 * Site key for the widget.
 *
 * @return string
 */
function altijara_turnstile_site_key() {
	if ( ! function_exists( 'altijara_get_auth_settings' ) ) {
		return '';
	}
	$auth = altijara_get_auth_settings();
	return (string) ( $auth['turnstile_site_key'] ?? '' );
}

/**
 * Enqueue Turnstile script on auth pages when configured.
 */
function altijara_turnstile_enqueue() {
	if ( ! altijara_turnstile_required() || ! is_page() ) {
		return;
	}
	$post = get_post();
	$slug = $post instanceof WP_Post ? $post->post_name : '';
	if ( ! in_array( $slug, array( 'login', 'register', 'reset', 'subscribe' ), true ) ) {
		return;
	}
	wp_enqueue_script(
		'cloudflare-turnstile',
		'https://challenges.cloudflare.com/turnstile/v0/api.js',
		array(),
		null,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'altijara_turnstile_enqueue', 30 );

/**
 * Render the Turnstile widget markup (no-op when disabled).
 */
function altijara_turnstile_widget_html() {
	if ( ! altijara_turnstile_required() ) {
		return;
	}
	$key = altijara_turnstile_site_key();
	echo '<div class="static-field auth-turnstile">';
	echo '<div class="cf-turnstile" data-sitekey="' . esc_attr( $key ) . '" data-theme="light"></div>';
	echo '</div>';
}

/**
 * Verify the Turnstile response token from the current request.
 *
 * @return bool True when valid or when Turnstile is not required.
 */
function altijara_turnstile_verify_request() {
	if ( ! altijara_turnstile_required() ) {
		return true;
	}
	$token = '';
	if ( isset( $_POST['cf-turnstile-response'] ) && ! is_array( $_POST['cf-turnstile-response'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$token = sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}
	if ( '' === $token ) {
		return false;
	}
	$auth   = altijara_get_auth_settings();
	$secret = (string) ( $auth['turnstile_secret_key'] ?? '' );
	$ip     = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

	$response = wp_remote_post(
		'https://challenges.cloudflare.com/turnstile/v0/siteverify',
		array(
			'timeout' => 15,
			'body'    => array(
				'secret'   => $secret,
				'response' => $token,
				'remoteip' => $ip,
			),
		)
	);
	if ( is_wp_error( $response ) ) {
		return false;
	}
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	return is_array( $body ) && ! empty( $body['success'] );
}
