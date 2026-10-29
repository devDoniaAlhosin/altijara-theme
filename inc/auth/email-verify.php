<?php
/**
 * Email verification for password-based signups.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User meta key for verification state.
 */
define( 'ALTIJARA_EMAIL_VERIFIED_META', '_altijara_email_verified' );

/**
 * Whether the user's email is considered verified.
 * Missing meta = legacy account (treated as verified).
 *
 * @param int $user_id User ID.
 * @return bool
 */
function altijara_user_email_is_verified( $user_id ) {
	$user_id = (int) $user_id;
	if ( $user_id < 1 ) {
		return false;
	}
	$raw = get_user_meta( $user_id, ALTIJARA_EMAIL_VERIFIED_META, true );
	if ( '' === $raw || null === $raw ) {
		return true;
	}
	return '1' === (string) $raw || 1 === (int) $raw || true === $raw;
}

/**
 * Mark email unverified (new password signup).
 *
 * @param int $user_id User ID.
 */
function altijara_mark_email_unverified( $user_id ) {
	update_user_meta( (int) $user_id, ALTIJARA_EMAIL_VERIFIED_META, '0' );
}

/**
 * Mark email verified.
 *
 * @param int $user_id User ID.
 */
function altijara_mark_email_verified( $user_id ) {
	update_user_meta( (int) $user_id, ALTIJARA_EMAIL_VERIFIED_META, '1' );
}

/**
 * HMAC secret for verify tokens.
 *
 * @return string
 */
function altijara_email_verify_secret() {
	if ( defined( 'AUTH_KEY' ) && AUTH_KEY ) {
		return (string) AUTH_KEY;
	}
	return wp_salt( 'auth' );
}

/**
 * Build a signed verification URL (valid ~48 hours).
 *
 * @param int    $user_id     User ID.
 * @param string $redirect_to Optional same-site redirect after verify.
 * @return string
 */
function altijara_email_verify_url( $user_id, $redirect_to = '' ) {
	$user = get_userdata( (int) $user_id );
	if ( ! $user instanceof WP_User ) {
		return '';
	}
	$expires = time() + 2 * DAY_IN_SECONDS;
	$payload = (int) $user->ID . '|' . strtolower( $user->user_email ) . '|' . $expires;
	$token   = hash_hmac( 'sha256', $payload, altijara_email_verify_secret() );
	$args    = array(
		'altijara_verify_email' => '1',
		'uid'                   => (int) $user->ID,
		'expires'               => $expires,
		'token'                 => $token,
	);
	if ( function_exists( 'altijara_oauth_sanitize_redirect' ) ) {
		$redirect_to = altijara_oauth_sanitize_redirect( $redirect_to );
	} else {
		$redirect_to = $redirect_to ? (string) wp_validate_redirect( $redirect_to, '' ) : '';
	}
	if ( $redirect_to ) {
		$args['redirect_to'] = $redirect_to;
	}
	return add_query_arg( $args, home_url( '/' ) );
}

/**
 * Validate a verification token for a user.
 *
 * @param int    $user_id User ID.
 * @param int    $expires Expiry unix time.
 * @param string $token   HMAC token.
 * @return bool
 */
function altijara_email_verify_token_valid( $user_id, $expires, $token ) {
	$user = get_userdata( (int) $user_id );
	if ( ! $user instanceof WP_User ) {
		return false;
	}
	$expires = (int) $expires;
	if ( $expires < time() ) {
		return false;
	}
	$payload = (int) $user->ID . '|' . strtolower( $user->user_email ) . '|' . $expires;
	$expect  = hash_hmac( 'sha256', $payload, altijara_email_verify_secret() );
	return hash_equals( $expect, (string) $token );
}

/**
 * Send the verification email.
 *
 * @param int    $user_id     User ID.
 * @param string $redirect_to Optional redirect after click.
 * @return bool
 */
function altijara_send_email_verification( $user_id, $redirect_to = '' ) {
	$user = get_userdata( (int) $user_id );
	if ( ! $user instanceof WP_User || ! is_email( $user->user_email ) ) {
		return false;
	}
	$link = altijara_email_verify_url( (int) $user_id, $redirect_to );
	if ( ! $link ) {
		return false;
	}
	$emails  = function_exists( 'altijara_get_emails_settings' ) ? altijara_get_emails_settings() : array();
	$subject = ! empty( $emails['verify_subject'] ) ? (string) $emails['verify_subject'] : __( 'تأكيد البريد الإلكتروني — مجلة التجارة', 'altijara' );
	$title   = ! empty( $emails['verify_heading'] ) ? (string) $emails['verify_heading'] : __( 'تأكيد البريد الإلكتروني', 'altijara' );
	$intro   = ! empty( $emails['verify_intro'] ) ? (string) $emails['verify_intro'] : __( 'أكد بريدك لإكمال إنشاء الحساب عبر الزر التالي (صالح لمدة ٤٨ ساعة):', 'altijara' );
	$cta     = ! empty( $emails['verify_cta'] ) ? (string) $emails['verify_cta'] : __( 'تأكيد البريد', 'altijara' );
	$name    = $user->display_name ? $user->display_name : $user->user_login;
	$body    = '<p>' . sprintf(
		/* translators: %s: display name */
		esc_html__( 'مرحباً %s،', 'altijara' ),
		esc_html( $name )
	) . '</p>';
	$body   .= '<p>' . esc_html( $intro ) . '</p>';
	if ( function_exists( 'altijara_mail_cta_html' ) ) {
		$body .= altijara_mail_cta_html( $link, $cta );
	} else {
		$body .= '<p><a href="' . esc_url( $link ) . '">' . esc_html( $link ) . '</a></p>';
	}
	$body .= '<p style="margin-top:24px;font-size:13px;color:#6e7787;">' . esc_html__( 'إن لم تنشئ حساباً فتجاهل هذه الرسالة.', 'altijara' ) . '</p>';

	if ( function_exists( 'altijara_mail_send_html' ) ) {
		return altijara_mail_send_html( $user->user_email, $subject, $title, $body );
	}
	return (bool) wp_mail( $user->user_email, $subject, wp_strip_all_tags( $body ) . "\n\n" . $link );
}

