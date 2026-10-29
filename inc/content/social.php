<?php
/**
 * Centralized social links + SVG icons.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SVG path markup for a platform (inner path only or full svg children).
 *
 * @param string $platform Platform key.
 * @return string
 */
function altijara_social_svg_inner( $platform ) {
	$paths = array(
		'x'         => '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>',
		'twitter'   => '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>',
		'linkedin'  => '<path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 10.9v8.37H9.2V10.9H6.46M7.83 6.2a1.66 1.66 0 0 0-1.66 1.66c0 .92.74 1.66 1.66 1.66.92 0 1.66-.74 1.66-1.66 0-.92-.74-1.66-1.66-1.66Z"/>',
		'youtube'   => '<path d="M21.58 7.19a2.51 2.51 0 0 0-1.77-1.78C18.25 5 12 5 12 5s-6.25 0-7.81.41a2.51 2.51 0 0 0-1.77 1.78C2 8.75 2 12 2 12s0 3.25.42 4.81c.23.88.94 1.58 1.77 1.78 1.56.41 7.81.41 7.81.41s6.25 0 7.81-.41a2.51 2.51 0 0 0 1.77-1.78C22 15.25 22 12 22 12s0-3.25-.42-4.81zM9.75 15.02V8.98L15 12l-5.25 3.02z"/>',
		'whatsapp'  => '<path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.23 8.23 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.19 8.19 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24m4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.98-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.4-1.75-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.44-.06-.13-.56-1.34-.76-1.84-.2-.48-.4-.42-.56-.43l-.47-.01c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.44.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.1-.22-.17-.47-.29z"/>',
		'facebook'  => '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>',
		'instagram' => '<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>',
		'tiktok'    => '<path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.95-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>',
	);
	$key = sanitize_key( $platform );
	return isset( $paths[ $key ] ) ? $paths[ $key ] : '';
}

/**
 * Enabled social items sorted by order.
 *
 * @return array<int, array<string, mixed>>
 */
function altijara_get_social_links() {
	$social = altijara_get_option( 'social', array() );
	$items  = isset( $social['items'] ) && is_array( $social['items'] ) ? $social['items'] : array();
	$out    = array();
	foreach ( $items as $item ) {
		if ( empty( $item['enabled'] ) || empty( $item['url'] ) ) {
			continue;
		}
		$url = esc_url_raw( $item['url'] );
		if ( ! $url ) {
			continue;
		}
		$out[] = array(
			'platform' => sanitize_key( $item['platform'] ?? 'custom' ),
			'label'    => sanitize_text_field( $item['label'] ?? '' ),
			'url'      => $url,
			'order'    => isset( $item['order'] ) ? (int) $item['order'] : 100,
		);
	}
	usort(
		$out,
		static function ( $a, $b ) {
			return $a['order'] <=> $b['order'];
		}
	);
	return $out;
}

/**
 * Render social icon links.
 *
 * @param string $context header|footer.
 * @param int    $size    Icon pixel size.
 */
function altijara_render_social_icons( $context = 'header', $size = 12 ) {
	$links = altijara_get_social_links();
	if ( empty( $links ) ) {
		return;
	}
	// Footer shows a broader set by default; header historically showed X/LI/YT/WA.
	$header_only = array( 'x', 'twitter', 'linkedin', 'youtube', 'whatsapp' );
	foreach ( $links as $link ) {
		if ( 'header' === $context && ! in_array( $link['platform'], $header_only, true ) ) {
			// Still allow if only those enabled; show all enabled when few.
			// Match prototype: header has 4; footer has LI/X/FB/IG/YT.
			continue;
		}
		if ( 'footer' === $context && in_array( $link['platform'], array( 'whatsapp' ), true ) ) {
			continue;
		}
		$inner = altijara_social_svg_inner( $link['platform'] );
		if ( ! $inner ) {
			continue;
		}
		$label = $link['label'] ? $link['label'] : $link['platform'];
		printf(
			'<a href="%1$s" target="_blank" rel="noopener" aria-label="%2$s" title="%2$s"><svg width="%3$d" height="%3$d" viewBox="0 0 24 24" fill="currentColor">%4$s</svg></a>',
			esc_url( $link['url'] ),
			esc_attr( $label ),
			(int) $size,
			$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG paths.
		);
	}
}

