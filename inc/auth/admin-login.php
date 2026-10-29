<?php
/**
 * Custom WordPress admin login slug (hides wp-login.php).
 *
 * Front-end member login at /login/ is unchanged.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ALTIJARA_ADMIN_LOGIN_SLUG_DEFAULT' ) ) {
	define( 'ALTIJARA_ADMIN_LOGIN_SLUG_DEFAULT', 'altijaramagazine-admin-login' );
}

/**
 * Sanitize a custom admin login path slug.
 *
 * @param string $slug Raw slug.
 * @return string
 */
function altijara_sanitize_admin_login_slug( $slug ) {
	$slug = sanitize_title( (string) $slug );
	$reserved = array(
		'wp-admin',
		'wp-login',
		'wp-content',
		'wp-includes',
		'login',
		'logout',
		'register',
		'feed',
		'admin',
		'xmlrpc',
	);
	if ( '' === $slug || in_array( $slug, $reserved, true ) ) {
		return ALTIJARA_ADMIN_LOGIN_SLUG_DEFAULT;
	}
	return $slug;
}

/**
 * Active admin login portal slug (Theme Options → Tools, or wp-config constant).
 *
 * @param bool $reset Clear static cache (after saving a new slug).
 * @return string
 */
function altijara_admin_login_slug( $reset = false ) {
	static $cached = null;
	if ( $reset ) {
		$cached = null;
	}
	if ( null !== $cached ) {
		return $cached;
	}

	// Optional hard override in wp-config.php.
	if ( defined( 'ALTIJARA_ADMIN_LOGIN_SLUG' ) && ALTIJARA_ADMIN_LOGIN_SLUG ) {
		$cached = altijara_sanitize_admin_login_slug( (string) ALTIJARA_ADMIN_LOGIN_SLUG );
		return $cached;
	}

	$adv  = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'advanced', array() ) : array();
	$from = ( is_array( $adv ) && ! empty( $adv['admin_login_slug'] ) ) ? (string) $adv['admin_login_slug'] : '';
	$cached = altijara_sanitize_admin_login_slug( $from );
	return $cached;
}

/**
 * Absolute URL for the custom admin login path.
 *
 * @param string|null $scheme URL scheme.
 * @return string
 */
function altijara_admin_login_url( $scheme = null ) {
	return home_url( '/' . altijara_admin_login_slug() . '/', $scheme );
}

/**
 * Path of the current request relative to home (no leading/trailing slash).
 *
 * @return string
 */
function altijara_admin_login_request_path() {
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return '';
	}
	$path = (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
	$path = rawurldecode( $path );

	$home_path = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$home_path = untrailingslashit( $home_path );
	if ( '' !== $home_path && 0 === strpos( $path, $home_path ) ) {
		$path = substr( $path, strlen( $home_path ) );
	}

	return trim( (string) $path, '/' );
}

/**
 * Whether this request is the custom admin login URL.
 *
 * @return bool
 */
function altijara_is_admin_login_request() {
	return altijara_admin_login_slug() === altijara_admin_login_request_path();
}

/**
 * Register rewrite rule so the slug is a valid front request.
 */
function altijara_admin_login_register_rewrite() {
	add_rewrite_rule(
		'^' . preg_quote( altijara_admin_login_slug(), '/' ) . '/?$',
		'index.php?altijara_admin_login=1',
		'top'
	);
}
add_action( 'init', 'altijara_admin_login_register_rewrite', 5 );

/**
 * @param string[] $vars Query vars.
 * @return string[]
 */
function altijara_admin_login_query_vars( $vars ) {
	$vars[] = 'altijara_admin_login';
	return $vars;
}
add_filter( 'query_vars', 'altijara_admin_login_query_vars' );

/**
 * Flush rewrite rules once after this feature (or slug) is introduced.
 */
function altijara_admin_login_maybe_flush_rewrites() {
	$slug = altijara_admin_login_slug();
	if ( get_option( 'altijara_admin_login_rewrite_flushed' ) === $slug ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'altijara_admin_login_rewrite_flushed', $slug, false );
}
add_action( 'init', 'altijara_admin_login_maybe_flush_rewrites', 99 );

/**
 * Rewrite a URL that points at wp-login.php to the custom slug.
 *
 * @param string $url Full URL.
 * @return string
 */
