<?php
/**
 * UPLOAD THIS FILE TO:  public_html/ad-debug.php
 * (same folder as wp-config.php — NOT inside the theme)
 *
 * Then open (logged in as admin):
 *   https://YOUR-SITE/ad-debug.php?ctx=issues
 *
 * DELETE after debugging.
 */

$wp_load = __DIR__ . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
	header( 'Content-Type: text/plain; charset=utf-8' );
	echo "ERROR: wp-load.php not found.\n";
	echo "Put this file in public_html next to wp-config.php.\n";
	exit;
}

require_once $wp_load;

if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
	auth_redirect();
	exit;
}

$ctx = isset( $_GET['ctx'] ) ? sanitize_key( wp_unslash( $_GET['ctx'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

if ( 'issues' === $ctx ) {
	query_posts(
		array(
			'post_type'      => 'issue',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
		)
	);
	$GLOBALS['wp_query']->is_post_type_archive = true;
	$GLOBALS['wp_query']->is_archive           = true;
	$GLOBALS['wp_query']->is_home              = false;
	$GLOBALS['wp_query']->is_singular          = false;
	$GLOBALS['wp_query']->is_single            = false;
	$GLOBALS['wp_query']->is_page              = false;
}

$header = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'header', array() ) : array();
$sc     = is_array( $header ) && ! empty( $header['ad_shortcode'] ) ? trim( (string) $header['ad_shortcode'] ) : '';

global $shortcode_tags;
$registered = is_array( $shortcode_tags ) ? array_keys( $shortcode_tags ) : array();
$adning_on  = in_array( 'adning', $registered, true );
$angwp      = defined( 'ADNI_VERSION' )
	|| class_exists( 'ADNI_Main', false )
	|| class_exists( 'ADNI_Filters', false )
	|| class_exists( 'ADNI_Shortcodes', false );

$raw_out = $sc ? do_shortcode( $sc ) : '';
$via_hdr = ( $sc && function_exists( 'altijara_do_header_ad_shortcode' ) )
	? altijara_do_header_ad_shortcode( $sc )
	: $raw_out;

$info = array(
	'ctx'                           => $ctx ? $ctx : 'default',
	'has_front_fallback'            => function_exists( 'altijara_header_ad_begin_front_context' ) ? 'yes' : 'no_upload_ads_php',
	'theme'                         => get_stylesheet(),
	'is_post_type_archive_issue'    => is_post_type_archive( 'issue' ),
	'altijara_issues_archive'       => function_exists( 'altijara_is_issues_archive_view' ) ? altijara_is_issues_archive_view() : null,
	'header_ad_shortcode'           => $sc,
	'adning_shortcode_registered'   => $adning_on,
	'angwp_plugin_likely_active'    => (bool) $angwp,
	'do_shortcode_len'              => is_string( $raw_out ) ? strlen( $raw_out ) : 0,
	'do_shortcode_empty'            => ( ! is_string( $raw_out ) || '' === trim( $raw_out ) ),
	'do_shortcode_has_ning_hidden'  => ( is_string( $raw_out ) && false !== strpos( $raw_out, '_ning_hidden' ) ),
	'header_helper_len'             => is_string( $via_hdr ) ? strlen( $via_hdr ) : 0,
	'header_helper_empty'           => ( ! is_string( $via_hdr ) || '' === trim( $via_hdr ) ),
	'header_helper_has_ning_hidden' => ( is_string( $via_hdr ) && false !== strpos( $via_hdr, '_ning_hidden' ) ),
	'header_helper_preview'         => is_string( $via_hdr ) ? substr( $via_hdr, 0, 800 ) : gettype( $via_hdr ),
	'do_shortcode_preview'          => is_string( $raw_out ) ? substr( $raw_out, 0, 800 ) : gettype( $raw_out ),
);

nocache_headers();
header( 'Content-Type: text/plain; charset=utf-8' );
echo "Altijara header ad / Adning debug\n";
echo "=================================\n\n";
foreach ( $info as $key => $val ) {
	if ( is_bool( $val ) ) {
		$val = $val ? 'true' : 'false';
	} elseif ( is_array( $val ) || is_object( $val ) ) {
		$val = wp_json_encode( $val, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	} elseif ( null === $val ) {
		$val = 'null';
	}
	echo $key . ":\n" . $val . "\n\n";
}
