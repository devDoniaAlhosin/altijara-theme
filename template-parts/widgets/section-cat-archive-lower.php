<?php
/**
 * Category archive widget: lower column.
 *
 * Reuses the category column renderer. category.php does not load this file.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$config = isset( $args['config'] ) && is_array( $args['config'] ) ? $args['config'] : array();
$posts  = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$label  = isset( $config['label'] ) ? (string) $config['label'] : '';
$order  = array();
if ( ! empty( $config['sidebar_order'] ) ) {
	$order = array_values( array_filter( array_map( 'sanitize_key', explode( ',', (string) $config['sidebar_order'] ) ) ) );
}
if ( ! function_exists( 'altijara_render_category_lower_column' ) ) {
	return;
}
altijara_render_category_lower_column(
	$posts,
	array(
		'visible' => true,
		'order'   => $order ? $order : array( 'ad', 'magazine', 'most_read' ),
	),
	array(
		'title' => $label,
	)
);