function altijara_admin_login_rewrite_url( $url ) {
	if ( ! is_string( $url ) || false === strpos( $url, 'wp-login.php' ) ) {
		return $url;
	}

	$parts = wp_parse_url( $url );
	$query = array();
	if ( ! empty( $parts['query'] ) ) {
		wp_parse_str( $parts['query'], $query );
	}

	$scheme = null;
	if ( ! empty( $parts['scheme'] ) ) {
		$scheme = $parts['scheme'];
	}

	$new = altijara_admin_login_url( $scheme );
	if ( $query ) {
		$new = add_query_arg( $query, $new );
	}
	return $new;
}

/**
 * Filter site_url() paths that target wp-login.php.
 *
 * @param string      $url     Full URL.
 * @param string      $path    Request path.
 * @param string|null $scheme  Scheme.
 * @param int|null    $blog_id Blog ID.
 * @return string
 */
function altijara_admin_login_filter_site_url( $url, $path = '', $scheme = null, $blog_id = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( is_string( $path ) && false !== strpos( $path, 'wp-login.php' ) ) {
		$query = array();
		$path_only = $path;
		if ( false !== strpos( $path, '?' ) ) {
			list( $path_only, $qs ) = explode( '?', $path, 2 );
			wp_parse_str( $qs, $query );
		}
		unset( $path_only );
		$new = altijara_admin_login_url( $scheme );
		if ( $query ) {
			$new = add_query_arg( $query, $new );
		}
		return $new;
	}
	return altijara_admin_login_rewrite_url( $url );
}
add_filter( 'site_url', 'altijara_admin_login_filter_site_url', 10, 4 );

/**
 * @param string      $url    Full URL.
 * @param string      $path   Path.
 * @param string|null $scheme Scheme.
 * @return string
 */
function altijara_admin_login_filter_network_site_url( $url, $path = '', $scheme = null ) {
	return altijara_admin_login_filter_site_url( $url, $path, $scheme, null );
}
add_filter( 'network_site_url', 'altijara_admin_login_filter_network_site_url', 10, 3 );

/**
 * Ensure wp_login_url() always uses the custom slug.
 *
 * @param string $login_url    Login URL.
 * @param string $redirect     Redirect after login.
 * @param bool   $force_reauth Force reauth.
 * @return string
 */
function altijara_admin_login_filter_login_url( $login_url, $redirect = '', $force_reauth = false ) {
	$url = altijara_admin_login_url( 'login' );
	if ( ! empty( $redirect ) ) {
		$url = add_query_arg( 'redirect_to', urlencode( $redirect ), $url );
	}
	if ( $force_reauth ) {
		$url = add_query_arg( 'reauth', '1', $url );
	}
	return $url;
}
add_filter( 'login_url', 'altijara_admin_login_filter_login_url', 10, 3 );

/**
 * Catch redirects that still target wp-login.php.
 *
 * @param string $location Redirect URL.
 * @return string
 */
function altijara_admin_login_filter_wp_redirect( $location ) {
	return altijara_admin_login_rewrite_url( $location );
}
add_filter( 'wp_redirect', 'altijara_admin_login_filter_wp_redirect' );

/**
 * Emit a 404 for direct wp-login.php access.
 */
function altijara_admin_login_forbid() {
	global $wp_query;

	status_header( 404 );
	nocache_headers();
	if ( $wp_query instanceof WP_Query ) {
		$wp_query->set_404();
	}

	$template = get_query_template( '404' );
	if ( $template ) {
		include $template;
	} else {
		wp_die( esc_html__( 'Page not found.', 'altijara' ), '', array( 'response' => 404 ) );
	}
	exit;
}

/**
 * Serve core login on the custom slug; block bare wp-login.php.
 */
function altijara_admin_login_bootstrap() {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return;
	}

	if ( altijara_is_admin_login_request() ) {
		if ( ! defined( 'ALTIJARA_ADMIN_LOGIN_PAGE' ) ) {
			define( 'ALTIJARA_ADMIN_LOGIN_PAGE', true );
		}
		global $pagenow, $error, $interim_login, $action, $user_login; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$pagenow = 'wp-login.php';
		require ABSPATH . 'wp-login.php';
		exit;
	}

	$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$script = isset( $_SERVER['SCRIPT_NAME'] ) ? (string) wp_unslash( $_SERVER['SCRIPT_NAME'] ) : '';
	if ( false !== stripos( $uri, 'wp-login.php' ) || false !== stripos( $script, 'wp-login.php' ) ) {
		altijara_admin_login_forbid();
	}
}
add_action( 'wp_loaded', 'altijara_admin_login_bootstrap', 1 );
