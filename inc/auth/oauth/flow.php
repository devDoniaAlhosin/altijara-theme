<?php
/**
 * OAuth start/callback flow orchestration.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle OAuth start + callback on init.
 */
function altijara_oauth_bootstrap() {
	if ( is_admin() ) {
		return;
	}

	// Start.
	if ( ! empty( $_GET['altijara_oauth'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$provider = sanitize_key( wp_unslash( $_GET['altijara_oauth'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! in_array( $provider, altijara_oauth_providers(), true ) ) {
			return;
		}
		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'altijara_oauth_' . $provider ) ) {
			wp_die( esc_html__( 'رابط الدخول الاجتماعي غير صالح.', 'altijara' ), 403 );
		}
		if ( ! altijara_oauth_provider_ready( $provider ) ) {
			wp_die( esc_html__( 'تسجيل الدخول الاجتماعي غير مفعّل لهذا المزود.', 'altijara' ) );
		}
		altijara_oauth_redirect_to_provider( $provider );
		exit;
	}

	// Callback.
	if ( empty( $_GET['altijara_oauth_callback'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	altijara_oauth_handle_callback();
	exit;
}
add_action( 'init', 'altijara_oauth_bootstrap', 5 );

/**
 * Redirect browser to the provider authorize URL.
 *
 * @param string $provider google|facebook|apple.
 */
function altijara_oauth_redirect_to_provider( $provider ) {
	$auth     = altijara_get_auth_settings();
	$state    = wp_generate_password( 32, false, false );
	$redirect = '';
	if ( ! empty( $_GET['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$redirect = altijara_oauth_sanitize_redirect( wp_unslash( $_GET['redirect_to'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	set_transient(
		'altijara_oauth_state_' . $state,
		array(
			'provider' => $provider,
			'redirect' => $redirect,
		),
		10 * MINUTE_IN_SECONDS
	);

	$callback = altijara_oauth_callback_url();

	if ( 'google' === $provider ) {
		$url = add_query_arg(
			array(
				'client_id'     => $auth['google_client_id'],
				'redirect_uri'  => $callback,
				'response_type' => 'code',
				'scope'         => 'openid email profile',
				'state'         => $state,
				'access_type'   => 'online',
				'prompt'        => 'select_account',
			),
			'https://accounts.google.com/o/oauth2/v2/auth'
		);
		wp_redirect( $url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		exit;
	}

	if ( 'apple' === $provider ) {
		$url = add_query_arg(
			array(
				'client_id'     => $auth['apple_client_id'],
				'redirect_uri'  => $callback,
				'response_type' => 'code',
				'response_mode' => 'form_post',
				'scope'         => 'name email',
				'state'         => $state,
			),
			'https://appleid.apple.com/auth/authorize'
		);
		wp_redirect( $url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		exit;
	}

	$url = add_query_arg(
		array(
			'client_id'     => $auth['facebook_app_id'],
			'redirect_uri'  => $callback,
			'state'         => $state,
			'scope'         => 'email,public_profile',
			'response_type' => 'code',
			// Ask again for email if the user skipped it on a previous attempt.
			'auth_type'     => 'rerequest',
		),
		'https://www.facebook.com/v21.0/dialog/oauth'
	);
	wp_redirect( $url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
	exit;
}

/**
 * Read and consume the one-time social-login state.
 *
 * @param string $state State token from the provider.
 * @return array{provider:string,redirect:string}
 */
function altijara_oauth_read_state( $state ) {
	$payload = get_transient( 'altijara_oauth_state_' . $state );
	delete_transient( 'altijara_oauth_state_' . $state );

	if ( is_string( $payload ) && in_array( $payload, altijara_oauth_providers(), true ) ) {
		return array(
			'provider' => $payload,
			'redirect' => '',
		);
	}
	if ( ! is_array( $payload ) ) {
		return array(
			'provider' => '',
			'redirect' => '',
		);
	}

	$provider = isset( $payload['provider'] ) ? sanitize_key( (string) $payload['provider'] ) : '';
	if ( ! in_array( $provider, altijara_oauth_providers(), true ) ) {
		$provider = '';
	}

	return array(
		'provider' => $provider,
		'redirect' => altijara_oauth_sanitize_redirect( isset( $payload['redirect'] ) ? (string) $payload['redirect'] : '' ),
	);
}

/**
 * Send a failed social login back to /login/.
 *
 * @param string $code     Error code shown as oauth_error.
 * @param string $redirect Return URL to keep on the login form.
 */
function altijara_oauth_fail( $code, $redirect = '' ) {
	$args = array(
		'oauth_error' => $code ? sanitize_key( $code ) : '1',
	);
	if ( $redirect ) {
		$args['redirect_to'] = $redirect;
	}
	wp_safe_redirect( add_query_arg( $args, altijara_oauth_login_page_url() ) );
	exit;
}

/**
 * Give the reader the WooCommerce customer role when WooCommerce is active.
 *
 * @param int $user_id User ID.
 */
function altijara_oauth_ensure_customer( $user_id ) {
	if ( ! class_exists( 'WooCommerce', false ) ) {
		return;
	}
	$user = get_userdata( (int) $user_id );
	if ( ! $user instanceof WP_User ) {
		return;
	}
	if ( ! in_array( 'customer', (array) $user->roles, true ) ) {
		$user->add_role( 'customer' );
	}
}

/**
 * Sign the social-login user in, including the WooCommerce customer session.
 *
 * @param int $user_id User ID.
 * @return bool
 */
function altijara_oauth_signon( $user_id ) {
	$user = get_userdata( (int) $user_id );
	if ( ! $user instanceof WP_User ) {
		return false;
	}
	altijara_oauth_ensure_customer( $user->ID );
	wp_clear_auth_cookie();
	wp_set_current_user( $user->ID );
	if ( function_exists( 'wc_set_customer_auth_cookie' ) && function_exists( 'WC' ) && WC()->session ) {
		wc_set_customer_auth_cookie( $user->ID );
	} else {
		wp_set_auth_cookie( $user->ID, true );
	}
	do_action( 'wp_login', $user->user_login, $user );
	return true;
}

/**
 * Read a callback field from Apple's POST or a Google/Facebook GET.
 *
 * @param string $key Field name.
 * @return string
 */
function altijara_oauth_request_value( $key ) {
	foreach ( array( $_POST, $_GET ) as $source ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $source[ $key ] ) || is_array( $source[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $source[ $key ] );
		// OAuth codes must not go through sanitize_text_field (can alter provider tokens).
		if ( 'code' === $key ) {
			return preg_replace( '/[^a-zA-Z0-9_\-\.\~\+\/=]/', '', (string) $raw );
		}
		return sanitize_text_field( (string) $raw );
	}
	return '';
}

/**
 * Name Apple sends only on the first authorization (form POST).
 *
 * @return string
 */
function altijara_oauth_apple_posted_name() {
	if ( empty( $_POST['user'] ) || is_array( $_POST['user'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return '';
	}
	$raw = json_decode( wp_unslash( $_POST['user'] ), true ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! is_array( $raw ) || empty( $raw['name'] ) || ! is_array( $raw['name'] ) ) {
		return '';
	}
	$first = isset( $raw['name']['firstName'] ) ? sanitize_text_field( (string) $raw['name']['firstName'] ) : '';
	$last  = isset( $raw['name']['lastName'] ) ? sanitize_text_field( (string) $raw['name']['lastName'] ) : '';
	return trim( $first . ' ' . $last );
}

/**
 * Process OAuth callback, create/login user, then open My Account or the return URL.
 */
function altijara_oauth_handle_callback() {
	nocache_headers();

	$code  = altijara_oauth_request_value( 'code' );
	$state = altijara_oauth_request_value( 'state' );
	$error = altijara_oauth_request_value( 'error' );

	$saved    = $state ? altijara_oauth_read_state( $state ) : array( 'provider' => '', 'redirect' => '' );
	$provider = $saved['provider'];
	$redirect = $saved['redirect'];

	if ( $error || ! $code || ! $provider ) {
		$fail = 'state';
		if ( $error ) {
			$fail = ( 'access_denied' === $error ) ? 'denied' : '1';
		}
		altijara_oauth_fail( $fail, $redirect );
	}

	if ( 'google' === $provider ) {
		$profile = altijara_oauth_fetch_google_profile( $code );
	} elseif ( 'apple' === $provider ) {
		$profile = altijara_oauth_fetch_apple_profile( $code, altijara_oauth_apple_posted_name() );
	} else {
		$profile = altijara_oauth_fetch_facebook_profile( $code );
	}

	// Facebook may omit email; Graph id alone is enough to create/link a user.
	if ( empty( $profile['id'] ) && empty( $profile['email'] ) ) {
		$fail = 'profile';
		if ( 'facebook' === $provider && function_exists( 'altijara_oauth_facebook_last_error' ) ) {
			$fb = altijara_oauth_facebook_last_error();
			if ( $fb ) {
				$fail = $fb;
			}
		}
		altijara_oauth_fail( $fail, $redirect );
	}
	if ( 'facebook' === $provider && empty( $profile['id'] ) ) {
		$fb = function_exists( 'altijara_oauth_facebook_last_error' ) ? altijara_oauth_facebook_last_error() : '';
		altijara_oauth_fail( $fb ? $fb : 'profile', $redirect );
	}

	$user_id = altijara_oauth_upsert_user( $provider, $profile );
	if ( ! $user_id || is_wp_error( $user_id ) ) {
		$fail = 'user';
		if ( is_wp_error( $user_id ) ) {
			$code = $user_id->get_error_code();
			if ( 'unverified' === $code ) {
				$fail = 'unverified';
			}
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'Altijara OAuth upsert failed: ' . $user_id->get_error_message() );
			}
		}
		altijara_oauth_fail( $fail, $redirect );
	}
	if ( ! altijara_oauth_signon( (int) $user_id ) ) {
		altijara_oauth_fail( 'user', $redirect );
	}

	$dest = $redirect ? $redirect : altijara_oauth_default_redirect();
	$dest = apply_filters( 'altijara_oauth_login_redirect', $dest, (int) $user_id, $provider );
	wp_safe_redirect( $dest );
	exit;
}
