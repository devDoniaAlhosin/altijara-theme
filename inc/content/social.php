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
