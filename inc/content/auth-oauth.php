<?php
/**
 * Social login (Google / Facebook) — theme options + OAuth redirect flow.
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
		'google_enabled'       => false,
		'google_client_id'     => '',
		'google_client_secret' => '',
		'facebook_enabled'     => false,
		'facebook_app_id'      => '',
		'facebook_app_secret'  => '',
	);
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
 * OAuth callback URL (must match Google/Facebook console).
 *
 * @return string
 */
function altijara_oauth_callback_url() {
	return home_url( '/?altijara_oauth_callback=1' );
}

/**
 * Whether a provider is enabled and configured.
 *
 * @param string $provider google|facebook.
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
	return false;
}

/**
 * Start URL for a provider (nonce-protected).
 *
 * @param string $provider google|facebook.
 * @return string
 */
function altijara_oauth_start_url( $provider ) {
	$provider = sanitize_key( $provider );
	return wp_nonce_url(
		add_query_arg(
			array(
				'altijara_oauth' => $provider,
			),
			home_url( '/' )
		),
		'altijara_oauth_' . $provider
	);
}

/**
 * Render Google / Facebook login buttons when enabled.
 *
 * @param string $context login|subscribe.
 */
function altijara_render_social_login_buttons( $context = 'login' ) {
	$google   = altijara_oauth_provider_ready( 'google' );
	$facebook = altijara_oauth_provider_ready( 'facebook' );

	$google_label = ( 'subscribe' === $context )
		? __( 'التسجيل عبر Google', 'altijara' )
		: __( 'الدخول عبر Google', 'altijara' );
	$fb_label     = ( 'subscribe' === $context )
		? __( 'التسجيل عبر Facebook', 'altijara' )
		: __( 'الدخول عبر Facebook', 'altijara' );
	$setup_hint   = __( 'فعّل بيانات OAuth من إعدادات القالب', 'altijara' );
	$google_svg   = '<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.4h6.5c-.3 1.5-1.1 2.8-2.4 3.6v3h3.9c2.3-2.1 3.5-5.2 3.5-8.7z"/><path fill="#34A853" d="M12 24c3.2 0 5.9-1.1 7.9-2.9l-3.9-3c-1.1.7-2.4 1.2-4 1.2-3.1 0-5.7-2.1-6.6-4.9H1.4v3.1C3.4 21.3 7.4 24 12 24z"/><path fill="#FBBC05" d="M5.4 14.4c-.2-.7-.4-1.4-.4-2.4s.1-1.7.4-2.4V6.5H1.4C.5 8.3 0 10.1 0 12s.5 3.7 1.4 5.5l4-3.1z"/><path fill="#EA4335" d="M12 4.8c1.7 0 3.3.6 4.5 1.8l3.4-3.4C17.9 1.1 15.2 0 12 0 7.4 0 3.4 2.7 1.4 6.5l4 3.1C6.3 6.8 8.9 4.8 12 4.8z"/></svg>';
	$fb_svg       = '<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="#1877F2" d="M24 12.1C24 5.4 18.6 0 12 0S0 5.4 0 12.1c0 6 4.4 11 10.1 11.9v-8.4H7.1v-3.5h3V9.4c0-3 1.8-4.7 4.5-4.7 1.3 0 2.7.2 2.7.2v3h-1.5c-1.5 0-2 .9-2 1.9v2.3h3.3l-.5 3.5h-2.8V24C19.6 23.1 24 18.1 24 12.1z"/></svg>';
	?>
	<div class="auth-social">
		<?php if ( $google ) : ?>
			<a class="auth-social-btn auth-social-btn--link" href="<?php echo esc_url( altijara_oauth_start_url( 'google' ) ); ?>">
				<?php echo $google_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $google_label ); ?></span>
			</a>
		<?php else : ?>
			<button type="button" class="auth-social-btn" disabled title="<?php echo esc_attr( $setup_hint ); ?>">
				<?php echo $google_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $google_label ); ?></span>
			</button>
		<?php endif; ?>
		<?php if ( $facebook ) : ?>
			<a class="auth-social-btn auth-social-btn--link" href="<?php echo esc_url( altijara_oauth_start_url( 'facebook' ) ); ?>">
				<?php echo $fb_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $fb_label ); ?></span>
			</a>
		<?php else : ?>
			<button type="button" class="auth-social-btn" disabled title="<?php echo esc_attr( $setup_hint ); ?>">
				<?php echo $fb_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $fb_label ); ?></span>
			</button>
		<?php endif; ?>
	</div>
	<div class="auth-divider"><span><?php esc_html_e( 'أو', 'altijara' ); ?></span></div>
	<?php
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
		if ( ! in_array( $provider, array( 'google', 'facebook' ), true ) ) {
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
 * Redirect browser to Google or Facebook authorize URL.
 *
 * @param string $provider google|facebook.
 */
function altijara_oauth_redirect_to_provider( $provider ) {
	$auth  = altijara_get_auth_settings();
	$state = wp_create_nonce( 'altijara_oauth_state_' . $provider );
	set_transient( 'altijara_oauth_state_' . $state, $provider, 10 * MINUTE_IN_SECONDS );

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

	$url = add_query_arg(
		array(
			'client_id'     => $auth['facebook_app_id'],
			'redirect_uri'  => $callback,
			'state'         => $state,
			'scope'         => 'email,public_profile',
			'response_type' => 'code',
		),
		'https://www.facebook.com/v19.0/dialog/oauth'
	);
	wp_redirect( $url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
	exit;
}

/**
 * Process OAuth callback, create/login user, redirect to login page.
 */
function altijara_oauth_handle_callback() {
	$login_url = function_exists( 'altijara_theme_urls' )
		? ( altijara_theme_urls()['login'] ?? home_url( '/login/' ) )
		: home_url( '/login/' );

	$code  = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$error = isset( $_GET['error'] ) ? sanitize_text_field( wp_unslash( $_GET['error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( $error || ! $code || ! $state ) {
		wp_safe_redirect( add_query_arg( 'oauth_error', '1', $login_url ) );
		exit;
	}

	$provider = get_transient( 'altijara_oauth_state_' . $state );
	delete_transient( 'altijara_oauth_state_' . $state );
	if ( ! $provider || ! in_array( $provider, array( 'google', 'facebook' ), true ) ) {
		wp_safe_redirect( add_query_arg( 'oauth_error', 'state', $login_url ) );
		exit;
	}
	if ( ! wp_verify_nonce( $state, 'altijara_oauth_state_' . $provider ) ) {
		wp_safe_redirect( add_query_arg( 'oauth_error', 'nonce', $login_url ) );
		exit;
	}

	$profile = ( 'google' === $provider )
		? altijara_oauth_fetch_google_profile( $code )
		: altijara_oauth_fetch_facebook_profile( $code );

	if ( empty( $profile['email'] ) && empty( $profile['id'] ) ) {
		wp_safe_redirect( add_query_arg( 'oauth_error', 'profile', $login_url ) );
		exit;
	}

	$user_id = altijara_oauth_upsert_user( $provider, $profile );
	if ( ! $user_id || is_wp_error( $user_id ) ) {
		wp_safe_redirect( add_query_arg( 'oauth_error', 'user', $login_url ) );
		exit;
	}

	wp_clear_auth_cookie();
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true );
	do_action( 'wp_login', get_userdata( $user_id )->user_login, get_userdata( $user_id ) );

	$redirect = apply_filters( 'altijara_oauth_login_redirect', home_url( '/' ), $user_id, $provider );
	wp_safe_redirect( $redirect );
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
	return array(
		'id'    => isset( $info['sub'] ) ? (string) $info['sub'] : '',
		'email' => isset( $info['email'] ) ? sanitize_email( $info['email'] ) : '',
		'name'  => isset( $info['name'] ) ? sanitize_text_field( $info['name'] ) : '',
	);
}

/**
 * Exchange Facebook code for profile.
 *
 * @param string $code Auth code.
 * @return array{id:string,email:string,name:string}
 */
function altijara_oauth_fetch_facebook_profile( $code ) {
	$auth = altijara_get_auth_settings();
	$token_url = add_query_arg(
		array(
			'client_id'     => $auth['facebook_app_id'],
			'client_secret' => $auth['facebook_app_secret'],
			'redirect_uri'  => altijara_oauth_callback_url(),
			'code'          => $code,
		),
		'https://graph.facebook.com/v19.0/oauth/access_token'
	);
	$token_res = wp_remote_get( $token_url, array( 'timeout' => 20 ) );
	if ( is_wp_error( $token_res ) ) {
		return array();
	}
	$token = json_decode( wp_remote_retrieve_body( $token_res ), true );
	if ( empty( $token['access_token'] ) ) {
		return array();
	}
	$me_url = add_query_arg(
		array(
			'fields'       => 'id,name,email',
			'access_token' => $token['access_token'],
		),
		'https://graph.facebook.com/v19.0/me'
	);
	$me_res = wp_remote_get( $me_url, array( 'timeout' => 20 ) );
	if ( is_wp_error( $me_res ) ) {
		return array();
	}
	$info = json_decode( wp_remote_retrieve_body( $me_res ), true );
	if ( ! is_array( $info ) ) {
		return array();
	}
	return array(
		'id'    => isset( $info['id'] ) ? (string) $info['id'] : '',
		'email' => isset( $info['email'] ) ? sanitize_email( $info['email'] ) : '',
		'name'  => isset( $info['name'] ) ? sanitize_text_field( $info['name'] ) : '',
	);
}

/**
 * Find or create WP user from OAuth profile.
 *
 * @param string               $provider google|facebook.
 * @param array<string,string> $profile  Profile.
 * @return int|WP_Error
 */
function altijara_oauth_upsert_user( $provider, $profile ) {
	$meta_key = 'google' === $provider ? '_altijara_oauth_google_id' : '_altijara_oauth_facebook_id';
	$oid      = isset( $profile['id'] ) ? (string) $profile['id'] : '';
	$email    = isset( $profile['email'] ) ? sanitize_email( $profile['email'] ) : '';
	$name     = isset( $profile['name'] ) ? sanitize_text_field( $profile['name'] ) : '';

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
			return (int) $found[0];
		}
	}

	if ( $email ) {
		$by_email = get_user_by( 'email', $email );
		if ( $by_email ) {
			update_user_meta( $by_email->ID, $meta_key, $oid );
			return (int) $by_email->ID;
		}
	}

	if ( ! $email ) {
		$email = $provider . '_' . ( $oid ?: wp_generate_password( 8, false ) ) . '@users.altijara.local';
	}

	$login_base = sanitize_user( current( explode( '@', $email ) ), true );
	if ( ! $login_base ) {
		$login_base = $provider . 'user';
	}
	$login = $login_base;
	$n     = 1;
	while ( username_exists( $login ) ) {
		$login = $login_base . $n;
		++$n;
	}

	$user_id = wp_insert_user(
		array(
			'user_login'   => $login,
			'user_email'   => $email,
			'user_pass'    => wp_generate_password( 24 ),
			'display_name' => $name ? $name : $login,
			'role'         => 'subscriber',
		)
	);
	if ( is_wp_error( $user_id ) ) {
		return $user_id;
	}
	update_user_meta( (int) $user_id, $meta_key, $oid );
	return (int) $user_id;
}
