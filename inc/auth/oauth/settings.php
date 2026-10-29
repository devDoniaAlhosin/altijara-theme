<?php
/**
 * OAuth settings, URLs, and login buttons.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default social-login settings.
 *
 * @return array<string, mixed>
 */
function altijara_default_auth_settings() {
	return array(
		'google_enabled'         => false,
		'google_client_id'       => '',
		'google_client_secret'   => '',
		'facebook_enabled'       => false,
		'facebook_app_id'        => '',
		'facebook_app_secret'    => '',
		'apple_enabled'          => false,
		'apple_client_id'        => '',
		'apple_team_id'          => '',
		'apple_key_id'           => '',
		'apple_private_key'      => '',
		'turnstile_enabled'      => false,
		'turnstile_site_key'     => '',
		'turnstile_secret_key'   => '',
	);
}

/**
 * Keep an Apple .p8 private key, including its line breaks.
 *
 * @param mixed $raw Raw textarea value.
 * @return string
 */
function altijara_sanitize_apple_private_key( $raw ) {
	$key = is_string( $raw ) ? trim( $raw ) : '';
	$key = str_replace( array( "\r\n", "\r" ), "\n", $key );
	if ( '' !== $key && false === strpos( $key, "\n" ) && false !== strpos( $key, '\n' ) ) {
		$key = str_replace( '\n', "\n", $key );
	}
	if ( '' === $key ) {
		return '';
	}
	return sanitize_textarea_field( $key );
}

/**
 * Sanitize auth settings.
 *
 * @param mixed $raw Raw input.
 * @return array<string, mixed>
 */
function altijara_sanitize_auth_settings( $raw ) {
	$d = altijara_default_auth_settings();
	if ( ! is_array( $raw ) ) {
		return $d;
	}
	return array(
		'google_enabled'       => ! empty( $raw['google_enabled'] ),
		'google_client_id'     => isset( $raw['google_client_id'] ) ? sanitize_text_field( $raw['google_client_id'] ) : '',
		'google_client_secret' => isset( $raw['google_client_secret'] ) ? sanitize_text_field( $raw['google_client_secret'] ) : '',
		'facebook_enabled'     => ! empty( $raw['facebook_enabled'] ),
		'facebook_app_id'      => isset( $raw['facebook_app_id'] ) ? sanitize_text_field( $raw['facebook_app_id'] ) : '',
		'facebook_app_secret'  => isset( $raw['facebook_app_secret'] ) ? sanitize_text_field( $raw['facebook_app_secret'] ) : '',
		'apple_enabled'        => ! empty( $raw['apple_enabled'] ),
		'apple_client_id'      => isset( $raw['apple_client_id'] ) ? sanitize_text_field( $raw['apple_client_id'] ) : '',
		'apple_team_id'        => isset( $raw['apple_team_id'] ) ? sanitize_text_field( $raw['apple_team_id'] ) : '',
		'apple_key_id'         => isset( $raw['apple_key_id'] ) ? sanitize_text_field( $raw['apple_key_id'] ) : '',
		'apple_private_key'    => altijara_sanitize_apple_private_key( $raw['apple_private_key'] ?? '' ),
		'turnstile_enabled'    => ! empty( $raw['turnstile_enabled'] ),
		'turnstile_site_key'   => isset( $raw['turnstile_site_key'] ) ? sanitize_text_field( $raw['turnstile_site_key'] ) : '',
		'turnstile_secret_key' => isset( $raw['turnstile_secret_key'] ) ? sanitize_text_field( $raw['turnstile_secret_key'] ) : '',
	);
}

/**
 * Get auth settings.
 *
 * @return array<string, mixed>
 */
function altijara_get_auth_settings() {
	$saved = altijara_get_option( 'auth', array() );
	return altijara_sanitize_auth_settings( is_array( $saved ) ? $saved : array() );
}

/**
 * OAuth callback URL (must match Google, Facebook, and Apple exactly).
 *
 * @return string
 */
function altijara_oauth_callback_url() {
	return add_query_arg( 'altijara_oauth_callback', '1', home_url( '/' ) );
}

/**
 * Providers this theme can sign in with.
 *
 * @return string[]
 */
function altijara_oauth_providers() {
	return array( 'google', 'facebook', 'apple' );
}

/**
 * Whether a provider is enabled and configured.
 *
 * @param string $provider google|facebook|apple.
 * @return bool
 */
