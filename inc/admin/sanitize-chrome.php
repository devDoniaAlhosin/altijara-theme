<?php
/**
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize header branch.
 *
 * @param array<string, mixed> $raw Raw.
 * @return array<string, mixed>
 */
function altijara_sanitize_header_input( $raw ) {
	$d = altijara_default_options()['header'];
	return array(
		'logo_url'              => isset( $raw['logo_url'] ) ? esc_url_raw( $raw['logo_url'] ) : $d['logo_url'],
		'logo_hover_url'        => isset( $raw['logo_hover_url'] ) ? esc_url_raw( $raw['logo_hover_url'] ) : $d['logo_hover_url'],
		'logo_width'            => isset( $raw['logo_width'] ) ? max( 0, (int) $raw['logo_width'] ) : 0,
		'logo_height'           => isset( $raw['logo_height'] ) ? max( 0, (int) $raw['logo_height'] ) : 0,
		'background'            => ( isset( $raw['background'] ) && is_string( $raw['background'] ) && '' !== $raw['background'] ) ? ( sanitize_hex_color( $raw['background'] ) ?: '' ) : '',
		'layout'                => isset( $raw['layout'] ) ? sanitize_key( $raw['layout'] ) : 'default',
		'sticky'                => ! empty( $raw['sticky'] ),
		'show_top_bar'          => ! empty( $raw['show_top_bar'] ),
		'top_bar_text'          => isset( $raw['top_bar_text'] ) ? sanitize_text_field( $raw['top_bar_text'] ) : '',
		'ad_shortcode'          => isset( $raw['ad_shortcode'] ) ? sanitize_text_field( $raw['ad_shortcode'] ) : '',
		'ad_image'              => isset( $raw['ad_image'] ) ? esc_url_raw( $raw['ad_image'] ) : '',
		'ad_url'                => isset( $raw['ad_url'] ) ? esc_url_raw( $raw['ad_url'] ) : '',
		'show_search'           => ! empty( $raw['show_search'] ),
		'show_login'            => ! empty( $raw['show_login'] ),
		'login_text'            => isset( $raw['login_text'] ) ? sanitize_text_field( $raw['login_text'] ) : $d['login_text'],
		'login_url'             => isset( $raw['login_url'] ) ? esc_url_raw( $raw['login_url'] ) : '',
		'show_subscribe'        => ! empty( $raw['show_subscribe'] ),
		'subscribe_text'        => isset( $raw['subscribe_text'] ) ? sanitize_text_field( $raw['subscribe_text'] ) : $d['subscribe_text'],
		'subscribe_url'         => isset( $raw['subscribe_url'] ) ? esc_url_raw( $raw['subscribe_url'] ) : '',
		'show_social'           => ! empty( $raw['show_social'] ),
		'show_date'             => ! empty( $raw['show_date'] ),
		'mobile_show_search'    => ! empty( $raw['mobile_show_search'] ),
		'mobile_show_subscribe' => ! empty( $raw['mobile_show_subscribe'] ),
	);
}

/**
 * @param array<string, mixed> $raw Raw.
 * @return array<string, mixed>
 */
function altijara_sanitize_footer_input( $raw ) {
	$d = altijara_default_options()['footer'];
	return array(
		'logo_url'         => isset( $raw['logo_url'] ) ? esc_url_raw( $raw['logo_url'] ) : $d['logo_url'],
		'description'      => isset( $raw['description'] ) ? sanitize_textarea_field( $raw['description'] ) : $d['description'],
		'sections_heading' => isset( $raw['sections_heading'] ) ? sanitize_text_field( $raw['sections_heading'] ) : $d['sections_heading'],
		'quick_heading'    => isset( $raw['quick_heading'] ) ? sanitize_text_field( $raw['quick_heading'] ) : $d['quick_heading'],
		'social_heading'   => isset( $raw['social_heading'] ) ? sanitize_text_field( $raw['social_heading'] ) : $d['social_heading'],
		'show_brand'       => ! empty( $raw['show_brand'] ),
		'show_sections'    => ! empty( $raw['show_sections'] ),
		'show_quick'       => ! empty( $raw['show_quick'] ),
		'show_social'      => ! empty( $raw['show_social'] ),
		'contact_address'  => isset( $raw['contact_address'] ) ? sanitize_textarea_field( $raw['contact_address'] ) : '',
		'contact_email'    => isset( $raw['contact_email'] ) ? sanitize_email( $raw['contact_email'] ) : '',
		'contact_phone'    => isset( $raw['contact_phone'] ) ? sanitize_text_field( $raw['contact_phone'] ) : '',
		'copyright'        => isset( $raw['copyright'] ) ? sanitize_text_field( $raw['copyright'] ) : $d['copyright'],
		'background'       => ( isset( $raw['background'] ) && is_string( $raw['background'] ) && '' !== $raw['background'] ) ? ( sanitize_hex_color( $raw['background'] ) ?: '' ) : '',
		'text_color'       => ( isset( $raw['text_color'] ) && is_string( $raw['text_color'] ) && '' !== $raw['text_color'] ) ? ( sanitize_hex_color( $raw['text_color'] ) ?: '' ) : '',
		'layout'           => isset( $raw['layout'] ) ? sanitize_key( $raw['layout'] ) : 'default',
	);
}

/**
 * @param array<string, mixed> $raw Raw.
 * @return array{items: array<int, array<string, mixed>>}
 */
function altijara_sanitize_social_input( $raw ) {
	$items_in = isset( $raw['items'] ) && is_array( $raw['items'] ) ? $raw['items'] : array();
	$items    = array();
	$order    = 10;
	foreach ( $items_in as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$url = isset( $row['url'] ) ? esc_url_raw( $row['url'] ) : '';
		$items[] = array(
			'platform' => isset( $row['platform'] ) ? sanitize_key( $row['platform'] ) : 'custom',
			'label'    => isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '',
			'url'      => $url,
			'enabled'  => ! empty( $row['enabled'] ),
			'order'    => isset( $row['order'] ) ? (int) $row['order'] : $order,
		);
		$order += 10;
	}
	return array( 'items' => $items );
}
