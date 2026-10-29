<?php
/**
 * Theme supports, menus, image sizes, CPTs
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme setup.
 */
function altijara_theme_setup() {
	load_theme_textdomain( 'altijara', ALTIJARA_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	// Menu locations registered in inc/content/nav.php (primary, top_bar, mobile, footer, footer_quick).

	add_image_size( 'altijara-card', 640, 400, true );
	add_image_size( 'altijara-hero', 1200, 675, true );
}
add_action( 'after_setup_theme', 'altijara_theme_setup' );

/**
 * Ensure <html> always exposes dir for bilingual CSS/JS (RTL via core; LTR explicit).
 *
 * TranslatePress (and other locale switches) update is_rtl() per active language;
 * core language_attributes() already emits dir="rtl" when needed but omits dir for LTR.
 *
 * @param string $output  Space-separated language attributes.
 * @param string $doctype Document type.
 * @return string
 */
function altijara_language_attributes_dir( $output, $doctype = 'html' ) {
	unset( $doctype );
	if ( false !== strpos( $output, 'dir=' ) ) {
		return $output;
	}
	$dir = is_rtl() ? 'rtl' : 'ltr';
	return 'dir="' . esc_attr( $dir ) . '" ' . $output;
}
add_filter( 'language_attributes', 'altijara_language_attributes_dir', 10, 2 );

/**
 * Clean document titles on category / issue archives (no "Category:" / "Archives" prefix).
 *
 * @param array<string, string> $parts Title parts.
 * @return array<string, string>
 */
function altijara_document_title_parts( $parts ) {
	if ( ! is_array( $parts ) ) {
		return $parts;
	}
	if ( is_category() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term && ! empty( $term->name ) ) {
			$parts['title'] = $term->name;
		}
	} elseif ( is_post_type_archive( 'issue' ) ) {
		$parts['title'] = __( 'أعداد المجلة', 'altijara' );
	} elseif ( is_singular( 'issue' ) ) {
		$parts['title'] = get_the_title();
	}
	return $parts;
}
add_filter( 'document_title_parts', 'altijara_document_title_parts', 20 );

/**
 * Prefer clean titles when Rank Math is active.
 *
 * @param string $title Title.
 * @return string
 */
function altijara_rank_math_title( $title ) {
	if ( is_category() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term && ! empty( $term->name ) ) {
			$site = get_bloginfo( 'name' );
			return $term->name . ( $site ? ' — ' . $site : '' );
		}
	}
	if ( is_post_type_archive( 'issue' ) ) {
		$site = get_bloginfo( 'name' );
		$base = __( 'أعداد المجلة', 'altijara' );
		return $base . ( $site ? ' — ' . $site : '' );
	}
	return $title;
}
add_filter( 'rank_math/frontend/title', 'altijara_rank_math_title', 20 );

/**
 * Register Issue custom post type (magazine issues archive).
 */
