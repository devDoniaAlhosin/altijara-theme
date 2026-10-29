<?php
/**
 * Apple OAuth helpers and profile exchange.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base64url encode.
 *
 * @param string $data Raw bytes.
 * @return string
 */
function altijara_oauth_base64url( $data ) {
	return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
}

/**
 * Base64url decode.
 *
 * @param string $data Encoded segment.
 * @return string
 */
function altijara_oauth_base64url_decode( $data ) {
	$data = strtr( (string) $data, '-_', '+/' );
	$pad  = strlen( $data ) % 4;
	if ( $pad ) {
		$data .= str_repeat( '=', 4 - $pad );
	}
	$decoded = base64_decode( $data, true );
	return is_string( $decoded ) ? $decoded : '';
}

/**
 * Convert an OpenSSL ECDSA signature to the raw R||S form Apple's JWT expects.
 *
 * @param string $der DER signature.
 * @return string
 */
function altijara_apple_ecdsa_der_to_raw( $der ) {
	$der = (string) $der;
	$len = strlen( $der );
	if ( $len < 8 || 0x30 !== ord( $der[0] ) ) {
		return '';
	}
	$offset = 2;
	if ( ord( $der[1] ) & 0x80 ) {
		$offset = 2 + ( ord( $der[1] ) & 0x7f );
	}
	if ( $offset >= $len || 0x02 !== ord( $der[ $offset ] ) ) {
		return '';
	}
	++$offset;
	$rlen   = ord( $der[ $offset ] );
	++$offset;
	$r      = substr( $der, $offset, $rlen );
	$offset += $rlen;
	if ( $offset >= $len || 0x02 !== ord( $der[ $offset ] ) ) {
		return '';
	}
	++$offset;
	$slen = ord( $der[ $offset ] );
	++$offset;
	$s    = substr( $der, $offset, $slen );
	$r    = ltrim( $r, "\x00" );
	$s    = ltrim( $s, "\x00" );
	if ( strlen( $r ) > 32 || strlen( $s ) > 32 ) {
		return '';
	}
	return str_pad( $r, 32, "\x00", STR_PAD_LEFT ) . str_pad( $s, 32, "\x00", STR_PAD_LEFT );
}

/**
 * Short-lived client secret JWT for Apple's token endpoint.
 *
 * @return string
 */
function altijara_apple_client_secret() {
	$auth = altijara_get_auth_settings();
	$key  = isset( $auth['apple_private_key'] ) ? (string) $auth['apple_private_key'] : '';
	if ( false === strpos( $key, 'BEGIN' ) || ! function_exists( 'openssl_sign' ) ) {
		return '';
	}
	$private = openssl_pkey_get_private( $key );
	if ( ! $private ) {
		return '';
	}
	$header = altijara_oauth_base64url(
		wp_json_encode(
			array(
				'alg' => 'ES256',
				'kid' => (string) $auth['apple_key_id'],
			)
		)
	);
	$claims = altijara_oauth_base64url(
		wp_json_encode(
			array(
				'iss' => (string) $auth['apple_team_id'],
				'iat' => time(),
				'exp' => time() + HOUR_IN_SECONDS,
				'aud' => 'https://appleid.apple.com',
				'sub' => (string) $auth['apple_client_id'],
			)
		)
	);
	$signing = $header . '.' . $claims;
	$der     = '';
	if ( ! openssl_sign( $signing, $der, $private, OPENSSL_ALGO_SHA256 ) ) {
		return '';
	}
	$raw = altijara_apple_ecdsa_der_to_raw( $der );
	if ( '' === $raw ) {
		return '';
	}
	return $signing . '.' . altijara_oauth_base64url( $raw );
}

/**
 * Exchange an Apple authorization code for the user's id and email.
 *
 * @param string $code Auth code.
 * @param string $name Display name from the first Apple POST, when present.
 * @return array{id:string,email:string,name:string}
 */
function altijara_oauth_fetch_apple_profile( $code, $name = '' ) {
	$auth   = altijara_get_auth_settings();
	$secret = altijara_apple_client_secret();
	if ( '' === $secret ) {
		return array();
	}
	$token_res = wp_remote_post(
		'https://appleid.apple.com/auth/token',
		array(
			'timeout' => 20,
			'body'    => array(
				'client_id'     => $auth['apple_client_id'],
				'client_secret' => $secret,
				'code'          => $code,
				'grant_type'    => 'authorization_code',
				'redirect_uri'  => altijara_oauth_callback_url(),
			),
		)
	);
	if ( is_wp_error( $token_res ) ) {
		return array();
	}
	$token = json_decode( wp_remote_retrieve_body( $token_res ), true );
	if ( empty( $token['id_token'] ) || ! is_string( $token['id_token'] ) ) {
		return array();
	}
	$parts = explode( '.', $token['id_token'] );
	if ( count( $parts ) < 2 ) {
		return array();
	}
	$payload = json_decode( altijara_oauth_base64url_decode( $parts[1] ), true );
	if ( ! is_array( $payload ) ) {
		return array();
	}
	if ( ( $payload['iss'] ?? '' ) !== 'https://appleid.apple.com' ) {
		return array();
	}
	$aud = $payload['aud'] ?? '';
	$aud_ok = is_array( $aud )
		? in_array( (string) $auth['apple_client_id'], $aud, true )
		: (string) $aud === (string) $auth['apple_client_id'];
	if ( ! $aud_ok || empty( $payload['sub'] ) || empty( $payload['exp'] ) || (int) $payload['exp'] < time() ) {
		return array();
	}
	$verified = $payload['email_verified'] ?? false;
	$verified = ( true === $verified || 'true' === $verified || 1 === $verified || '1' === $verified );
	$email    = ( $verified && ! empty( $payload['email'] ) ) ? sanitize_email( (string) $payload['email'] ) : '';
	return array(
		'id'             => (string) $payload['sub'],
		'email'          => $email,
		'name'           => sanitize_text_field( $name ),
		'email_verified' => $verified && '' !== $email,
	);
}