function altijara_oauth_provider_ready( $provider ) {
	$auth = altijara_get_auth_settings();
	if ( 'google' === $provider ) {
		return ! empty( $auth['google_enabled'] )
			&& '' !== (string) $auth['google_client_id']
			&& '' !== (string) $auth['google_client_secret'];
	}
	if ( 'facebook' === $provider ) {
		return ! empty( $auth['facebook_enabled'] )
			&& '' !== (string) $auth['facebook_app_id']
			&& '' !== (string) $auth['facebook_app_secret'];
	}
	if ( 'apple' === $provider ) {
		return ! empty( $auth['apple_enabled'] )
			&& '' !== (string) $auth['apple_client_id']
			&& '' !== (string) $auth['apple_team_id']
			&& '' !== (string) $auth['apple_key_id']
			&& '' !== (string) $auth['apple_private_key'];
	}
	return false;
}

/**
 * Where a social login should land when the login page did not ask for a return URL.
 *
 * @return string
 */
function altijara_oauth_default_redirect() {
	if ( function_exists( 'altijara_account_url' ) ) {
		return altijara_account_url();
	}
	return home_url( '/' );
}

/**
 * Branded login page used when social login fails.
 *
 * @return string
 */
function altijara_oauth_login_page_url() {
	if ( function_exists( 'altijara_login_page_url' ) ) {
		return altijara_login_page_url();
	}
	if ( function_exists( 'altijara_theme_urls' ) ) {
		$urls = altijara_theme_urls();
		if ( ! empty( $urls['login'] ) ) {
			return $urls['login'];
		}
	}
	return home_url( '/login/' );
}

/**
 * Keep social-login returns on this site.
 *
 * @param string $url Candidate URL.
 * @return string Empty when the URL is missing or off-site.
 */
function altijara_oauth_sanitize_redirect( $url ) {
	$url = is_string( $url ) ? esc_url_raw( $url ) : '';
	if ( '' === $url ) {
		return '';
	}
	$valid = wp_validate_redirect( $url, '' );
	return is_string( $valid ) ? $valid : '';
}

/**
 * Start URL for a provider (nonce-protected).
 *
 * @param string $provider    google|facebook|apple.
 * @param string $redirect_to Optional same-site URL to open after login.
 * @return string
 */
function altijara_oauth_start_url( $provider, $redirect_to = '' ) {
	$provider = sanitize_key( $provider );
	$args     = array(
		'altijara_oauth' => $provider,
	);
	$redirect_to = altijara_oauth_sanitize_redirect( $redirect_to );
	if ( $redirect_to ) {
		$args['redirect_to'] = $redirect_to;
	}
	return wp_nonce_url(
		add_query_arg( $args, home_url( '/' ) ),
		'altijara_oauth_' . $provider
	);
}

/**
 * Render Google / Facebook login buttons when enabled.
 *
 * @param string $context     login|subscribe|register.
 * @param string $redirect_to Optional same-site URL to open after login.
 */
