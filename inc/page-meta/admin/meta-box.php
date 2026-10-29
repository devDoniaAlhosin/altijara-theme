<?php
/**
 * Register page meta box (render lives in panels.php).
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register meta box.
 */
function altijara_register_page_meta_box() {
	add_meta_box(
		'altijara_page_settings',
		__( 'Altijara Page Settings', 'altijara' ),
		'altijara_render_page_meta_box',
		'page',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'altijara_register_page_meta_box' );
