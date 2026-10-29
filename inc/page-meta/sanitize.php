<?php
/**
 * Sanitize posted page meta.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize posted page meta by schema.
 *
 * @param string               $schema Schema key.
 * @param array<string, mixed> $raw    Raw POST.
 * @return array<string, mixed>
 */
function altijara_sanitize_page_meta( $schema, $raw ) {
	$defaults = altijara_page_meta_defaults( $schema );
	$out      = array();

	$text_keys = array();
	$area_keys = array();
	$list_defs = array();

	switch ( $schema ) {
		case 'home':
			// Layout pack is the only write shape. Legacy content-only POSTs convert once.
			if ( function_exists( 'altijara_sanitize_home2_layout_meta' ) ) {
				if ( ! empty( $raw['layout_submitted'] ) || isset( $raw['layout_sections'] ) || isset( $raw['layout_order'] ) ) {
					return altijara_sanitize_home2_layout_meta( $raw );
				}
			}
			if ( ! empty( $raw['sections_submitted'] ) && function_exists( 'altijara_sanitize_home_meta_sections' ) && function_exists( 'altijara_home2_pack_from_legacy_sections' ) ) {
				$sections_raw = isset( $raw['sections'] ) && is_array( $raw['sections'] ) ? $raw['sections'] : array();
				$sections_raw['sections_submitted'] = 1;
				$legacy = altijara_sanitize_home_meta_sections( $sections_raw );
				return array(
					'layout' => altijara_home2_pack_from_legacy_sections( $legacy ),
				);
			}
			return array(
				'layout' => isset( $defaults['layout'] ) ? $defaults['layout'] : array( 'sections' => array(), 'order' => array() ),
			);
		case 'about':
			$text_keys = array( 'highlight_kicker', 'highlight_title', 'offers_title', 'hq_kicker', 'hq_title', 'hq_phone', 'hq_phone_label', 'hq_po_label', 'hq_po_value', 'hq_contact_label', 'hq_advertise_label' );
			$area_keys = array( 'lead', 'highlight_intro', 'mission', 'dist_intro', 'hq_address' );
			$list_defs = array(
				'stats'      => array( 'value', 'label' ),
				'offers'     => array( 'title', 'text' ),
				'dist_items' => array( 'text' ),
			);
			break;
		case 'contact':
			$text_keys = array( 'chip_email', 'chip_name', 'chip_org', 'form_title', 'form_shortcode', 'success_message', 'agent_kicker', 'agent_title', 'agent_name', 'agent_role', 'agent_phone', 'agent_mobile', 'agent_email' );
			$area_keys = array( 'lead', 'agent_intro' );
			break;
		case 'advertise':
			$text_keys = array( 'agent_kicker', 'agent_title', 'agent_name', 'agent_role', 'agent_phone', 'agent_mobile', 'agent_email', 'form_title', 'form_shortcode', 'success_message', 'why_title', 'media_kit_url' );
			$area_keys = array( 'lead', 'agent_intro', 'form_lead', 'why_text', 'guidelines' );
			$list_defs = array(
				'ad_specs' => array( 'title', 'subtitle', 'size', 'variant', 'points', 'price', 'price_note' ),
			);
			break;
		case 'subscribe':
			$text_keys = array( 'success_message' );
			$area_keys = array( 'lead', 'consent_text' );
			$list_defs = array(
				'plans' => array( 'value', 'title', 'text' ),
			);
			break;
		case 'login':
			$text_keys = array( 'success_message', 'forgot_label', 'subscribe_note', 'subscribe_link_label' );
			$area_keys = array( 'lead' );
			break;
	}

	foreach ( $text_keys as $key ) {
		$out[ $key ] = isset( $raw[ $key ] ) ? sanitize_text_field( $raw[ $key ] ) : ( $defaults[ $key ] ?? '' );
	}
	foreach ( $area_keys as $key ) {
		$out[ $key ] = isset( $raw[ $key ] ) ? sanitize_textarea_field( $raw[ $key ] ) : ( $defaults[ $key ] ?? '' );
	}
	if ( isset( $out['media_kit_url'] ) ) {
		$out['media_kit_url'] = esc_url_raw( $out['media_kit_url'] );
	}
	foreach ( array( 'chip_email', 'agent_email' ) as $email_key ) {
		if ( isset( $out[ $email_key ] ) ) {
			$out[ $email_key ] = sanitize_email( $out[ $email_key ] ) ?: sanitize_text_field( $out[ $email_key ] );
		}
	}

	foreach ( $list_defs as $list_key => $cols ) {
		$out[ $list_key ] = array();
		if ( empty( $raw[ $list_key ] ) || ! is_array( $raw[ $list_key ] ) ) {
			continue;
		}
		foreach ( $raw[ $list_key ] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$clean_row = array();
			$has       = false;
			foreach ( $cols as $col ) {
				$val = isset( $row[ $col ] ) ? (string) $row[ $col ] : '';
				if ( 'points' === $col || 'text' === $col || 'guidelines' === $col ) {
					$val = sanitize_textarea_field( $val );
				} elseif ( 'value' === $col && 'plans' === $list_key ) {
					$val = sanitize_key( $val );
				} elseif ( 'variant' === $col ) {
					$val = sanitize_key( $val );
				} else {
					$val = sanitize_text_field( $val );
				}
				$clean_row[ $col ] = $val;
				if ( '' !== trim( $val ) ) {
					$has = true;
				}
			}
			if ( $has ) {
				$out[ $list_key ][] = $clean_row;
			}
		}
	}

	return $out;
}