<?php
/**
 * Homepage sections — front body layout
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classic homepage body. Uses the shared pairing loop so a hidden column stretches.
 */
function altijara_render_home_body_sections() {
	if ( function_exists( 'altijara_echo_empty_tag_warnings' ) ) {
		altijara_echo_empty_tag_warnings();
	}
	if ( ! function_exists( 'altijara_render_layout_body_ids' ) || ! function_exists( 'altijara_get_body_section_ids' ) ) {
		return;
	}
	altijara_render_layout_body_ids( altijara_get_body_section_ids() );
}
