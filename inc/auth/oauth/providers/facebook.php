<?php
/**
 * Facebook OAuth profile exchange.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remember the last Facebook Graph error for the login-page message.
 *
 * @param string|null $set New code, or null to read.
 * @return string
 */
function altijara_oauth_facebook_last_error( $set = null ) {
	static $code = '';
	if ( null !== $set ) {
		$code = sanitize_key( (string) $set );
	}
	return $code;
}

/**
 * Log Facebook Graph failures when debugging.
 *
 * @param string               $stage Stage label.
 * @param array<string,mixed>  $body  Decoded JSON body.
 * @param int                  $http  HTTP status.
 */
function altijara_oauth_facebook_debug_log( $stage, $body, $http = 0 ) {
	if ( ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) && ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) ) {
		return;
	}
	$msg = isset( $body['error']['message'] ) ? (string) $body['error']['message'] : wp_json_encode( $body );
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log( sprintf( 'Altijara Facebook OAuth [%s] HTTP %d: %s', $stage, (int) $http, $msg ) );
}

/**
 * Exchange Facebook code for profile.
 *
 * Creates/links users even when Facebook withholds email (common); id is enough.
 *
 * @param string $code Auth code.
 * @return array{id:string,email:string,name:string}
 */
function altijara_oauth_fetch_facebook_profile( $code ) {
	altijara_oauth_facebook_last_error( '' );
	$auth = altijara_get_auth_settings();
	$code = is_string( $code ) ? trim( $code ) : '';
	if ( '' === $code ) {
		altijara_oauth_facebook_last_error( 'code' );
		return array();
	}

	$app_id     = trim( (string) ( $auth['facebook_app_id'] ?? '' ) );
	$app_secret = trim( (string) ( $auth['facebook_app_secret'] ?? '' ) );
	$redirect   = altijara_oauth_callback_url();
	if ( '' === $app_id || '' === $app_secret ) {
		altijara_oauth_facebook_last_error( 'config' );
		return array();
	}

	// Prefer POST (same pattern as Google); GET often fails with long codes / strict apps.
	$token_res = wp_remote_post(
		'https://graph.facebook.com/v21.0/oauth/access_token',
		array(
			'timeout' => 20,
			'body'    => array(
				'client_id'     => $app_id,
				'client_secret' => $app_secret,
				'redirect_uri'  => $redirect,
				'code'          => $code,
			),
		)
	);
	if ( is_wp_error( $token_res ) ) {
		altijara_oauth_facebook_last_error( 'token' );
		altijara_oauth_facebook_debug_log( 'token_transport', array( 'error' => array( 'message' => $token_res->get_error_message() ) ) );
		return array();
	}
	$token_http = (int) wp_remote_retrieve_response_code( $token_res );
	$token      = json_decode( wp_remote_retrieve_body( $token_res ), true );
	if ( ! is_array( $token ) || empty( $token['access_token'] ) ) {
		altijara_oauth_facebook_last_error( 'token' );
		altijara_oauth_facebook_debug_log( 'token', is_array( $token ) ? $token : array(), $token_http );
		return array();
	}

	$access_token = (string) $token['access_token'];
	$proof        = hash_hmac( 'sha256', $access_token, $app_secret );

	$me_args = array(
		'fields'       => 'id,name,email',
		'access_token' => $access_token,
	);

	// Try with appsecret_proof first (required when the FB app enforces it).
	$info = altijara_oauth_facebook_me( array_merge( $me_args, array( 'appsecret_proof' => $proof ) ) );
	if ( empty( $info['id'] ) ) {
		// Fallback: some apps reject proof when the stored secret was rotated / mistyped relative to the token.
		$info = altijara_oauth_facebook_me( $me_args );
	}
	if ( ! is_array( $info ) || empty( $info['id'] ) ) {
		altijara_oauth_facebook_last_error( 'profile' );
		return array();
	}

	// Graph does not expose a reliable email_verified flag — never auto-link by email.
	return array(
		'id'             => (string) $info['id'],
		'email'          => isset( $info['email'] ) ? sanitize_email( (string) $info['email'] ) : '',
		'name'           => isset( $info['name'] ) ? sanitize_text_field( (string) $info['name'] ) : '',
		'email_verified' => false,
	);
}

/**
 * GET /me from Graph API.
 *
 * @param array<string,string> $args Query args.
 * @return array<string,mixed>
 */
function altijara_oauth_facebook_me( $args ) {
	$me_url = add_query_arg( $args, 'https://graph.facebook.com/v21.0/me' );
	$me_res = wp_remote_get(
		$me_url,
		array(
			'timeout' => 20,
			'headers' => array(
				'Accept' => 'application/json',
			),
		)
	);
	if ( is_wp_error( $me_res ) ) {
		altijara_oauth_facebook_debug_log( 'me_transport', array( 'error' => array( 'message' => $me_res->get_error_message() ) ) );
		return array();
	}
	$http = (int) wp_remote_retrieve_response_code( $me_res );
	$info = json_decode( wp_remote_retrieve_body( $me_res ), true );
	if ( ! is_array( $info ) || empty( $info['id'] ) ) {
		altijara_oauth_facebook_debug_log( 'me', is_array( $info ) ? $info : array(), $http );
		return array();
	}
	return $info;
}
