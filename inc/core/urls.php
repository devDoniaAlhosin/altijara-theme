<?php
/**
 * Theme URL helpers.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme asset URL.
 *
 * @param string $path Path relative to theme root or assets/.
 */
function altijara_asset( $path ) {
	return trailingslashit( ALTIJARA_THEME_URI ) . ltrim( $path, '/' );
}

/**
 * Permalink for a theme utility page (prefers utility_page_ids / about-2).
 *
 * @param string $logical_slug about|contact|advertise|subscribe|login.
 * @param string $fallback_path Path fallback.
 * @return string
 */
function altijara_utility_page_url( $logical_slug, $fallback_path ) {
	$page = function_exists( 'altijara_get_utility_page' )
		? altijara_get_utility_page( $logical_slug )
		: get_page_by_path( $logical_slug );
	if ( $page instanceof WP_Post ) {
		$link = get_permalink( $page );
		if ( $link && ! is_wp_error( $link ) ) {
			return $link;
		}
	}
	return home_url( $fallback_path );
}

/**
 * Named theme URLs for templates + JS.
 *
 * @return array<string, string>
 */
function altijara_theme_urls() {
	$urls = array(
		'home'      => home_url( '/' ),
		'about'     => altijara_utility_page_url( 'about', '/about/' ),
		'contact'   => altijara_utility_page_url( 'contact', '/contact/' ),
		'advertise' => altijara_utility_page_url( 'advertise', '/advertise/' ),
		'subscribe' => altijara_utility_page_url( 'subscribe', '/subscribe/' ),
		'login'     => altijara_utility_page_url( 'login', '/login/' ),
		'account'   => function_exists( 'altijara_account_url' ) ? altijara_account_url() : altijara_utility_page_url( 'subscribe', '/subscribe/' ),
		'register'  => function_exists( 'altijara_register_url' ) ? altijara_register_url() : altijara_utility_page_url( 'register', '/register/' ),
		'reset'     => function_exists( 'altijara_reset_url' ) ? altijara_reset_url() : altijara_utility_page_url( 'reset', '/reset/' ),
		'saved'     => function_exists( 'altijara_saved_articles_url' ) ? altijara_saved_articles_url() : altijara_utility_page_url( 'saved', '/saved/' ),
		'issues'    => get_post_type_archive_link( 'issue' ) ?: home_url( '/issues/' ),
		'rest'      => esc_url_raw( rest_url( 'wp/v2' ) ),
		'theme'     => ALTIJARA_THEME_URI,
		'site'      => home_url( '/' ),
	);
	/**
	 * Filter named theme URLs.
	 *
	 * @param array<string, string> $urls URLs.
	 */
	return apply_filters( 'altijara_theme_urls', $urls );
}

/**
 * Whether the current front view is the English language.
 *
 * TranslatePress reports the active language; otherwise the site locale is used.
 *
 * @return bool
 */
function altijara_is_english_view() {
	$lang = '';
	if ( function_exists( 'trp_get_current_language' ) ) {
		$lang = (string) trp_get_current_language();
	}
	if ( '' === $lang ) {
		$lang = function_exists( 'determine_locale' ) ? (string) determine_locale() : (string) get_locale();
	}
	$lang = strtolower( str_replace( '_', '-', $lang ) );
	return 0 === strpos( $lang, 'en' );
}

/**
 * Logo URL for the active language.
 *
 * Header logos are used in the header and the side drawer. Footer logos stay
 * separate because the footer sits on a dark background. An empty English
 * logo falls back to the Arabic one.
 *
 * @param string $context header|footer.
 * @return string
 */
function altijara_theme_logo_url( $context = 'header' ) {
	$context = ( 'footer' === $context ) ? 'footer' : 'header';
	$opts    = altijara_get_option( $context, array() );
	if ( ! is_array( $opts ) ) {
		$opts = array();
	}
	$arabic  = isset( $opts['logo_url'] ) ? (string) $opts['logo_url'] : '';
	$english = isset( $opts['logo_url_en'] ) ? (string) $opts['logo_url_en'] : '';
	if ( altijara_is_english_view() && '' !== $english ) {
		return $english;
	}
	return $arabic;
}

/**
 * Archive / category URL for a section key (slug-mapped, not hardcoded IDs).
 *
 * @param string $key Section key.
 */
function altijara_get_section_archive_url( $key ) {
	$cat_id = altijara_resolve_category_id( $key );
	if ( $cat_id ) {
		$link = get_category_link( $cat_id );
		if ( $link && ! is_wp_error( $link ) ) {
			return $link;
		}
	}
	return home_url( '/' );
}