/**
 * After creating a password-based reader: mark unverified and send mail.
 *
 * @param int    $user_id     User ID.
 * @param string $redirect_to Optional redirect baked into the link.
 * @return bool Mail send result.
 */
function altijara_start_email_verification( $user_id, $redirect_to = '' ) {
	altijara_mark_email_unverified( (int) $user_id );
	return altijara_send_email_verification( (int) $user_id, $redirect_to );
}

/**
 * Consume ?altijara_verify_email=1 links.
 */
function altijara_handle_email_verify_request() {
	if ( empty( $_GET['altijara_verify_email'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	nocache_headers();

	$uid     = isset( $_GET['uid'] ) ? absint( $_GET['uid'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$expires = isset( $_GET['expires'] ) ? absint( $_GET['expires'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$token   = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$redirect = '';
	if ( ! empty( $_GET['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$redirect = esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$redirect = wp_validate_redirect( $redirect, '' );
	}

	$login_url = function_exists( 'altijara_utility_page_url' )
		? altijara_utility_page_url( 'login', '/login/' )
		: home_url( '/login/' );

	if ( ! $uid || ! $token || ! altijara_email_verify_token_valid( $uid, $expires, $token ) ) {
		wp_safe_redirect( add_query_arg( 'auth_notice', 'verify_fail', $login_url ) );
		exit;
	}

	altijara_mark_email_verified( $uid );

	$user = get_userdata( $uid );
	if ( $user instanceof WP_User ) {
		wp_clear_auth_cookie();
		wp_set_current_user( $uid );
		if ( function_exists( 'wc_set_customer_auth_cookie' ) && function_exists( 'WC' ) && WC()->session ) {
			wc_set_customer_auth_cookie( $uid );
		} else {
			wp_set_auth_cookie( $uid, true );
		}
		do_action( 'wp_login', $user->user_login, $user );
	}

	$dest = $redirect ? $redirect : ( function_exists( 'altijara_account_url' ) ? altijara_account_url() : home_url( '/' ) );
	wp_safe_redirect( add_query_arg( 'auth_notice', 'verified_ok', $dest ) );
	exit;
}
add_action( 'template_redirect', 'altijara_handle_email_verify_request', 4 );

/**
 * Resend verification email (POST from login/register).
 */
function altijara_handle_verify_resend_post() {
	if ( empty( $_POST['altijara_verify_resend'] ) ) {
		return;
	}
	$login_url = function_exists( 'altijara_utility_page_url' )
		? altijara_utility_page_url( 'login', '/login/' )
		: home_url( '/login/' );

	if ( ! isset( $_POST['altijara_auth_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['altijara_auth_nonce'] ) ), 'altijara_verify_resend' ) ) {
		wp_safe_redirect( add_query_arg( 'auth_notice', 'nonce', $login_url ) );
		exit;
	}
	if ( function_exists( 'altijara_turnstile_required' ) && altijara_turnstile_required() && function_exists( 'altijara_turnstile_verify_request' ) && ! altijara_turnstile_verify_request() ) {
		wp_safe_redirect( add_query_arg( 'auth_notice', 'captcha', $login_url ) );
		exit;
	}
	if ( function_exists( 'altijara_auth_rate_allow' ) && ! altijara_auth_rate_allow( 'verify_resend', 4, 900 ) ) {
		wp_safe_redirect( add_query_arg( 'auth_notice', 'rate', $login_url ) );
		exit;
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	// Always same success copy (no enumeration).
	if ( is_email( $email ) ) {
		$user = get_user_by( 'email', $email );
		if ( $user instanceof WP_User && ! altijara_user_email_is_verified( (int) $user->ID ) ) {
			altijara_send_email_verification( (int) $user->ID );
		}
	}
	wp_safe_redirect( add_query_arg( 'auth_notice', 'verify_sent', $login_url ) );
	exit;
}
add_action( 'template_redirect', 'altijara_handle_verify_resend_post', 5 );