/**
 * Platform choices for admin.
 *
 * @return array<string, string>
 */
function altijara_social_platform_choices() {
	return array(
		'x'         => 'X / Twitter',
		'linkedin'  => 'LinkedIn',
		'youtube'   => 'YouTube',
		'whatsapp'  => 'WhatsApp',
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'tiktok'    => 'TikTok',
		'custom'    => __( 'أخرى', 'altijara' ),
	);
}

/**
 * Built-in share platforms available on single articles (admin choices).
 *
 * @return array<string, string>
 */
function altijara_article_share_platform_choices() {
	return array(
		'whatsapp'  => 'WhatsApp',
		'telegram'  => 'Telegram',
		'x'         => 'X / Twitter',
		'linkedin'  => 'LinkedIn',
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'tiktok'    => 'TikTok',
		'email'     => __( 'Email', 'altijara' ),
		'reddit'    => 'Reddit',
		'pinterest' => 'Pinterest',
		'copy'      => __( 'Copy link', 'altijara' ),
	);
}

/**
 * Icon choices for custom share buttons.
 *
 * @return array<string, string>
 */
function altijara_article_share_icon_choices() {
	$built = altijara_article_share_platform_choices();
	unset( $built['copy'], $built['email'] );
	$built['custom'] = __( 'Generic / other', 'altijara' );
	return $built;
}

/**
 * Platforms that copy the article link (no native web share URL).
 *
 * @return string[]
 */
function altijara_article_share_copy_style_platforms() {
	return array( 'copy' );
}

/**
 * Platforms that open the device share sheet (no public web share URL).
 *
 * @return string[]
 */
function altijara_article_share_native_platforms() {
	return array( 'instagram', 'tiktok' );
}

/**
 * Enabled built-in share platform keys from article settings.
 *
 * @return string[]
 */
