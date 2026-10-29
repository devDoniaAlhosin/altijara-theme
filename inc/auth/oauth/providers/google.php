<?php
/**
 * Google OAuth profile exchange.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exchange Google code for profile.
 *
 * @param string $code Auth code.
 * @return array{id:string,email:string,name:string}
 */
function altijara_oauth_fetch_google_profile( $code ) {
	$auth = altijara_get_auth_settings();
	$token_res = wp_remote_post(
		'https://oauth2.googleapis.com/token',
		array(
			'timeout' => 20,
			'body'    => array(
				'code'          => $code,
				'client_id'     => $auth['google_client_id'],
				'client_secret' => $auth['google_client_secret'],
				'redirect_uri'  => altijara_oauth_callback_url(),
				'grant_type'    => 'authorization_code',
			),
		)
	);
	if ( is_wp_error( $token_res ) ) {
		return array();
	}
	$token = json_decode( wp_remote_retrieve_body( $token_res ), true );
	if ( empty( $token['access_token'] ) ) {
		return array();
	}
	$info_res = wp_remote_get(
		'https://www.googleapis.com/oauth2/v3/userinfo',
		array(
			'timeout' => 20,
			'headers' => array( 'Authorization' => 'Bearer ' . $token['access_token'] ),
		)
	);
	if ( is_wp_error( $info_res ) ) {
		return array();
	}
	$info = json_decode( wp_remote_retrieve_body( $info_res ), true );
	if ( ! is_array( $info ) ) {
		return array();
	}
	$verified = $info['email_verified'] ?? false;
	$verified = ( true === $verified || 'true' === $verified || 1 === $verified || '1' === $verified );
	return array(
		'id'             => isset( $info['sub'] ) ? (string) $info['sub'] : '',
		'email'          => isset( $info['email'] ) ? sanitize_email( $info['email'] ) : '',
		'name'           => isset( $info['name'] ) ? sanitize_text_field( $info['name'] ) : '',
		'email_verified' => $verified,
	);
}