function altijara_register_issue_cpt() {
	$labels = array(
		'name'          => __( 'أعداد المجلة', 'altijara' ),
		'singular_name' => __( 'عدد', 'altijara' ),
		'add_new_item'  => __( 'إضافة عدد', 'altijara' ),
		'edit_item'     => __( 'تعديل العدد', 'altijara' ),
		'view_item'     => __( 'عرض العدد', 'altijara' ),
		'search_items'  => __( 'بحث في الأعداد', 'altijara' ),
	);

	register_post_type(
		'issue',
		array(
			'labels'       => $labels,
			'public'       => true,
			'has_archive'  => 'issues',
			'rewrite'      => array( 'slug' => 'issue' ),
			'menu_icon'    => 'dashicons-book-alt',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'altijara_register_issue_cpt' );

/**
 * Body classes.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function altijara_body_classes( $classes ) {
	if ( is_page_template( 'page-issues.php' ) || is_post_type_archive( 'issue' ) || ( function_exists( 'altijara_is_issues_archive_view' ) && altijara_is_issues_archive_view() ) ) {
		$classes[] = 'issues-page-body';
	}
	if ( is_front_page() || is_category() ) {
		$classes[] = 'altijara-ssr';
	}
	if ( is_front_page() ) {
		$classes[] = 'altijara-home-ssr';
		$classes[] = 'live-ready';
	}
	$header = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'header', array() ) : array();
	if ( ! empty( $header['sticky'] ) ) {
		$classes[] = 'altijara-sticky-header';
	}
	return $classes;
}
add_filter( 'body_class', 'altijara_body_classes' );

/**
 * Pages keep their real slug.
 *
 * A short-link rewrite turns get_permalink() into /s/{id}/. WooCommerce then
 * appends account endpoints onto that id, so /my-account/edit-account/ becomes
 * /s/340444/edit-account/ and the endpoint never matches. Articles can stay short.
 *
 * @param string $link    Permalink.
 * @param int    $post_id Page ID.
 * @return string
 */
function altijara_page_permalink_use_slug( $link, $post_id ) {
	$path = wp_parse_url( (string) $link, PHP_URL_PATH );
	if ( ! is_string( $path ) || ! preg_match( '#/s/\d+/?$#', $path ) ) {
		return $link;
	}
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
		return $link;
	}
	$uri = get_page_uri( $post );
	if ( ! is_string( $uri ) || '' === $uri ) {
		return $link;
	}
	$fixed = home_url( user_trailingslashit( $uri ) );
	$query = wp_parse_url( (string) $link, PHP_URL_QUERY );
	if ( is_string( $query ) && '' !== $query ) {
		$fixed .= '?' . $query;
	}
	return $fixed;
}
add_filter( 'page_link', 'altijara_page_permalink_use_slug', 100000, 2 );

/**
 * Account endpoints must sit on /my-account/, not on /s/{id}/.
 *
 * @param string $url Endpoint or account permalink.
 * @return string
 */
function altijara_account_url_use_slug( $url ) {
	if ( ! is_string( $url ) || ! preg_match( '#/s/\d+(/|$)#', $url ) ) {
		return $url;
	}
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		return $url;
	}
	$page = get_post( (int) wc_get_page_id( 'myaccount' ) );
	if ( ! $page instanceof WP_Post ) {
		return $url;
	}
	$uri = get_page_uri( $page );
	if ( ! is_string( $uri ) || '' === $uri ) {
		return $url;
	}
	$parts = wp_parse_url( $url );
	if ( empty( $parts['path'] ) ) {
		return $url;
	}
	$path  = preg_replace( '#/s/\d+#', '/' . trim( $uri, '/' ), $parts['path'], 1 );
	$fixed = home_url( $path );
	if ( ! empty( $parts['query'] ) ) {
		$fixed .= '?' . $parts['query'];
	}
	return $fixed;
}
add_filter( 'woocommerce_get_endpoint_url', 'altijara_account_url_use_slug', 100000 );
add_filter( 'woocommerce_get_myaccount_page_permalink', 'altijara_account_url_use_slug', 100000 );

/**
 * Keep the views counter out of the public custom-fields list on articles.
 *
 * @param bool   $protected Whether the key is protected.
 * @param string $meta_key  Meta key.
 * @return bool
 */
function altijara_protect_custom_views_count( $protected, $meta_key ) {
	if ( 'custom_views_count' === $meta_key ) {
		return true;
	}
	return $protected;
}
add_filter( 'is_protected_meta', 'altijara_protect_custom_views_count', 10, 2 );

/**
 * Remove balanced &lt;div&gt;…&lt;/div&gt; blocks whose opening tag class contains $class.
 *
 * @param string $html  HTML fragment.
 * @param string $class Class token to match (e.g. custom_views_count).
 * @return string
 */
