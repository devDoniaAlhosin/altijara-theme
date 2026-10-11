<?php
/**
 * Widget Renderer — SSR load of registered templates.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a section should render on the front.
 *
 * @param string $section_key Section ID.
 */
function altijara_section_is_visible( $section_key ) {
	$cfg = altijara_get_section_config( $section_key );
	return ! empty( $cfg['visible'] );
}

/**
 * Render a homepage section instance via its widget type.
 *
 * @param string $section_key Section ID.
 */
function altijara_render_home_section( $section_key ) {
	altijara_render_widget_instance( $section_key );
}

/**
 * @param string $section_key Section / instance ID.
 */
function altijara_render_widget_instance( $section_key ) {
	if ( ! altijara_section_is_visible( $section_key ) ) {
		return;
	}

	$cfg   = altijara_get_section_config( $section_key );
	$posts = altijara_get_section_posts( $section_key );
	$path  = altijara_widget_template_path( $cfg );

	get_template_part(
		$path,
		null,
		array(
			'section_key' => $section_key,
			'config'      => $cfg,
			'posts'       => $posts,
			'widget'      => ! empty( $cfg['widget'] ) ? $cfg['widget'] : altijara_layout_to_widget( $cfg['layout'] ?? '' ),
		)
	);
}
