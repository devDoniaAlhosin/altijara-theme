<?php
/**
 * Template Name: حسابي
 * Legacy theme account shell — not auto-created.
 * With WooCommerce, redirects to Woo My Account; without Woo, no account page.
 *
 * @package Altijara
 */

if ( class_exists( 'WooCommerce', false ) && function_exists( 'wc_get_page_permalink' ) ) {
	$wc = wc_get_page_permalink( 'myaccount' );
	if ( $wc ) {
		wp_safe_redirect( $wc );
		exit;
	}
}

// Without WooCommerce: cart / my-account / checkout do not exist.
$fallback = function_exists( 'altijara_theme_urls' )
	? ( altijara_theme_urls()['subscribe'] ?? home_url( '/' ) )
	: home_url( '/' );
wp_safe_redirect( $fallback );
exit;