function altijara_remove_divs_with_class( $html, $class ) {
	$class = preg_quote( (string) $class, '/' );
	$offset = 0;
	while ( preg_match( '/<div\b[^>]*\bclass=(["\'])[^"\']*\b' . $class . '\b[^"\']*\1[^>]*>/i', $html, $m, PREG_OFFSET_CAPTURE, $offset ) ) {
		$start    = (int) $m[0][1];
		$open_len = strlen( $m[0][0] );
		$pos      = $start + $open_len;
		$depth    = 1;
		$len      = strlen( $html );
		while ( $pos < $len && $depth > 0 ) {
			$next_open  = stripos( $html, '<div', $pos );
			$next_close = stripos( $html, '</div>', $pos );
			if ( false === $next_close ) {
				break;
			}
			if ( false !== $next_open && $next_open < $next_close ) {
				++$depth;
				$pos = $next_open + 4;
				continue;
			}
			--$depth;
			$pos = $next_close + 6;
		}
		if ( $depth > 0 ) {
			$offset = $start + $open_len;
			continue;
		}
		$html   = substr( $html, 0, $start ) . substr( $html, $pos );
		$offset = $start;
	}
	return $html;
}

/**
 * Drop JNews / custom-fields views counter markup from the article body.
 *
 * Removes plain-text meta leaks (custom_views_count: N) and migrated JNews
 * widgets (.custom_views_count / .jeg_views_count) plus their reposition script.
 *
 * @param string $content Post content.
 * @return string
 */
function altijara_strip_custom_views_count( $content ) {
	if ( ! is_singular( 'post' ) || ! is_string( $content ) ) {
		return $content;
	}
	$haystack = strtolower( $content );
	if ( false === strpos( $haystack, 'custom_views_count' ) && false === strpos( $haystack, 'jeg_views_count' ) ) {
		return $content;
	}

	// Plain-text custom field dumps.
	$content = preg_replace( '/<li\b[^>]*>[^<]*custom_views_count\b[^<]*<\/li>/iu', '', $content );
	$content = preg_replace( '/<p\b[^>]*>\s*custom_views_count\b[^<]*<\/p>/iu', '', $content );
	$content = preg_replace( '/<div\b[^>]*>\s*custom_views_count\b[^<]*<\/div>/iu', '', $content );
	$content = preg_replace( '/<ul\b[^>]*class=[\'"][^\'"]*post-meta[^\'"]*[\'"][^>]*>\s*<\/ul>/iu', '', $content );

	// JNews share-bar widgets (may nest .counts / .sharetext).
	$content = altijara_remove_divs_with_class( $content, 'custom_views_count' );
	$content = altijara_remove_divs_with_class( $content, 'jeg_views_count' );

	// Inline jQuery that moves .custom_views_count after .jeg_sharelist.
	$content = preg_replace(
		'/<script\b[^>]*>[^<]*custom_views_count[^<]*jeg_sharelist[^<]*<\/script>/is',
		'',
		$content
	);
	$content = preg_replace(
		'/<script\b[^>]*>[^<]*jeg_sharelist[^<]*custom_views_count[^<]*<\/script>/is',
		'',
		$content
	);

	return $content;
}
add_filter( 'the_content', 'altijara_strip_custom_views_count', 999 );

/**
 * Always hide JNews views widgets on singular posts (CSS + late DOM remove).
 * Covers markup injected outside the_content (share bar, footer scripts).
 */
function altijara_hide_jnews_views_assets() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	echo '<style id="altijara-hide-jnews-views">.custom_views_count,.jeg_views_count{display:none!important}</style>' . "\n";
}
add_action( 'wp_head', 'altijara_hide_jnews_views_assets', 99 );

/**
 * Remove leftover JNews views nodes after the full page has rendered.
 */
function altijara_hide_jnews_views_footer_script() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	echo "<script id=\"altijara-hide-jnews-views-js\">(function(){function rm(){document.querySelectorAll('.custom_views_count,.jeg_views_count').forEach(function(el){el.remove();});}rm();if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',rm);})();</script>\n";
}
add_action( 'wp_footer', 'altijara_hide_jnews_views_footer_script', 99 );