function altijara_get_article_share_platforms() {
	$settings = function_exists( 'altijara_get_article_settings' ) ? altijara_get_article_settings() : array();
	$raw      = isset( $settings['share_platforms'] ) && is_array( $settings['share_platforms'] )
		? $settings['share_platforms']
		: array( 'whatsapp', 'x', 'linkedin', 'facebook', 'telegram', 'copy' );
	$allowed  = array_keys( altijara_article_share_platform_choices() );
	$out      = array();
	foreach ( $raw as $p ) {
		$p = sanitize_key( $p );
		if ( $p && in_array( $p, $allowed, true ) ) {
			$out[] = $p;
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * Enabled custom share buttons from article settings.
 *
 * @return array<int, array{label:string,url_template:string,icon:string}>
 */
function altijara_get_article_share_extra() {
	$settings = function_exists( 'altijara_get_article_settings' ) ? altijara_get_article_settings() : array();
	$raw      = isset( $settings['share_extra'] ) && is_array( $settings['share_extra'] ) ? $settings['share_extra'] : array();
	$out      = array();
	foreach ( $raw as $row ) {
		if ( ! is_array( $row ) || empty( $row['enabled'] ) ) {
			continue;
		}
		$label = isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '';
		$tpl   = isset( $row['url_template'] ) ? trim( (string) $row['url_template'] ) : '';
		if ( '' === $label || '' === $tpl ) {
			continue;
		}
		$out[] = array(
			'label'        => $label,
			'url_template' => $tpl,
			'icon'         => sanitize_key( $row['icon'] ?? 'custom' ),
		);
	}
	return $out;
}

/**
 * Replace {url} / {title} placeholders in a share URL template.
 *
 * @param string $template Template.
 * @param string $url      Article URL.
 * @param string $title    Article title.
 * @return string
 */
function altijara_article_share_fill_template( $template, $url, $title ) {
	$template = (string) $template;
	$url      = (string) $url;
	$title    = (string) $title;
	$replaced = str_replace(
		array( '{url}', '{title}', '{URL}', '{TITLE}' ),
		array( rawurlencode( $url ), rawurlencode( $title ), rawurlencode( $url ), rawurlencode( $title ) ),
		$template
	);
	$clean = esc_url_raw( $replaced );
	if ( $clean ) {
		return $clean;
	}
	if ( preg_match( '#^(https?:|mailto:)#i', $replaced ) ) {
		return $replaced;
	}
	return '';
}

/**
 * Build share URL for a built-in platform.
 *
 * @param string $platform Platform key.
 * @param string $url      Article URL.
 * @param string $title    Article title.
 * @return string Empty for copy-style (handled in JS).
 */
function altijara_article_share_url( $platform, $url, $title ) {
	$url   = (string) $url;
	$title = (string) $title;
	switch ( sanitize_key( $platform ) ) {
		case 'whatsapp':
			return 'https://wa.me/?text=' . rawurlencode( $title . ' ' . $url );
		case 'telegram':
			return 'https://t.me/share/url?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( $title );
		case 'x':
		case 'twitter':
			return 'https://twitter.com/intent/tweet?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( $title );
		case 'linkedin':
			return 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $url );
		case 'facebook':
			return 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url );
		case 'email':
			return 'mailto:?subject=' . rawurlencode( $title ) . '&body=' . rawurlencode( $title . "\n\n" . $url );
		case 'reddit':
			return 'https://www.reddit.com/submit?url=' . rawurlencode( $url ) . '&title=' . rawurlencode( $title );
		case 'pinterest':
			return 'https://pinterest.com/pin/create/button/?url=' . rawurlencode( $url ) . '&description=' . rawurlencode( $title );
		default:
			return '';
	}
}

/**
 * SVG for share toolbar (telegram + copy + email + generic extras).
 *
 * @param string $platform Platform.
 * @return string Inner SVG paths.
 */
function altijara_article_share_svg_inner( $platform ) {
	$platform = sanitize_key( $platform );
	if ( 'telegram' === $platform ) {
		return '<path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>';
	}
	if ( 'copy' === $platform ) {
		return '<path fill="none" stroke="currentColor" stroke-width="2" d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path fill="none" stroke="currentColor" stroke-width="2" d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>';
	}
	if ( 'email' === $platform ) {
		return '<path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/>';
	}
	if ( 'reddit' === $platform ) {
		return '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm6.14 6.3c.55 0 1 .45 1 1 0 .4-.24.74-.58.9a3.4 3.4 0 0 1-.02.48c0 2.44-2.83 4.42-6.32 4.42H12c-3.49 0-6.32-1.98-6.32-4.42 0-.16 0-.32.02-.48A1.05 1.05 0 0 1 5.12 9.3c0-.55.45-1 1-1 .36 0 .67.19.84.47A6.7 6.7 0 0 1 12 7.7c1.85 0 3.5.6 4.78 1.57.17-.28.48-.47.84-.47zM8.8 12.1a1.15 1.15 0 1 1 0 2.3 1.15 1.15 0 0 1 0-2.3zm6.4 0a1.15 1.15 0 1 1 0 2.3 1.15 1.15 0 0 1 0-2.3zM12 17.4c-1.35 0-2.54-.45-3.3-1.14-.2-.18-.22-.48-.04-.68.18-.2.48-.22.68-.04.55.5 1.5.86 2.66.86s2.11-.36 2.66-.86c.2-.18.5-.16.68.04.18.2.16.5-.04.68-.76.69-1.95 1.14-3.3 1.14z"/>';
	}
	if ( 'pinterest' === $platform ) {
		return '<path d="M12 2C6.48 2 2 6.48 2 12c0 4.22 2.61 7.83 6.3 9.32-.09-.79-.17-2.01.03-2.87.18-.78 1.16-4.96 1.16-4.96s-.3-.59-.3-1.47c0-1.38.8-2.41 1.8-2.41.85 0 1.26.64 1.26 1.4 0 .85-.54 2.13-.82 3.31-.23.99.5 1.8 1.47 1.8 1.77 0 3.13-1.87 3.13-4.56 0-2.38-1.71-4.05-4.16-4.05-2.83 0-4.5 2.12-4.5 4.32 0 .85.33 1.77.74 2.27.08.1.09.19.07.29l-.28 1.12c-.04.18-.14.22-.32.13-1.2-.56-1.95-2.32-1.95-3.74 0-3.05 2.21-5.85 6.39-5.85 3.35 0 5.96 2.39 5.96 5.58 0 3.33-2.1 6.01-5.01 6.01-.98 0-1.9-.51-2.21-1.11l-.6 2.29c-.22.84-.81 1.89-1.21 2.53A10 10 0 0 0 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2z"/>';
	}
	if ( 'custom' === $platform ) {
		return '<path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/>';
	}
	$from_social = altijara_social_svg_inner( $platform );
	return $from_social ? $from_social : altijara_article_share_svg_inner( 'custom' );
}

/**
 * Render one share icon button (copy-style or link).
 *
 * @param string $label   Accessible label.
 * @param string $inner   SVG inner markup.
 * @param string $share   Share URL (ignored when $as_copy).
 * @param bool   $as_copy Render as copy-link button.
 * @param bool   $stroke  Stroke-style SVG (copy-link icon only).
 */
function altijara_render_article_share_button( $label, $inner, $share = '', $as_copy = false, $stroke = false ) {
	if ( ! $inner ) {
		return;
	}
	if ( $as_copy ) {
		if ( $stroke ) {
			printf(
				'<button type="button" class="btn-share-icon" onclick="typeof copyArticleLink===\'function\'&&copyArticleLink()" title="%1$s" aria-label="%1$s"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">%2$s</svg></button>',
				esc_attr( $label ),
				$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
			return;
		}
		printf(
			'<button type="button" class="btn-share-icon" onclick="typeof copyArticleLink===\'function\'&&copyArticleLink()" title="%1$s" aria-label="%1$s"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">%2$s</svg></button>',
			esc_attr( $label ),
			$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
		return;
	}
	printf(
		'<a class="btn-share-icon" href="%1$s" target="_blank" rel="noopener noreferrer" title="%2$s" aria-label="%2$s"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">%3$s</svg></a>',
		esc_url( $share ),
		esc_attr( $label ),
		$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

/**
 * Render article share toolbar from admin-selected + custom platforms.
 *
 * @param int $post_id Post ID.
 */
function altijara_render_article_share_actions( $post_id = 0 ) {
	$post_id   = $post_id ? absint( $post_id ) : get_the_ID();
	$platforms = altijara_get_article_share_platforms();
	$extra     = altijara_get_article_share_extra();
	$url    = get_permalink( $post_id );
	$title  = get_the_title( $post_id );
	$labels     = altijara_article_share_platform_choices();
	$copy_style = altijara_article_share_copy_style_platforms();
	$native     = altijara_article_share_native_platforms();
	echo '<div class="article-share-actions">';
	foreach ( $platforms as $platform ) {
		$label = isset( $labels[ $platform ] ) ? $labels[ $platform ] : $platform;
		$inner = altijara_article_share_svg_inner( $platform );
		if ( in_array( $platform, $native, true ) ) {
			printf(
				'<button type="button" class="btn-share-icon" data-share-native="1" data-share-url="%1$s" data-share-title="%2$s" title="%3$s" aria-label="%3$s"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">%4$s</svg></button>',
				esc_url( $url ),
				esc_attr( $title ),
				esc_attr( $label ),
				$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
			continue;
		}
		if ( in_array( $platform, $copy_style, true ) ) {
			altijara_render_article_share_button( $label, $inner, '', true, 'copy' === $platform );
			continue;
		}
		$share = altijara_article_share_url( $platform, $url, $title );
		if ( ! $share ) {
			continue;
		}
		altijara_render_article_share_button( $label, $inner, $share, false, false );
	}
	foreach ( $extra as $row ) {
		$share = altijara_article_share_fill_template( $row['url_template'], $url, $title );
		if ( ! $share ) {
			continue;
		}
		$icon  = $row['icon'] ? $row['icon'] : 'custom';
		$inner = altijara_article_share_svg_inner( $icon );
		altijara_render_article_share_button( $row['label'], $inner, $share, false, false );
	}
	if ( function_exists( 'altijara_render_save_button' ) ) {
		altijara_render_save_button( $post_id );
	}
	echo '</div>';
}
