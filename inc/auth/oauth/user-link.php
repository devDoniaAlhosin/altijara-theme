<?php
/**
 * Link OAuth profiles to WordPress users.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Synthetic but site-valid email when a provider withholds email (common on Facebook).
 *
 * @param string $provider google|facebook|apple.
 * @param string $oid      Provider user id.
 * @return string
 */
function altijara_oauth_stub_email( $provider, $oid ) {
	$provider = sanitize_key( $provider );
	$stub     = $oid ? preg_replace( '/[^a-zA-Z0-9]/', '', (string) $oid ) : wp_generate_password( 10, false );
	if ( ! $stub ) {
		$stub = wp_generate_password( 10, false );
	}
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	$host = is_string( $host ) ? strtolower( preg_replace( '/^www\./', '', $host ) ) : '';
	if ( ! $host || false === strpos( $host, '.' ) ) {
		$host = 'users.altijara.local';
	}
	$email = $provider . '.' . $stub . '@' . $host;
	if ( ! is_email( $email ) ) {
		$email = $provider . '.' . $stub . '@example.com';
	}
	// Avoid colliding with a real inbox on the same domain.
	$n = 0;
	$candidate = $email;
	while ( email_exists( $candidate ) && $n < 50 ) {
		++$n;
		$candidate = $provider . '.' . $stub . '.' . $n . '@' . $host;
		if ( ! is_email( $candidate ) ) {
			$candidate = $provider . '.' . $stub . '.' . $n . '@example.com';
		}
	}
	return $candidate;
}

/**
 * Whether the OAuth profile reports a verified email we may use for link/create.
 *
 * @param array<string,mixed> $profile Profile.
 * @return bool
 */
function altijara_oauth_profile_email_verified( $profile ) {
	if ( empty( $profile['email'] ) || ! is_email( $profile['email'] ) ) {
		return false;
	}
	$v = $profile['email_verified'] ?? false;
	return ( true === $v || 'true' === $v || 1 === $v || '1' === $v );
}

/**
 * Mark a user email as verified (OAuth or confirm link).
 *
 * @param int $user_id User ID.
 */
function altijara_oauth_mark_email_verified( $user_id ) {
	$user_id = (int) $user_id;
	if ( $user_id < 1 ) {
		return;
	}
	if ( function_exists( 'altijara_mark_email_verified' ) ) {
		altijara_mark_email_verified( $user_id );
		return;
	}
	update_user_meta( $user_id, '_altijara_email_verified', '1' );
}

/**
 * Find or create WP user from OAuth profile.
 *
 * Email is used for lookup/link/create only when the provider reports it verified.
 *
 * @param string               $provider google|facebook|apple.
 * @param array<string,string> $profile  Profile.
 * @return int|WP_Error
 */
function altijara_oauth_upsert_user( $provider, $profile ) {
	$meta_keys = array(
		'google'   => '_altijara_oauth_google_id',
		'facebook' => '_altijara_oauth_facebook_id',
		'apple'    => '_altijara_oauth_apple_id',
	);
	$meta_key  = $meta_keys[ $provider ] ?? '';
	if ( '' === $meta_key ) {
		return new WP_Error( 'provider', 'provider' );
	}
	$oid       = isset( $profile['id'] ) ? (string) $profile['id'] : '';
	$email_raw = isset( $profile['email'] ) ? sanitize_email( $profile['email'] ) : '';
	$name      = isset( $profile['name'] ) ? sanitize_text_field( $profile['name'] ) : '';
	$verified  = altijara_oauth_profile_email_verified( $profile );
	$email     = ( $verified && $email_raw ) ? $email_raw : '';

	if ( $oid ) {
		$found = get_users(
			array(
				'meta_key'   => $meta_key,
				'meta_value' => $oid,
				'number'     => 1,
				'fields'     => 'ID',
			)
		);
		if ( ! empty( $found[0] ) ) {
			$user_id = (int) $found[0];
			if ( $email && ! email_exists( $email ) ) {
				$existing = get_userdata( $user_id );
				// Upgrade a synthetic email if Facebook later grants a real verified one.
				if ( $existing && $existing->user_email && false !== strpos( $existing->user_email, $provider . '.' ) ) {
					wp_update_user(
						array(
							'ID'         => $user_id,
							'user_email' => $email,
						)
					);
				}
			}
			if ( $email ) {
				altijara_oauth_mark_email_verified( $user_id );
			}
			return $user_id;
		}
	}

	if ( $email ) {
		$by_email = get_user_by( 'email', $email );
		if ( $by_email ) {
			update_user_meta( $by_email->ID, $meta_key, $oid );
			altijara_oauth_mark_email_verified( (int) $by_email->ID );
			if ( $name && ! $by_email->display_name ) {
				wp_update_user(
					array(
						'ID'           => (int) $by_email->ID,
						'display_name' => $name,
					)
				);
			}
			return (int) $by_email->ID;
		}
	}

	// Google/Apple returned an email that is not verified — refuse rather than claim it.
	if ( $email_raw && ! $verified && in_array( $provider, array( 'google', 'apple' ), true ) ) {
		return new WP_Error( 'unverified', 'unverified' );
	}

	// Facebook often withholds email; still create a stable account from the Graph id.
	// Unverified provider emails are not used (stub instead) to avoid silent account takeover.
	if ( ! $email ) {
		if ( ! $oid ) {
			return new WP_Error( 'profile', 'profile' );
		}
		$email = altijara_oauth_stub_email( $provider, $oid );
	}

	$login_base = sanitize_user( $provider . '_' . ( $oid ? substr( preg_replace( '/\D/', '', $oid ), -12 ) : wp_generate_password( 6, false ) ), true );
	if ( ! $login_base ) {
		$login_base = sanitize_user( current( explode( '@', $email ) ), true );
	}
	if ( ! $login_base ) {
		$login_base = $provider . 'user';
	}
	$login = $login_base;
	$n     = 1;
	while ( username_exists( $login ) ) {
		$login = $login_base . $n;
		++$n;
	}

	$role = ( class_exists( 'WooCommerce', false ) && get_role( 'customer' ) ) ? 'customer' : 'subscriber';

	$user_id = wp_insert_user(
		array(
			'user_login'   => $login,
			'user_email'   => $email,
			'user_pass'    => wp_generate_password( 24 ),
			'display_name' => $name ? $name : $login,
			'role'         => $role,
			'show_admin_bar_front' => false,
		)
	);
	if ( is_wp_error( $user_id ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Altijara OAuth wp_insert_user failed: ' . $user_id->get_error_message() );
		}
		return $user_id;
	}
	update_user_meta( (int) $user_id, $meta_key, $oid );
	update_user_meta( (int) $user_id, '_altijara_oauth_provider', $provider );
	if ( $verified ) {
		altijara_oauth_mark_email_verified( (int) $user_id );
	}
	return (int) $user_id;
}