function altijara_render_social_login_buttons( $context = 'login', $redirect_to = '' ) {
	$auth        = altijara_get_auth_settings();
	$google_on   = ! empty( $auth['google_enabled'] );
	$facebook_on = ! empty( $auth['facebook_enabled'] );
	$apple_on    = ! empty( $auth['apple_enabled'] );
	if ( ! $google_on && ! $facebook_on && ! $apple_on ) {
		return;
	}
	$google      = altijara_oauth_provider_ready( 'google' );
	$facebook    = altijara_oauth_provider_ready( 'facebook' );
	$apple       = altijara_oauth_provider_ready( 'apple' );
	$redirect_to = altijara_oauth_sanitize_redirect( $redirect_to );

	$google_label = ( 'subscribe' === $context )
		? __( 'التسجيل عبر Google', 'altijara' )
		: __( 'الدخول عبر Google', 'altijara' );
	$fb_label     = ( 'subscribe' === $context )
		? __( 'التسجيل عبر Facebook', 'altijara' )
		: __( 'الدخول عبر Facebook', 'altijara' );
	$apple_label  = ( 'subscribe' === $context )
		? __( 'التسجيل عبر Apple', 'altijara' )
		: __( 'الدخول عبر Apple', 'altijara' );
	$setup_hint   = __( 'فعّل بيانات OAuth من إعدادات القالب', 'altijara' );
	$google_svg   = '<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.4h6.5c-.3 1.5-1.1 2.8-2.4 3.6v3h3.9c2.3-2.1 3.5-5.2 3.5-8.7z"/><path fill="#34A853" d="M12 24c3.2 0 5.9-1.1 7.9-2.9l-3.9-3c-1.1.7-2.4 1.2-4 1.2-3.1 0-5.7-2.1-6.6-4.9H1.4v3.1C3.4 21.3 7.4 24 12 24z"/><path fill="#FBBC05" d="M5.4 14.4c-.2-.7-.4-1.4-.4-2.4s.1-1.7.4-2.4V6.5H1.4C.5 8.3 0 10.1 0 12s.5 3.7 1.4 5.5l4-3.1z"/><path fill="#EA4335" d="M12 4.8c1.7 0 3.3.6 4.5 1.8l3.4-3.4C17.9 1.1 15.2 0 12 0 7.4 0 3.4 2.7 1.4 6.5l4 3.1C6.3 6.8 8.9 4.8 12 4.8z"/></svg>';
	$fb_svg       = '<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="#1877F2" d="M24 12.1C24 5.4 18.6 0 12 0S0 5.4 0 12.1c0 6 4.4 11 10.1 11.9v-8.4H7.1v-3.5h3V9.4c0-3 1.8-4.7 4.5-4.7 1.3 0 2.7.2 2.7.2v3h-1.5c-1.5 0-2 .9-2 1.9v2.3h3.3l-.5 3.5h-2.8V24C19.6 23.1 24 18.1 24 12.1z"/></svg>';
	$apple_svg    = '<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M16.37 12.64c.02 2.22 1.95 2.96 1.97 2.97-.02.05-.31 1.05-1.01 2.08-.61.89-1.24 1.77-2.24 1.79-.98.02-1.29-.58-2.41-.58s-1.47.56-2.4.6c-.96.04-1.69-.96-2.31-1.85-1.26-1.82-2.22-5.14-.93-7.38.64-1.11 1.79-1.82 3.03-1.84.95-.02 1.84.64 2.41.64.57 0 1.66-.79 2.8-.67.48.02 1.81.19 2.67 1.45-.07.04-1.59.93-1.58 2.79zM14.7 6.72c.51-.62.85-1.48.76-2.34-.73.03-1.62.49-2.14 1.1-.47.54-.88 1.41-.77 2.24.82.06 1.65-.41 2.15-1z"/></svg>';
	?>
	<div class="auth-social">
		<?php if ( $google_on && $google ) : ?>
			<a class="auth-social-btn auth-social-btn--link" href="<?php echo esc_url( altijara_oauth_start_url( 'google', $redirect_to ) ); ?>">
				<?php echo $google_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $google_label ); ?></span>
			</a>
		<?php elseif ( $google_on ) : ?>
			<button type="button" class="auth-social-btn" disabled title="<?php echo esc_attr( $setup_hint ); ?>">
				<?php echo $google_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $google_label ); ?></span>
			</button>
		<?php endif; ?>
		<?php if ( $facebook_on && $facebook ) : ?>
			<a class="auth-social-btn auth-social-btn--link" href="<?php echo esc_url( altijara_oauth_start_url( 'facebook', $redirect_to ) ); ?>">
				<?php echo $fb_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $fb_label ); ?></span>
			</a>
		<?php elseif ( $facebook_on ) : ?>
			<button type="button" class="auth-social-btn" disabled title="<?php echo esc_attr( $setup_hint ); ?>">
				<?php echo $fb_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $fb_label ); ?></span>
			</button>
		<?php endif; ?>
		<?php if ( $apple_on && $apple ) : ?>
			<a class="auth-social-btn auth-social-btn--link" href="<?php echo esc_url( altijara_oauth_start_url( 'apple', $redirect_to ) ); ?>">
				<?php echo $apple_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $apple_label ); ?></span>
			</a>
		<?php elseif ( $apple_on ) : ?>
			<button type="button" class="auth-social-btn" disabled title="<?php echo esc_attr( $setup_hint ); ?>">
				<?php echo $apple_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $apple_label ); ?></span>
			</button>
		<?php endif; ?>
	</div>
	<div class="auth-divider"><span><?php esc_html_e( 'أو', 'altijara' ); ?></span></div>
	<?php
}
